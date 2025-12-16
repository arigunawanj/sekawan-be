<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Booking\ApprovalController;
use App\Http\Controllers\Booking\VehicleBookingController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Master\MasterCrudController;
use App\Http\Controllers\Master\MasterDataController;
use App\Http\Controllers\Log\AppLogController;
use App\Http\Controllers\Report\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Shared (admin + approver)
    Route::get('dashboard/usage', [DashboardController::class, 'usage']);

    // Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('master/vehicle-types', [MasterDataController::class, 'vehicleTypes']);
        Route::get('master/vehicles', [MasterDataController::class, 'vehicles']);
        Route::get('master/drivers', [MasterDataController::class, 'drivers']);
        Route::get('master/approvers', [MasterDataController::class, 'approvers']);

        // Master CRUD (admin)
        Route::post('master/vehicle-types', [MasterCrudController::class, 'storeVehicleType']);
        Route::put('master/vehicle-types/{id}', [MasterCrudController::class, 'updateVehicleType']);
        Route::delete('master/vehicle-types/{id}', [MasterCrudController::class, 'destroyVehicleType']);

        Route::post('master/vehicles', [MasterCrudController::class, 'storeVehicle']);
        Route::put('master/vehicles/{id}', [MasterCrudController::class, 'updateVehicle']);
        Route::delete('master/vehicles/{id}', [MasterCrudController::class, 'destroyVehicle']);

        Route::post('master/drivers', [MasterCrudController::class, 'storeDriver']);
        Route::put('master/drivers/{id}', [MasterCrudController::class, 'updateDriver']);
        Route::delete('master/drivers/{id}', [MasterCrudController::class, 'destroyDriver']);

        Route::get('bookings', [VehicleBookingController::class, 'index']);
        Route::post('bookings', [VehicleBookingController::class, 'store']);
        Route::get('bookings/{id}', [VehicleBookingController::class, 'show']);
        Route::put('bookings/{id}', [VehicleBookingController::class, 'update']);
        Route::delete('bookings/{id}', [VehicleBookingController::class, 'destroy']);

        Route::get('reports/bookings/excel', [ReportController::class, 'bookingsExcel']);

        // App logs (admin)
        Route::get('logs', [AppLogController::class, 'index']);
    });

    // Approver
    Route::middleware('role:approver')->group(function () {
        Route::get('approvals/pending', [ApprovalController::class, 'pending']);
        Route::post('approvals/{approvalId}/approve', [ApprovalController::class, 'approve']);
        Route::post('approvals/{approvalId}/reject', [ApprovalController::class, 'reject']);
    });
});
