<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Services;

use App\Modules\Attachment\Models\AttachmentApplication;
use App\Modules\Attachment\Models\AttachmentPlacement;
use App\Modules\Attachment\Models\HostOrganization;

final class AttachmentReportService
{
    /** @return array<string, int|float> */
    public function dashboard(string $institutionId): array
    {
        return [
            'host_organizations' => HostOrganization::query()
                ->where('institution_id', $institutionId)
                ->where('is_active', true)
                ->count(),
            'pending_applications' => AttachmentApplication::query()
                ->where('institution_id', $institutionId)
                ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])
                ->count(),
            'active_placements' => AttachmentPlacement::query()
                ->where('institution_id', $institutionId)
                ->where('status', 'ACTIVE')
                ->count(),
            'completed_placements' => AttachmentPlacement::query()
                ->where('institution_id', $institutionId)
                ->where('status', 'COMPLETED')
                ->count(),
            'awaiting_host' => AttachmentPlacement::query()
                ->where('institution_id', $institutionId)
                ->where('status', 'PENDING_HOST')
                ->count(),
        ];
    }
}
