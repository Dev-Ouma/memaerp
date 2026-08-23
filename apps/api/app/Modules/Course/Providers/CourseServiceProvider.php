<?php

declare(strict_types=1);

namespace App\Modules\Course\Providers;

use App\Modules\Course\Contracts\OfferingCapacity;
use App\Modules\Course\Services\OfferingCapacityService;
use Illuminate\Support\ServiceProvider;

final class CourseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OfferingCapacity::class, OfferingCapacityService::class);
    }
}
