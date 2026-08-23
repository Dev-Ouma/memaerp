<?php

declare(strict_types=1);

use App\Modules\Finance\Http\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/finance')->group(function (): void {
    Route::post('/mpesa/c2b-callback', [FinanceController::class, 'mpesaCallback'])
        ->name('api.finance.mpesa.callback');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/statement', [FinanceController::class, 'statement'])->name('api.finance.statement');
        Route::get('/clearance-status', [FinanceController::class, 'clearanceStatus'])->name('api.finance.clearance');
        Route::post('/payments', [FinanceController::class, 'recordPayment'])->name('api.finance.payments.record');
        Route::post('/mpesa/stk-push', [FinanceController::class, 'mpesaStkPush'])->name('api.finance.mpesa.stk');
        Route::post('/invoices/issue', [FinanceController::class, 'issueInvoice'])->name('api.finance.invoices.issue');
        Route::get('/receipts/{payment}', [FinanceController::class, 'receipt'])
            ->whereUuid('payment')
            ->name('api.finance.receipts.show');
    });
});
