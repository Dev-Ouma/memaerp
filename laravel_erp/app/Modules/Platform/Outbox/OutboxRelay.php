<?php

declare(strict_types=1);

namespace App\Modules\Platform\Outbox;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Drains the outbox.
 *
 * Rows are claimed with `SKIP LOCKED` so several relay workers can run at once without handing the same
 * event to two of them. Failures are recorded on the row and retried with a widening backoff rather
 * than being discarded, and the row is left unpublished — an event that could not be delivered stays
 * visible instead of vanishing.
 */
final class OutboxRelay
{
    public const MAX_ATTEMPTS = 10;

    public function drain(int $batchSize = 100): int
    {
        $published = 0;

        foreach ($this->claim($batchSize) as $row) {
            try {
                Event::dispatch('outbox.'.$row->event_name, [[
                    'event' => $row->event_name,
                    'aggregate_type' => $row->aggregate_type,
                    'aggregate_id' => $row->aggregate_id,
                    'payload' => json_decode((string) $row->payload, true) ?: [],
                    'correlation_id' => $row->correlation_id,
                    'occurred_at' => $row->occurred_at,
                ]]);

                DB::table('outbox_events')->where('id', $row->id)->update([
                    'published_at' => now(),
                    'last_error' => null,
                ]);

                $published++;
            } catch (Throwable $e) {
                $attempts = (int) $row->attempts + 1;

                DB::table('outbox_events')->where('id', $row->id)->update([
                    'attempts' => $attempts,
                    // Truncated and message-only: the outbox row is operational data, not a place to
                    // accumulate stack traces that may contain applicant details.
                    'last_error' => mb_substr($e->getMessage(), 0, 500),
                    'available_at' => now()->addSeconds(min(3600, 2 ** min($attempts, 11))),
                ]);

                Log::error('Outbox delivery failed.', [
                    'outbox_event_id' => $row->id,
                    'event' => $row->event_name,
                    'attempts' => $attempts,
                ]);
            }
        }

        return $published;
    }

    /** @return Collection<int, object> */
    private function claim(int $batchSize)
    {
        return DB::transaction(function () use ($batchSize) {
            $query = DB::table('outbox_events')
                ->whereNull('published_at')
                ->where('available_at', '<=', now())
                ->where('attempts', '<', self::MAX_ATTEMPTS)
                ->orderBy('occurred_at')
                ->limit($batchSize);

            if (DB::connection()->getDriverName() === 'pgsql') {
                $query->lockForUpdate()->skipLocked();
            }

            return $query->get();
        });
    }
}
