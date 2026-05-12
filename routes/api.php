<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ReservationLogController;
use App\Http\Controllers\Api\ReportRequestController;
use App\Http\Controllers\Api\ReportController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me/permissions', [AuthController::class, 'getPermissions']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Routes for users
    Route::prefix('users')->group(function () {
        Route::get('/general-data', [UserController::class, 'getGeneralData']);
    });

    Route::apiResource('users', UserController::class);

    // Routes for permissions and roles
    Route::prefix('permissions')->group(function () {
        Route::post('/general-data', [PermissionController::class, 'index']);
        Route::get('/roles/{role}', [PermissionController::class, 'getRolePermissions']);
        Route::post('/roles/{role}/assign', [PermissionController::class, 'assignPermissions']);
    });

    // Resource routes for catalog and reservations
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('equipments', EquipmentController::class);
    Route::apiResource('reservations', ReservationController::class);
    Route::apiResource('reservation-logs', ReservationLogController::class);
    Route::apiResource('report-requests', ReportRequestController::class);
    Route::apiResource('reports', ReportController::class);
});
