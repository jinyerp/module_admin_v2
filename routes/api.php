<?php

use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\Admin\AdminPermissionLogController;
use Jiny\Admin\Http\Controllers\Admin\SystemOperationLogController;
use Jiny\Admin\Http\Controllers\AdminSetupController;

// 관리자 API - 사용자 목록
Route::get('/users', function () {
    return response()->json(['message' => 'Admin API - Users List']);
})->name('admin.api.users');

// 관리자 API - 통계
Route::get('/stats', function () {
    return response()->json(['message' => 'Admin API - Statistics']);
})->name('admin.api.stats');

// 관리자 API - 권한 로그
Route::prefix('/permission-logs')->name('permission-logs.')->group(function () {
    Route::get('/', [AdminPermissionLogController::class, 'apiIndex'])->name('index');
    Route::get('/{id}', [AdminPermissionLogController::class, 'apiShow'])->name('show');
    Route::get('/stats', [AdminPermissionLogController::class, 'apiStats'])->name('stats');
    Route::get('/permission-analysis', [AdminPermissionLogController::class, 'permissionAnalysis'])->name('permission-analysis');
    Route::get('/admin-analysis', [AdminPermissionLogController::class, 'adminAnalysis'])->name('admin-analysis');
    Route::get('/resource-analysis', [AdminPermissionLogController::class, 'resourceAnalysis'])->name('resource-analysis');
    Route::get('/time-trend', [AdminPermissionLogController::class, 'timeTrend'])->name('time-trend');
    Route::post('/export', [AdminPermissionLogController::class, 'export'])->name('export');
});

// 관리자 API - 운영 로그
Route::prefix('/operation-logs')->name('operation-logs.')->group(function () {
    Route::get('/', [SystemOperationLogController::class, 'apiIndex'])->name('index');
    Route::get('/{id}', [SystemOperationLogController::class, 'apiShow'])->name('show');
    Route::get('/stats', [SystemOperationLogController::class, 'apiStats'])->name('stats');
    Route::get('/operation-type-analysis', [SystemOperationLogController::class, 'operationTypeAnalysis'])->name('operation-type-analysis');
    Route::get('/performer-analysis', [SystemOperationLogController::class, 'performerAnalysis'])->name('performer-analysis');
    Route::get('/performance-analysis', [SystemOperationLogController::class, 'performanceAnalysis'])->name('performance-analysis');
    Route::get('/time-trend', [SystemOperationLogController::class, 'timeTrend'])->name('time-trend');
    Route::get('/error-analysis', [SystemOperationLogController::class, 'errorAnalysis'])->name('error-analysis');
    Route::post('/export', [SystemOperationLogController::class, 'export'])->name('export');
});

// 관리자 최초 설정 라우트 제거됨
