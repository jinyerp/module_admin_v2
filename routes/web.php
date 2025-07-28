<?php
use Illuminate\Support\Facades\Route;

$adminPrefix = config('admin.settings.prefix', 'admin');




/**
 * 1. 관리자 최초 설정
 * 인증 없이 접근 가능
 */

use Jiny\Admin\App\Http\Controllers\Auth\AdminSetupController;
Route::prefix($adminPrefix)->middleware(['web'])->name('admin.')
    ->group(function () {
    Route::get('/setup', [
        AdminSetupController::class, 'index'])->name('setup');
    Route::post('/setup/migrate', [
        AdminSetupController::class, 'migrate'])->name('setup.migrate');
    Route::post('/setup/superadmin', [
        AdminSetupController::class, 'createSuperAdmin'])->name('setup.superadmin');
});

/**
 * 1.관리자 Session 로그인
 * admin:guest 미들웨어 적용, session 인증 없이 접근 가능
 */
use Jiny\Admin\Http\Controllers\Auth\AdminSessionLogin;
use Jiny\Admin\App\Http\Controllers\Auth\AdminLoginFormController;
Route::prefix($adminPrefix)->middleware(['web'])->name('admin.')
    ->group(function () {
    // 로그인 폼
    Route::get('/login', [
        AdminLoginFormController::class,
        'showLoginForm'])->name('login');

    // 로그인 처리
    Route::post('/login', [
        AdminSessionLogin::class,
        'login'])->name('login.store');

    Route::post('/login/ajax', [
        AdminSessionLogin::class,
        'loginAjax'])->name('login.ajax');
});

// 관리자 인증 라우트 그룹 (web 미들웨어 적용)
Route::prefix($adminPrefix)->middleware(['web'])->name('admin.')
    ->group(function () {
    // 로그아웃 처리
    Route::get('/logout', [
        AdminSessionLogin::class,
        'logout'])->name('logout');
});



use Jiny\Admin\Http\Controllers\AdminDashboard;
use Jiny\Admin\Http\Controllers\DatabaseController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationActionController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationStatusController;
use Jiny\Admin\Http\Controllers\MigrationListController;
use Jiny\Admin\Http\Controllers\Logs\AdminUserLogController;
use Jiny\Admin\Http\Controllers\Logs\AdminActivityLogController;
use Jiny\Admin\Http\Controllers\Logs\AdminAuditLogController;
use Jiny\Admin\Http\Controllers\AdminSettingMailController;

/**
 * 3. 2FA 인증
 * 2FA 인증 라우트 (로그인 후, 2FA 검증이 필요한 페이지)
 */
Route::prefix($adminPrefix)->middleware(['web', 'admin:auth'])->name('admin.')
    ->group(function () {
    // 2FA 인증 페이지
    Route::get('/2fa/challenge', [
        \Jiny\Admin\Http\Controllers\Auth\AdminTwoFactorController::class,
        'challenge'])->name('2fa.challenge');
    // 2FA 인증 처리
    Route::post('/2fa/verify', [
        \Jiny\Admin\Http\Controllers\Auth\AdminTwoFactorController::class,
        'verify'])->name('2fa.verify');
});

/**
 * 4. 메일 환경설정
 * 인증 필요
 */
// 메일 환경설정 라우트
Route::prefix($adminPrefix)->middleware(['web', 'admin:auth'])->name('admin.')
    ->group(function () {
    Route::get('/setting/mail', [
        AdminSettingMailController::class, 
        'index'])->name('setting.mail');
    Route::put('/setting/mail', [
        AdminSettingMailController::class, 
        'update'])->name('setting.mail.update');
    Route::post('/setting/mail/test', [
        AdminSettingMailController::class, 
        'test'])->name('setting.mail.test');
});

// 관리자 대시보드 (인증 필요)
Route::prefix($adminPrefix)->middleware(['web', 'admin:auth'])->name('admin.')
    ->group(function () {
    Route::get('/dashboard', [
        AdminDashboard::class,
        'index'])->name('dashboard');
    Route::get('/', [
        AdminDashboard::class,
        'index'])->name('dashboard');
    // 데이터베이스 관리
    Route::prefix('database')->name('database.')->group(function () {
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

// monitoring, admin, system 등 하위 라우트도 동일하게 prefix($adminPrefix)->middleware([...]) 구조로 통일
Route::prefix("$adminPrefix/monitoring")->middleware(['web', 'admin:auth'])->name('admin.monitoring.')->group(function () {

});


/**
 * admin/admin 관리기능
 */
Route::prefix("$adminPrefix/admin")->middleware(['web', 'admin:auth'])->name('admin.admin.')->group(function () {

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


Route::prefix("$adminPrefix/system")->middleware(['web', 'admin:auth'])->name('admin.system.')->group(function () {

    // 시스템 성능 로그 관리
    Route::prefix('performance-logs')->name('performance-logs.')->group(function () {
        Route::get('/', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'index'])->name('index');
        Route::get('/create', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'create'])->name('create');
        Route::post('/', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'store'])->name('store');
        Route::get('/{id}', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/bulk-delete', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/download-csv', [\Jiny\Admin\Http\Controllers\SystemPerformanceLogController::class, 'downloadCsv'])->name('downloadCsv');
    });

});


/**
 * 추가설정정
 */
use Jiny\Admin\Http\Controllers\Admin\AdminCountryController;
use Jiny\Admin\Http\Controllers\Admin\AdminLanguageController;
Route::prefix($adminPrefix)->middleware(['web', 'admin:auth'])->name('admin.')
    ->group(function () {

    // 국가 관리
    Route::prefix("country")->name('country.')
    ->group(function () {
        Route::get('/', [
            AdminCountryController::class, 
            'index'])->name('index');
        Route::get('/create', [
            AdminCountryController::class, 
            'create'])->name('create');
        Route::post('/', [
            AdminCountryController::class, 
            'store'])->name('store');
        Route::get('/{id}/edit', [
            AdminCountryController::class, 
            'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [
            AdminCountryController::class, 
            'update'])->name('update');
        Route::delete('/{id}', [
            AdminCountryController::class, 
            'destroy'])->name('destroy');
        Route::post('/{id}/toggle-enable', [
            AdminCountryController::class,
            'toggleEnableAjax'
        ])->name('toggle-enable')->where('id', '[0-9]+');
        Route::post('/enable-all', [
            AdminCountryController::class,
            'enableAllAjax'
        ])->name('enable-all');
        Route::get('/{id}', [
            AdminCountryController::class,
            'show'
        ])->name('show')->where('id', '[0-9]+');
    });

    // 언어 관리
    Route::prefix("language")->name('language.')
    ->group(function () {
        Route::get('/', [AdminLanguageController::class, 'index'])->name('index');
        Route::get('/create', [AdminLanguageController::class, 'create'])->name('create');
        Route::post('/', [AdminLanguageController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminLanguageController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [AdminLanguageController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminLanguageController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-enable', [AdminLanguageController::class, 'toggleEnableAjax'])->name('toggle-enable')->where('id', '[0-9]+');
        Route::post('/enable-all', [AdminLanguageController::class, 'enableAllAjax'])->name('enable-all');
        Route::get('/{id}', [AdminLanguageController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::post('/bulk-delete', [AdminLanguageController::class, 'bulkDelete'])->name('bulk-delete');
    });

});

