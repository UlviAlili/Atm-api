<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AtmCashBalanceController;
use App\Http\Controllers\Api\DenominationController;
use App\Http\Controllers\Api\CurrencyController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'throttle:10,1', 'measure.performance'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/currencies', [CurrencyController::class, 'index']);
    Route::get('/currencies/{currency}', [CurrencyController::class, 'show']);

    Route::get('/denominations', [DenominationController::class, 'index']);
    Route::get('/denominations/{denomination}', [DenominationController::class, 'show']);

    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/{account}', [AccountController::class, 'show']);

    Route::get('/atm-cash-balances', [AtmCashBalanceController::class, 'index']);
    Route::put('/atm-cash-balances/{atmCashBalance}', [AtmCashBalanceController::class, 'update']);

    Route::get('/withdrawals', [WithdrawalController::class, 'index']);
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);
    Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show']);
    Route::delete('/withdrawals/{withdrawal}', [WithdrawalController::class, 'destroy']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show']);
});