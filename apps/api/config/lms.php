<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('LMS_ENABLED', false),
    'base_url' => env('MOODLE_BASE_URL'),
    'token' => env('MOODLE_WS_TOKEN'),
    'timeout' => (int) env('LMS_HTTP_TIMEOUT', 15),
];
