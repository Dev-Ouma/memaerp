<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Platform\Outbox\OutboxRelay;
use Illuminate\Console\Command;

final class RelayOutboxEvents extends Command
{
    protected $signature = 'admission:relay-outbox {--batch=100 : Events to publish per pass}';

    protected $description = 'Publish pending transactional outbox events';

    public function handle(OutboxRelay $relay): int
    {
        $published = $relay->drain((int) $this->option('batch'));

        $this->info("Published {$published} outbox event(s).");

        return self::SUCCESS;
    }
}
