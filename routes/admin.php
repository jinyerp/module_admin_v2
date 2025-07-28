<?php
use Illuminate\Support\Facades\Route;

use Jiny\Admin\App\Http\Controllers\Admin\AdminUserController;
use Jiny\Admin\Http\Controllers\AdminSessionController;
Route::prefix('admin/admin')->name('admin.admin.')
->middleware(['web', 'admin:auth'])
->group(function () {
    
    // 관리자 회원 목록
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [
            AdminUserController::class,
            'index'])->name('index'); // 목록 출력
        Route::get('/create', [
            AdminUserController::class,
            'create'])->name('create'); // 생성 폼
        Route::post('/', [
            AdminUserController::class,
            'store'])->name('store'); // 저장
        Route::get('/{id}', [
            AdminUserController::class,
            'show'])->name('show')->where('id', '[0-9]+'); // 상세 조회
        Route::get('/{id}/edit', [
            AdminUserController::class,
            'edit'])->name('edit')->where('id', '[0-9]+'); // 수정폼
        Route::put('/{id}', [
            AdminUserController::class,
            'update'])->name('update')->where('id', '[0-9]+'); // 갱신
        Route::delete('/{id}', [
            AdminUserController::class,
            'destroy'])->name('destroy')->where('id', '[0-9]+'); // 삭제
        Route::get('/{id}/confirm', [
            AdminUserController::class,
            'deleteConfirm'])->name('confirm')->where('id', '[0-9]+'); // 삭제 확인
        Route::post('/bulk-delete', [
            AdminUserController::class,
            'bulkDelete'])->name('bulk-delete'); // 선택 삭제
        // CSV 다운로드 라우트 추가
        Route::get('/download-csv', [
            AdminUserController::class,
            'downloadCsv'])->name('downloadCsv');
        
        // 관리자별 2FA 설정
        Route::get('/{id}/2fa/setup', [
            \Jiny\Admin\Http\Controllers\Admin\AdminUser2FAController::class,
            'setup'])->name('2fa.setup')->where('id', '[0-9]+');
        Route::post('/{id}/2fa/enable', [
            \Jiny\Admin\Http\Controllers\Admin\AdminUser2FAController::class,
            'enable'])->name('2fa.enable')->where('id', '[0-9]+');
        Route::get('/{id}/2fa/manage', [
            \Jiny\Admin\Http\Controllers\Admin\AdminUser2FAController::class,
            'manage'])->name('2fa.manage')->where('id', '[0-9]+');
        Route::post('/{id}/2fa/disable', [
            \Jiny\Admin\Http\Controllers\Admin\AdminUser2FAController::class,
            'disable'])->name('2fa.disable')->where('id', '[0-9]+');
        Route::post('/{id}/2fa/regenerate-backup-codes', [
            \Jiny\Admin\Http\Controllers\Admin\AdminUser2FAController::class,
            'regenerateBackupCodes'])->name('2fa.regenerate-backup-codes')->where('id', '[0-9]+');
    });

});




