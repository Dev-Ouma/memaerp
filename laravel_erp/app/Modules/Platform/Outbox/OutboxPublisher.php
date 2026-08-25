<?php

declare(strict_types=1);

namespace App\Modules\Platform\Outbox;

use App\Models\Platform\OutboxEvent;

/**
 * Transactional outbox.
 *
 * Domain events are written in the same database transaction as the state change that produced them, so
 * an event can never claim something the database did not commit — and a committed change can never
 * silently fail to notify. A relay job publishes them afterwards.
 */
final class OutboxPublisher
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function publish(string $eventName, string $aggregateType, string $aggregateId, array $payload = []): OutboxEvent
    {
        return OutboxEvent::create([
            'event_name' => $eventName,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'payload' => $payload,
            'correlation_id' => correlation_id(),
            'occurred_at' => now(),
            'available_at' => now(),
        ]);
    }
}
