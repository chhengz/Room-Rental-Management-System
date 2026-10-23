<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BuildingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\MeterReadingController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RentalContractController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\RoomTypeController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\UtilityMeterController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('v1')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view');

        Route::apiResource('buildings', BuildingController::class)->middleware('permission:buildings.manage');
        Route::apiResource('room-types', RoomTypeController::class)->middleware('permission:rooms.manage');
        Route::apiResource('rooms', RoomController::class)->middleware('permission:rooms.manage');
        Route::apiResource('tenants', TenantController::class)->middleware('permission:tenants.manage');
        Route::apiResource('contracts', RentalContractController::class)->middleware('permission:contracts.manage');
        Route::apiResource('invoices', InvoiceController::class)->middleware('permission:invoices.manage');
        Route::apiResource('payments', PaymentController::class)->middleware('permission:payments.manage');
        Route::apiResource('utility-meters', UtilityMeterController::class)->middleware('permission:utilities.manage');
        Route::apiResource('meter-readings', MeterReadingController::class)->middleware('permission:utilities.manage');
        Route::apiResource('expenses', ExpenseController::class)->middleware('permission:expenses.manage');
        Route::get('settings', [SettingController::class, 'index'])->middleware('permission:settings.manage');
        Route::put('settings', [SettingController::class, 'update'])->middleware('permission:settings.manage');
    });
});
