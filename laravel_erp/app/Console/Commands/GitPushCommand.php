<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class GitPushCommand extends Command
{
    protected $signature = 'git:push 
                            {message? : Custom commit message}
                            {--no-test : Skip automated test suite verification}
                            {--no-build : Skip Vite asset compilation}
                            {--branch=main : Target branch to push}';

    protected $description = 'Automate building assets, running tests, staging, committing and pushing to remote Git repository';

    public function handle(): int
    {
        $this->info('🚀 Starting automated Git push sequence...');

        $basePath = base_path('..');
        $laravelPath = base_path();

        // 1. Build assets
        if (!$this->option('no-build')) {
            $this->line('📦 Step 1: Compiling production assets with Vite...');
            $buildProc = Process::fromShellCommandline('npm run build', $laravelPath);
            $buildProc->run();
            if (!$buildProc->isSuccessful()) {
                $this->error('❌ Asset build failed: ' . $buildProc->getErrorOutput());
                return self::FAILURE;
            }
            $this->info('✅ Assets built cleanly.');
        }

        // 2. Run test suite
        if (!$this->option('no-test')) {
            $this->line('🧪 Step 2: Running automated test suite in testing environment...');
            $env = array_merge($_ENV, [
                'APP_ENV' => 'testing',
                'DB_DATABASE' => 'mema_erp_testing',
            ]);
            $testProc = Process::fromShellCommandline('php artisan test', $laravelPath, $env);
            $testProc->run();
            if (!$testProc->isSuccessful()) {
                $this->error('❌ Test suite failed. Fix failing tests before pushing.');
                $this->line($testProc->getOutput());
                return self::FAILURE;
            }
            $this->info('✅ All automated tests passed.');
        }

        // 3. Check Git status
        $this->line('🔍 Step 3: Checking Git status...');
        $statusProc = Process::fromShellCommandline('git status --porcelain', $basePath);
        $statusProc->run();
        $statusOutput = trim($statusProc->getOutput());

        if (empty($statusOutput)) {
            $this->line('Checking if local branch is ahead of remote...');
            $pushProc = Process::fromShellCommandline('git push origin ' . escapeshellarg($this->option('branch')), $basePath);
            $pushProc->run();
            if ($pushProc->isSuccessful()) {
                $this->info('✅ Working tree clean. Synced with remote repository.');
                return self::SUCCESS;
            }
        }

        // 4. Stage changes
        $this->line('📥 Step 4: Staging all modified and new files...');
        $addProc = Process::fromShellCommandline('git add .', $basePath);
        $addProc->run();

        // 5. Commit
        $commitMessage = $this->argument('message') ?? 'chore: automated update [' . now()->format('Y-m-d H:i:s') . ']';
        $this->line("📝 Step 5: Creating commit: \"{$commitMessage}\"...");
        $commitProc = Process::fromShellCommandline('git commit -m ' . escapeshellarg($commitMessage), $basePath);
        $commitProc->run();

        // 6. Push to remote
        $branch = $this->option('branch');
        $this->line("🚀 Step 6: Pushing to origin/{$branch}...");
        $pushProc = Process::fromShellCommandline('git push origin ' . escapeshellarg($branch), $basePath);
        $pushProc->run();

        if (!$pushProc->isSuccessful()) {
            $this->error('❌ Git push failed: ' . $pushProc->getErrorOutput());
            return self::FAILURE;
        }

        $this->info("✨ Successfully automated commit and pushed to origin/{$branch}!");
        return self::SUCCESS;
    }
}
