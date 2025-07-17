<?php
use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\Auth\AdminSessionLogin;
use Jiny\Admin\Http\Controllers\AdminDashboard;

use Jiny\Admin\Http\Controllers\AdminMigrationController;
use Jiny\Admin\Http\Controllers\DatabaseController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationActionController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationStatusController;
use Jiny\Admin\Http\Controllers\MigrationListController;

use Jiny\Admin\Http\Controllers\Logs\AdminUserLogController;
use Jiny\Admin\Http\Controllers\Logs\AdminActivityLogController;
use Jiny\Admin\Http\Controllers\Logs\AdminAuditLogController;

use Jiny\Admin\Http\Controllers\AdminSetupController;


// 관리자 인증 라우트 그룹 (web 미들웨어 적용)
Route::middleware(['web'])->group(function () {
    // 로그인 폼
    Route::get('/admin/login', [
        AdminSessionLogin::class,
        'showLoginForm'])->name('admin.login');

    // 로그인 처리
    Route::post('/admin/login', [
        AdminSessionLogin::class,
        'login'])->name('admin.login.store');

    Route::post('/admin/login/ajax', [
        AdminSessionLogin::class,
        'loginAjax'])->name('admin.login.ajax');

    // 로그아웃 처리
    Route::get('/admin/logout', [
        AdminSessionLogin::class,
        'logout'])->name('admin.logout');
});

// 관리자 최초 설정 (인증 없이 접근 가능)
Route::middleware(['web'])->group(function () {
    Route::get('/admin/setup', [AdminSetupController::class, 'index'])->name('admin.setup');
    Route::post('/admin/setup/migrate', [AdminSetupController::class, 'migrate'])->name('admin.setup.migrate');
    Route::post('/admin/setup/superadmin', [AdminSetupController::class, 'createSuperAdmin'])->name('admin.setup.superadmin');
    
});


use Jiny\Admin\Http\Controllers\AdminSettingMailController;

// 메일 환경설정 라우트
Route::middleware(['web', 'admin:auth'])->group(function () {
    Route::get('/admin/setting/mail', [
        AdminSettingMailController::class, 
        'index'])->name('admin.setting.mail');
    Route::put('/admin/setting/mail', [
        AdminSettingMailController::class, 
        'update'])->name('admin.setting.mail.update');
    Route::post('/admin/setting/mail/test', [
        AdminSettingMailController::class, 
        'test'])->name('admin.setting.mail.test');
});


// 관리자 대시보드 (인증 필요)
Route::prefix('admin')->middleware(['web', 'admin:auth'])->name('admin.')->group(function () {
    Route::get('/dashboard', [
        AdminDashboard::class,
        'index'])->name('dashboard');
    
    Route::get('/', [
            AdminDashboard::class,
            'index'])->name('dashboard');
    
    // // 마이그레이션 관리
    // Route::prefix('migrations')->name('migrations.')->group(function () {
    //     Route::get('/', [AdminMigrationController::class, 'index'])->name('index');
    //     Route::get('/status', [AdminMigrationController::class, 'status'])->name('status');
    //     Route::post('/run', [AdminMigrationController::class, 'run'])->name('run');
    //     Route::post('/rollback', [AdminMigrationController::class, 'rollback'])->name('rollback');
    //     Route::post('/refresh', [AdminMigrationController::class, 'refresh'])->name('refresh');
    // });

    // 데이터베이스 관리
    Route::prefix('databases')->name('databases.')->group(function () {
        // 데이터베이스 대시보드
        Route::get('/', [DatabaseController::class, 'index'])->name('index');
        
        // 마이그레이션 관리
        Route::prefix('migrations')->name('migrations.')->group(function () {
            Route::get('/', [MigrationListController::class, 'index'])->name('index');
            Route::get('/{id}', [
                MigrationListController::class, 
                'show'])->where('id', '[0-9]+')->name('show');
            
            // 마이그레이션 액션
            Route::post('/run', [DatabaseMigrationActionController::class, 'run'])->name('run');
            Route::post('/rollback', [DatabaseMigrationActionController::class, 'rollback'])->name('rollback');
            Route::post('/refresh', [DatabaseMigrationActionController::class, 'refresh'])->name('refresh');
            Route::post('/reset', [DatabaseMigrationActionController::class, 'reset'])->name('reset');
            Route::post('/run-specific/{migration}', [DatabaseMigrationActionController::class, 'runSpecific'])->name('run-specific');
            
            // 마이그레이션 상태 확인 (AJAX용)
            Route::get('/status/check', [DatabaseMigrationActionController::class, 'status'])->name('status-check');
            
            // 마이그레이션 상태
            Route::get('/status', [DatabaseMigrationStatusController::class, 'status'])->name('status');
            Route::get('/status/api', [DatabaseMigrationStatusController::class, 'statusApi'])->name('status-api');
            Route::get('/batches', [DatabaseMigrationStatusController::class, 'batches'])->name('batches');
            Route::get('/batches/{batch}/migrations', [DatabaseMigrationStatusController::class, 'batchMigrations'])->name('batch-migrations');
        });
    });
});


