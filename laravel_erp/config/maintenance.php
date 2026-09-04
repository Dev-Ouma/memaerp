<?php

declare(strict_types=1);

return [
    'allow_destructive_restore' => env('MAINTENANCE_ALLOW_RESTORE', env('APP_ENV') === 'local'),
];
