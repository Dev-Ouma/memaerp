<?php

use App\Modules\Iam\Providers\IamServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\PlatformServiceProvider;

return [
    AppServiceProvider::class,
    PlatformServiceProvider::class,

    // One provider per bounded context. A module that needs bindings, policies, routes or
    // event listeners registers them here and nowhere else — that is what keeps the modules
    // separable if one ever has to be extracted.
    IamServiceProvider::class,
];
