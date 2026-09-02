<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Platform\Audit\AuditRecorder;
use Illuminate\Console\Command;

final class VerifyAuditChain extends Command
{
    protected $signature = 'admission:verify-audit-chain';

    protected $description = 'Replay the audit hash chain and report the first inconsistent entry';

    public function handle(AuditRecorder $recorder): int
    {
        $broken = $recorder->firstBrokenSequence();

        if ($broken === null) {
            $this->info('Audit chain verified: every entry links to its predecessor.');

            return self::SUCCESS;
        }

        $this->error("Audit chain broken at sequence {$broken}. Investigate before relying on the trail.");

        return self::FAILURE;
    }
}