/**
 * admin/admin 관리기능
 */
Route::prefix('admin/admin')->middleware(['web', 'admin:auth'])
    ->name('admin.admin.')->group(function () {

    // // 관리자 사용자 로그 CRUD 라우트
    // Route::prefix('logs/user')->name('logs.user.')->group(function () {
    //     Route::get('/', [AdminUserLogController::class, 'index'])->name('index');
    //     Route::get('/create', [AdminUserLogController::class, 'create'])->name('create');
    //     Route::post('/', [AdminUserLogController::class, 'store'])->name('store');
    //     Route::get('/stats', [AdminUserLogController::class, 'stats'])->name('stats');
    //     Route::get('/admin/{adminUserId}/stats', [AdminUserLogController::class, 'adminStats'])->name('admin-stats');
    //     Route::post('/export', [AdminUserLogController::class, 'export'])->name('export');
    //     Route::post('/bulk-delete', [AdminUserLogController::class, 'bulkDelete'])->name('bulk-delete');
    //     Route::post('/cleanup', [AdminUserLogController::class, 'cleanup'])->name('cleanup');

    //     // UUID 기반 라우트
    //     Route::get('/{userLog}', [AdminUserLogController::class, 'show'])->name('show')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    //     Route::get('/{userLog}/edit', [AdminUserLogController::class, 'edit'])->name('edit')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    //     Route::put('/{userLog}', [AdminUserLogController::class, 'update'])->name('update')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    //     Route::delete('/{userLog}', [AdminUserLogController::class, 'destroy'])->name('destroy')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    // });


    // 활동 로그 관리 - activity-log (신규)
    Route::prefix('activity-log')->name('activity-log.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'store'])->name('store');
        Route::get('/stats', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'stats'])->name('stats');
        Route::get('/admin/{adminId}/stats', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'adminStats'])->name('admin-stats');
        Route::post('/export', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'export'])->name('export');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/cleanup', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'cleanup'])->name('cleanup');
        Route::get('/{activityLog}', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'show'])->name('show');
        Route::get('/{activityLog}/edit', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'edit'])->name('edit');
        Route::put('/{activityLog}', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'update'])->name('update');
        Route::delete('/{activityLog}', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'destroy'])->name('destroy');
        Route::get('/download-csv', [\Jiny\Admin\Http\Controllers\AdminActivityLogController::class, 'downloadCsv'])->name('downloadCsv');
    });


    

    // 1.관리자 회원 목록
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'index'])->name('index'); // 목록 출력
        Route::get('/create', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'create'])->name('create'); // 생성 폼
        Route::post('/', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'store'])->name('store'); // 저장
        Route::get('/{id}', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'show'])->name('show')->where('id', '[0-9]+'); // 상세 조회
        Route::get('/{id}/edit', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'edit'])->name('edit')->where('id', '[0-9]+'); // 수정폼
        Route::put('/{id}', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'update'])->name('update')->where('id', '[0-9]+'); // 갱신
        Route::delete('/{id}', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'destroy'])->name('destroy')->where('id', '[0-9]+'); // 삭제
        Route::post('/bulk-delete', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'bulkDelete'])->name('bulk-delete'); // 선택 삭제
        // CSV 다운로드 라우트 추가
        Route::get('/download-csv', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'downloadCsv'])->name('downloadCsv');
    });

    // 2.관리자 사용자 로그 관리
    Route::prefix('user-logs')->name('user-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'show'])->name('show')->where('id', '[0-9a-fA-F-]+');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'edit'])->name('edit')->where('id', '[0-9a-fA-F-]+');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'update'])->name('update')->where('id', '[0-9a-fA-F-]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9a-fA-F-]+');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'bulkDelete'])->name('bulk-delete');
        // CSV 다운로드 라우트 추가
        Route::get('/download-csv', [\Jiny\Admin\Http\Controllers\AdminUserLogController::class, 'downloadCsv'])->name('downloadCsv');
    });

    // 관리자 감사 로그 관리 - audit-logs (신규)
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'bulkDelete'])->name('bulk-delete');
        // CSV 다운로드 라우트 추가
        Route::get('/download-csv', [\Jiny\Admin\Http\Controllers\AdminAuditLogController::class, 'downloadCsv'])->name('downloadCsv');
    });

    // 권한 관리 - permissions
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/download-csv', [\Jiny\Admin\Http\Controllers\AdminPermissionController::class, 'downloadCsv'])->name('downloadCsv');
    });

    // 사용자 권한 관리 - user-permissions
    Route::prefix('user-permissions')->name('user-permissions.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/download-csv', [\Jiny\Admin\Http\Controllers\AdminUserPermissionController::class, 'downloadCsv'])->name('downloadCsv');
    });

    // 권한 로그 관리 - permission-logs
    Route::prefix('permission-logs')->name('permission-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/download-csv', [\Jiny\Admin\Http\Controllers\AdminPermissionLogController::class, 'downloadCsv'])->name('downloadCsv');
    });



});

