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
    Route::get('/me', [AuthController::class, 'me']);
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

    Route::prefix('equipments')->group(function () {
        Route::get('/general-data', [EquipmentController::class, 'getGeneralData']);
    });
    Route::apiResource('equipments', EquipmentController::class);
    
    Route::prefix('reservations')->group(function () {
        Route::get('/general-data', [ReservationController::class, 'getGeneralData']);
        Route::post('/{id}/approve', [ReservationController::class, 'approve']);
        Route::post('/{id}/reject', [ReservationController::class, 'reject']);
    });
    Route::apiResource('reservations', ReservationController::class);
    
    Route::apiResource('reservation-logs', ReservationLogController::class);
    Route::prefix('report-requests')->group(function () {
        Route::get('/general-data', [ReportRequestController::class, 'getGeneralData']);
    });
    Route::apiResource('report-requests', ReportRequestController::class);
    Route::apiResource('reports', ReportController::class);
    Route::prefix('report-requests')->group(function () {
        Route::get('/general-data', [ReportRequestController::class, 'getGeneralData']);
        Route::post('/{reportRequest}/generate', [ReportController::class, 'generate']);
    });
});