// 관리자 세션 관리
Route::prefix('admin/sessions')->name('admin.sessions.')->middleware(['web', 'admin:auth'])
->group(function () {
    Route::get('/', [AdminSessionController::class, 'index'])->name('index');
    Route::delete('/{id}', [AdminSessionController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/refresh', [AdminSessionController::class, 'refresh'])->name('refresh');
});

// 관리자 등급 관리
Route::prefix('admin/admin/levels')->name('admin.admin.levels.')->middleware(['web', 'admin:auth'])
->group(function () {
    Route::get('/', [
        \Jiny\Admin\Http\Controllers\AdminLevelController::class, 
        'index'])->name('index');
    Route::get('/create', [
        \Jiny\Admin\Http\Controllers\AdminLevelController::class, 
        'create'])->name('create');
    Route::post('/', [
        \Jiny\Admin\Http\Controllers\AdminLevelController::class, 
        'store'])->name('store');
    Route::get('/{id}/edit', [
        \Jiny\Admin\Http\Controllers\AdminLevelController::class, 
        'edit'])->name('edit');
    Route::put('/{id}', [
        \Jiny\Admin\Http\Controllers\AdminLevelController::class, 
        'update'])->name('update');
    Route::delete('/{id}', [
        \Jiny\Admin\Http\Controllers\AdminLevelController::class, 
        'destroy'])->name('destroy');
});






Route::prefix('admin/logs')->middleware(['web', 'admin:auth'])
    ->name('admin.logs.')->group(function () {

    // 2FA 로그 관리 - 2fa-logs
    Route::prefix('2fa')->name('2fa.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\Admin2FALogController::class, 'index'])->name('index');
        Route::get('/stats', [\Jiny\Admin\Http\Controllers\Admin2FALogController::class, 'stats'])->name('stats');
        Route::get('/export', [\Jiny\Admin\Http\Controllers\Admin2FALogController::class, 'export'])->name('export');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\Admin2FALogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/cleanup', [\Jiny\Admin\Http\Controllers\Admin2FALogController::class, 'cleanup'])->name('cleanup');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\Admin2FALogController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\Admin2FALogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    });

});

// 시스템 관리 라우트
Route::prefix('admin/systems')->middleware(['web', 'admin:auth'])
    ->name('admin.systems.')->group(function () {
    
    // 시스템 대시보드
    Route::get('/', [\Jiny\Admin\Http\Controllers\System\AdminSystemController::class, 'index'])->name('index');
    Route::get('/status', [\Jiny\Admin\Http\Controllers\System\AdminSystemController::class, 'status'])->name('status');
    
    // 백업 로그 관리
    Route::prefix('backup-logs')->name('backup-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'update'])->name('update');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/status', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'updateStatus'])->name('update-status');
        Route::get('/stats', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'stats'])->name('stats');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/export', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'export'])->name('export');
        
        // 백업 실행 및 다운로드
        Route::get('/create-backup', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'createBackup'])->name('create-backup');
        Route::post('/execute-backup', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'executeBackup'])->name('execute-backup');
        Route::get('/{id}/download', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'downloadBackup'])->name('download');
        Route::delete('/{id}/delete-file', [\Jiny\Admin\Http\Controllers\System\SystemBackupLogController::class, 'deleteBackupFile'])->name('delete-file');
    });
    
    // 유지보수 로그 관리
    Route::prefix('maintenance-logs')->name('maintenance-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'update'])->name('update');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/status', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'updateStatus'])->name('update-status');
        Route::get('/stats', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'stats'])->name('stats');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/export', [\Jiny\Admin\Http\Controllers\System\SystemMaintenanceLogController::class, 'export'])->name('export');
    });
    
    // 운영 로그 관리
    Route::prefix('operation-logs')->name('operation-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'index'])->name('index');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'show'])->name('show');
        Route::get('/api', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'apiIndex'])->name('api.index');
        Route::get('/api/{id}', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'apiShow'])->name('api.show');
        Route::get('/api/stats', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'apiStats'])->name('api.stats');
        Route::get('/api/operation-type-analysis', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'operationTypeAnalysis'])->name('api.operation-type-analysis');
        Route::get('/api/performer-analysis', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'performerAnalysis'])->name('api.performer-analysis');
        Route::get('/api/performance-analysis', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'performanceAnalysis'])->name('api.performance-analysis');
        Route::get('/api/time-trend', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'timeTrend'])->name('api.time-trend');
        Route::get('/api/error-analysis', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'errorAnalysis'])->name('api.error-analysis');
        Route::get('/export', [\Jiny\Admin\Http\Controllers\System\SystemOperationLogController::class, 'export'])->name('export');
    });
    
    // 성능 로그 관리
    Route::prefix('performance-logs')->name('performance-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'store'])->name('store');
        Route::get('/stats', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'stats'])->name('stats');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/export', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'export'])->name('export');
        Route::get('/realtime', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'realtime'])->name('realtime');
        
        // ID가 필요한 라우트들
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::put('/{id}/status', [\Jiny\Admin\Http\Controllers\System\SystemPerformanceLogController::class, 'updateStatus'])->name('update-status')->where('id', '[0-9]+');
    });
});


