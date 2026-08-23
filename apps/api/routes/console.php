<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('audit:partitions --months=12')
    ->monthlyOn(20, '02:15')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->onOneServer();
