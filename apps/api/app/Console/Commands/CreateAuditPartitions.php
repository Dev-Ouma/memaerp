<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CreateAuditPartitions extends Command
{
    protected $signature = 'audit:partitions
        {--months=12 : Number of future monthly partitions to guarantee}';

    protected $description = 'Create future monthly partitions for the append-only audit log';

    public function handle(): int
    {
        $months = filter_var($this->option('months'), FILTER_VALIDATE_INT);

        if (! is_int($months) || $months < 1 || $months > 36) {
            $this->error('--months must be an integer between 1 and 36.');

            return self::INVALID;
        }

        $start = CarbonImmutable::now('UTC')->startOfMonth();

        try {
            DB::transaction(function () use ($months, $start): void {
                // Prevent two schedulers from racing to create the same partition.
                DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', [
                    'memaerp:audit:partitions',
                ]);

                for ($offset = 0; $offset <= $months; $offset++) {
                    $from = $start->addMonths($offset);
                    $to = $from->addMonth();
                    $partition = 'activity_log_'.$from->format('Y_m');

                    DB::statement(sprintf(
                        'CREATE TABLE IF NOT EXISTS audit.%s PARTITION OF audit.activity_log
                         FOR VALUES FROM (%s) TO (%s)',
                        $partition,
                        DB::getPdo()->quote($from->format('Y-m-d H:i:sP')),
                        DB::getPdo()->quote($to->format('Y-m-d H:i:sP')),
                    ));
                }
            });
        } catch (Throwable $exception) {
            $this->error('Audit partition creation failed: '.$exception->getMessage());
            $this->line(
                'Check audit.activity_log_default for rows in the requested range before retrying.',
            );

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Audit partitions are available from %s through %s.',
            $start->format('Y-m'),
            $start->addMonths($months)->format('Y-m'),
        ));

        return self::SUCCESS;
    }
}
