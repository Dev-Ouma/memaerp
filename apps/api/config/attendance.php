<?php

declare(strict_types=1);

return [
    'threshold_percentage' => (float) env('ATTENDANCE_THRESHOLD', 75),
    'qr_ttl_minutes' => (int) env('ATTENDANCE_QR_TTL_MINUTES', 5),
    'late_grace_minutes' => (int) env('ATTENDANCE_LATE_GRACE_MINUTES', 10),
];
