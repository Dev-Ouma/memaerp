<?php

declare(strict_types=1);

namespace App\Modules\Course\Services;

use App\Modules\Course\Contracts\OfferingCapacity;
use App\Modules\Course\Models\CourseOffering;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

final class OfferingCapacityService implements OfferingCapacity
{
    public function increment(string $offeringId): void
    {
        DB::transaction(function () use ($offeringId): void {
            $offering = CourseOffering::query()->whereKey($offeringId)->lockForUpdate()->firstOrFail();
            if ($offering->status !== 'OFFERED' || ! $offering->is_open_for_enrollment) {
                throw new HttpResponseException(response()->json([
                    'error' => ['code' => 'ERR-CRS-CLOSED', 'message' => 'This offering is closed for enrollment.'],
                ], 409));
            }
            if ($offering->enrolled_count >= $offering->max_capacity) {
                throw new HttpResponseException(response()->json([
                    'error' => ['code' => 'ERR-CRS-CAPACITY', 'message' => 'The section is at capacity. Join the waitlist.'],
                ], 409));
            }
            $offering->increment('enrolled_count');
        });
    }

    public function decrement(string $offeringId): void
    {
        DB::transaction(function () use ($offeringId): void {
            $offering = CourseOffering::query()->whereKey($offeringId)->lockForUpdate()->firstOrFail();
            if ($offering->enrolled_count > 0) {
                $offering->decrement('enrolled_count');
            }
        });
    }
}
