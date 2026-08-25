<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API entry point
|--------------------------------------------------------------------------
|
| Versioned from the first release. `/api/v1` is a published contract that the
| frontend team builds against independently; breaking changes go to a new
| prefix and are recorded in API_CHANGELOG.md.
|
*/

Route::prefix('v1')->group(base_path('routes/api/v1.php'));
