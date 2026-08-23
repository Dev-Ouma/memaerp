<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - MEMA ERP Monolith Hub
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'system' => 'MEMA ERP API',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Load Module API Routes
require app_path('Modules/Iam/Routes/api.php');
require app_path('Modules/Institution/Routes/api.php');
require app_path('Modules/Curriculum/Routes/api.php');
require app_path('Modules/Course/Routes/api.php');
require app_path('Modules/Admission/Routes/api.php');
require app_path('Modules/Finance/Routes/api.php');
require app_path('Modules/Enrollment/Routes/api.php');
require app_path('Modules/Examination/Routes/api.php');
require app_path('Modules/Graduation/Routes/api.php');
require app_path('Modules/Portal/Routes/api.php');
require app_path('Modules/Lms/Routes/api.php');
require app_path('Modules/Attendance/Routes/api.php');
require app_path('Modules/Advising/Routes/api.php');
require app_path('Modules/Attachment/Routes/api.php');
require app_path('Modules/Student/Routes/api.php');
