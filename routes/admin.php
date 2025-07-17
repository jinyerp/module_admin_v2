<?php
use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\Admin\AdminCountryController;
use Jiny\Admin\Http\Controllers\Admin\AdminPermissionController;
use Jiny\Admin\Http\Controllers\Admin\AdminSettingsController;
use Jiny\Admin\Http\Controllers\Admin\AdminLanguageController;
use Jiny\Admin\Http\Controllers\Admin\AdminCurrencyController;
use Jiny\Admin\Http\Controllers\Admin\SystemBackupLogController;
use Jiny\Admin\Http\Controllers\Admin\SystemMaintenanceLogController;
use Jiny\Admin\Http\Controllers\Admin\SystemPerformanceLogController;
use Jiny\Admin\Http\Controllers\Admin\AdminPermissionLogController;
use Jiny\Admin\Http\Controllers\Admin\SystemOperationLogController;
use Jiny\Admin\Http\Controllers\Admin\DatabaseMigrationController;
use Jiny\Admin\Http\Controllers\Admin\DatabaseMigrationActionController;
use Jiny\Admin\Http\Controllers\Admin\DatabaseMigrationStatusController;
use App\Models\Country;
use Jiny\Admin\Http\Controllers\Menu\AdminSideMenuController;
use Jiny\Admin\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminUserPermissionController;


use Jiny\Auth\Http\Controllers\Admin\AdminAuthSessionController;

// // 관리자 로그인 (jiny/auth 패키지로 이동됨)
// // Route::middleware(['web'])->group(function () {
// //     Route::prefix('/admin')->name('admin.')
// //     ->group(function () {
// //         // 관리자 로그인
// //         Route::get('/login', [
// //             AdminAuthSessionController::class,
// //             'login'])->name('login');
// //         // 관리자 로그인 처리
// //         Route::post('/login', [
// //             AdminAuthSessionController::class,
// //             'authenticate'])->name('login.store');
// //         // 관리자 로그아웃
// //         Route::get('/logout', [
// //             AdminAuthSessionController::class,
// //             'logout'])->name('logout');
// //     });
// // });


// // 관리자 대시보드 (jiny/auth 패키지로 이동됨)
// // Route::prefix('/admin')->name('admin.')->group(function () {
// //     Route::get('/', [AdminAuthSessionController::class, 'dashboard'])->name('dashboard');
// // });


//         // ========================================
//         // 인증 관리 (jiny/auth 패키지로 이동됨)
//         // ========================================

//         // Route::prefix('/admin/auth')->name('admin.auth.')->group(function () {
//         //     // 인증 관리 메인
//         //     Route::get('/', [AdminAuthSessionController::class, 'index'])->name('index');

//         //     // 승인 대기 중인 회원
//         //     Route::get('/pending', [AdminAuthSessionController::class, 'pending'])->name('pending');

//         //     // 승인된 회원
//         //     Route::get('/approved', [AdminAuthSessionController::class, 'approved'])->name('approved');

//         //     // 거부된 회원
//         //     Route::get('/rejected', [AdminAuthSessionController::class, 'rejected'])->name('rejected');

//         //     // 인증 통계
//         //     Route::get('/stats', [AdminAuthSessionController::class, 'stats'])->name('stats');

//         //     // 회원 승인/거부 처리
//         //     Route::post('/{user}/approve', [AdminAuthSessionController::class, 'approveUser'])->name('approve');
//         //     Route::post('/{user}/reject', [AdminAuthSessionController::class, 'rejectUser'])->name('reject');
//         // });




// Route::prefix('/admin')->name('admin.')->group(function () {

//     // 시스템 관리 - 국가 관리
//     Route::prefix('/system/countries')->name('system.countries.')->group(function () {
//         Route::get('/', [AdminCountryController::class, 'index'])->name('index');
//         Route::get('/create', [AdminCountryController::class, 'create'])->name('create');
//         Route::post('/', [AdminCountryController::class, 'store'])->name('store');
//         Route::get('/{country}', [AdminCountryController::class, 'show'])->name('show')->where('country', '[0-9]+');
//         Route::get('/{country}/edit', [AdminCountryController::class, 'edit'])->name('edit')->where('country', '[0-9]+');
//         Route::put('/{country}', [AdminCountryController::class, 'update'])->name('update')->where('country', '[0-9]+');
//         Route::delete('/{country}', [AdminCountryController::class, 'destroy'])->name('destroy')->where('country', '[0-9]+');
//         Route::patch('/{country}/toggle-active', [AdminCountryController::class, 'toggleActive'])->name('toggle-active')->where('country', '[0-9]+');
//         Route::patch('/{country}/set-default', [AdminCountryController::class, 'setDefault'])->name('set-default')->where('country', '[0-9]+');
//         Route::post('/update-order', [AdminCountryController::class, 'updateOrder'])->name('update-order');
//         Route::get('/stats', [AdminCountryController::class, 'stats'])->name('stats');
//         Route::post('/bulk-delete', [AdminCountryController::class, 'bulkDelete'])->name('bulk-delete');
//     });

//     // 시스템 관리 - 권한 관리
//     Route::prefix('/system/permissions')->name('permissions.')->group(function () {
//         Route::get('/', [AdminPermissionController::class, 'index'])->name('index');
//         Route::get('/create', [AdminPermissionController::class, 'create'])->name('create');
//         Route::post('/', [AdminPermissionController::class, 'store'])->name('store');
//         Route::get('/{permission}', [AdminPermissionController::class, 'show'])->name('show')->where('permission', '[0-9]+');
//         Route::get('/{permission}/edit', [AdminPermissionController::class, 'edit'])->name('edit')->where('permission', '[0-9]+');
//         Route::put('/{permission}', [AdminPermissionController::class, 'update'])->name('update')->where('permission', '[0-9]+');
//         Route::delete('/{permission}', [AdminPermissionController::class, 'destroy'])->name('destroy')->where('permission', '[0-9]+');
//         Route::patch('/{permission}/toggle-active', [AdminPermissionController::class, 'toggleActive'])->name('toggle-active')->where('permission', '[0-9]+');
//         Route::post('/update-order', [AdminPermissionController::class, 'updateOrder'])->name('update-order');
//         Route::get('/stats', [AdminPermissionController::class, 'stats'])->name('stats');
//         Route::post('/bulk-delete', [AdminPermissionController::class, 'bulkDelete'])->name('bulk-delete');
//     });

//     // 시스템 관리 - 언어 관리
//     Route::prefix('/system/languages')->name('languages.')->group(function () {
//         Route::get('/', [AdminLanguageController::class, 'index'])->name('index');
//         Route::get('/create', [AdminLanguageController::class, 'create'])->name('create');
//         Route::post('/', [AdminLanguageController::class, 'store'])->name('store');
//         Route::get('/{language}', [AdminLanguageController::class, 'show'])->name('show')->where('language', '[0-9]+');
//         Route::get('/{language}/edit', [AdminLanguageController::class, 'edit'])->name('edit')->where('language', '[0-9]+');
//         Route::put('/{language}', [AdminLanguageController::class, 'update'])->name('update')->where('language', '[0-9]+');
//         Route::delete('/{language}', [AdminLanguageController::class, 'destroy'])->name('destroy')->where('language', '[0-9]+');
//         Route::patch('/{language}/toggle-active', [AdminLanguageController::class, 'toggleActive'])->name('toggle-active')->where('language', '[0-9]+');
//         Route::patch('/{language}/set-default', [AdminLanguageController::class, 'setDefault'])->name('set-default')->where('language', '[0-9]+');
//         Route::post('/update-order', [AdminLanguageController::class, 'updateOrder'])->name('update-order');
//         Route::get('/stats', [AdminLanguageController::class, 'stats'])->name('stats');
//         Route::post('/bulk-delete', [AdminLanguageController::class, 'bulkDelete'])->name('bulk-delete');
//     });

//     // 시스템 관리 - 통화 관리
//     Route::prefix('/system/currencies')->name('currencies.')->group(function () {
//         Route::get('/', [AdminCurrencyController::class, 'index'])->name('index');
//         Route::get('/create', [AdminCurrencyController::class, 'create'])->name('create');
//         Route::post('/', [AdminCurrencyController::class, 'store'])->name('store');
//         Route::get('/{currency}', [AdminCurrencyController::class, 'show'])->name('show')->where('currency', '[0-9]+');
//         Route::get('/{currency}/edit', [AdminCurrencyController::class, 'edit'])->name('edit')->where('currency', '[0-9]+');
//         Route::put('/{currency}', [AdminCurrencyController::class, 'update'])->name('update')->where('currency', '[0-9]+');
//         Route::delete('/{currency}', [AdminCurrencyController::class, 'destroy'])->name('destroy')->where('currency', '[0-9]+');
//         Route::patch('/{currency}/toggle-active', [AdminCurrencyController::class, 'toggleActive'])->name('toggle-active')->where('currency', '[0-9]+');
//         Route::patch('/{currency}/set-default', [AdminCurrencyController::class, 'setDefault'])->name('set-default')->where('currency', '[0-9]+');
//         Route::post('/update-order', [AdminCurrencyController::class, 'updateOrder'])->name('update-order');
//         Route::patch('/{currency}/update-exchange-rate', [AdminCurrencyController::class, 'updateExchangeRate'])->name('update-exchange-rate')->where('currency', '[0-9]+');
//         Route::get('/stats', [AdminCurrencyController::class, 'stats'])->name('stats');
//         Route::post('/bulk-delete', [AdminCurrencyController::class, 'bulkDelete'])->name('bulk-delete');
//     });

//     // 시스템 관리 - 백업 로그
//     Route::prefix('/system/backup-logs')->name('system-backup-logs.')->group(function () {
//         Route::get('/', [SystemBackupLogController::class, 'index'])->name('index');
//         Route::get('/create', [SystemBackupLogController::class, 'create'])->name('create');
//         Route::post('/', [SystemBackupLogController::class, 'store'])->name('store');
//         Route::get('/{systemBackupLog}', [SystemBackupLogController::class, 'show'])->name('show')->where('systemBackupLog', '[0-9]+');
//         Route::get('/{systemBackupLog}/edit', [SystemBackupLogController::class, 'edit'])->name('edit')->where('systemBackupLog', '[0-9]+');
//         Route::put('/{systemBackupLog}', [SystemBackupLogController::class, 'update'])->name('update')->where('systemBackupLog', '[0-9]+');
//         Route::delete('/{systemBackupLog}', [SystemBackupLogController::class, 'destroy'])->name('destroy')->where('systemBackupLog', '[0-9]+');
//         Route::patch('/{systemBackupLog}/update-status', [SystemBackupLogController::class, 'updateStatus'])->name('update-status')->where('systemBackupLog', '[0-9]+');
//         Route::get('/stats', [SystemBackupLogController::class, 'stats'])->name('stats');
//         Route::post('/bulk-delete', [SystemBackupLogController::class, 'bulkDelete'])->name('bulk-delete');
//         Route::post('/export', [SystemBackupLogController::class, 'export'])->name('export');
//     });

//     // 시스템 관리 - 유지보수 로그
//     Route::prefix('/system/maintenance-logs')->name('system-maintenance-logs.')->group(function () {
//         Route::get('/', [SystemMaintenanceLogController::class, 'index'])->name('index');
//         Route::get('/create', [SystemMaintenanceLogController::class, 'create'])->name('create');
//         Route::post('/', [SystemMaintenanceLogController::class, 'store'])->name('store');
//         Route::get('/{systemMaintenanceLog}', [SystemMaintenanceLogController::class, 'show'])->name('show')->where('systemMaintenanceLog', '[0-9]+');
//         Route::get('/{systemMaintenanceLog}/edit', [SystemMaintenanceLogController::class, 'edit'])->name('edit')->where('systemMaintenanceLog', '[0-9]+');
//         Route::put('/{systemMaintenanceLog}', [SystemMaintenanceLogController::class, 'update'])->name('update')->where('systemMaintenanceLog', '[0-9]+');
//         Route::delete('/{systemMaintenanceLog}', [SystemMaintenanceLogController::class, 'destroy'])->name('destroy')->where('systemMaintenanceLog', '[0-9]+');
//         Route::patch('/{systemMaintenanceLog}/update-status', [SystemMaintenanceLogController::class, 'updateStatus'])->name('update-status')->where('systemMaintenanceLog', '[0-9]+');
//         Route::get('/stats', [SystemMaintenanceLogController::class, 'stats'])->name('stats');
//         Route::post('/bulk-delete', [SystemMaintenanceLogController::class, 'bulkDelete'])->name('bulk-delete');
//         Route::post('/export', [SystemMaintenanceLogController::class, 'export'])->name('export');
//     });

//     // 시스템 관리 - 성능 로그
//     Route::prefix('/system/performance-logs')->name('system-performance-logs.')->group(function () {
//         Route::get('/', [SystemPerformanceLogController::class, 'index'])->name('index');
//         Route::get('/create', [SystemPerformanceLogController::class, 'create'])->name('create');
//         Route::post('/', [SystemPerformanceLogController::class, 'store'])->name('store');
//         Route::get('/{systemPerformanceLog}', [SystemPerformanceLogController::class, 'show'])->name('show')->where('systemPerformanceLog', '[0-9]+');
//         Route::get('/{systemPerformanceLog}/edit', [SystemPerformanceLogController::class, 'edit'])->name('edit')->where('systemPerformanceLog', '[0-9]+');
//         Route::put('/{systemPerformanceLog}', [SystemPerformanceLogController::class, 'update'])->name('update')->where('systemPerformanceLog', '[0-9]+');
//         Route::delete('/{systemPerformanceLog}', [SystemPerformanceLogController::class, 'destroy'])->name('destroy')->where('systemPerformanceLog', '[0-9]+');
//         Route::patch('/{systemPerformanceLog}/update-status', [SystemPerformanceLogController::class, 'updateStatus'])->name('update-status')->where('systemPerformanceLog', '[0-9]+');
//         Route::get('/stats', [SystemPerformanceLogController::class, 'stats'])->name('stats');
//         Route::post('/bulk-delete', [SystemPerformanceLogController::class, 'bulkDelete'])->name('bulk-delete');
//         Route::post('/export', [SystemPerformanceLogController::class, 'export'])->name('export');
//         Route::get('/realtime', [SystemPerformanceLogController::class, 'realtime'])->name('realtime');
//     });

//     // 시스템 관리 - 환경설정
//     Route::prefix('/settings')->name('settings.')->group(function () {
//         Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
//         Route::get('/auth', [AdminSettingsController::class, 'auth'])->name('auth');
//         Route::post('/auth', [AdminSettingsController::class, 'updateAuth'])->name('update-auth');
//         Route::get('/database', [AdminSettingsController::class, 'database'])->name('database');
//         Route::post('/database', [AdminSettingsController::class, 'updateDatabase'])->name('update-database');
//         Route::get('/mail', [AdminSettingsController::class, 'mail'])->name('mail');
//         Route::post('/mail', [AdminSettingsController::class, 'updateMail'])->name('update-mail');
//         Route::get('/system', [AdminSettingsController::class, 'system'])->name('system');
//         Route::post('/system', [AdminSettingsController::class, 'updateSystem'])->name('update-system');
//         Route::post('/test-connection', [AdminSettingsController::class, 'testConnection'])->name('test-connection');
//         Route::post('/test-mail', [AdminSettingsController::class, 'testMail'])->name('test-mail');
//     });

//     // 시스템 관리 - 권한 로그
//     Route::prefix('/system/permission-logs')->name('permission-logs.')->group(function () {
//         Route::get('/', [AdminPermissionLogController::class, 'index'])->name('index');
//         Route::get('/{permissionLog}', [AdminPermissionLogController::class, 'show'])->name('show')->where('permissionLog', '[0-9]+');
//         Route::get('/stats', [AdminPermissionLogController::class, 'apiStats'])->name('stats');
//         Route::get('/permission-analysis', [AdminPermissionLogController::class, 'permissionAnalysis'])->name('permission-analysis');
//         Route::get('/admin-analysis', [AdminPermissionLogController::class, 'adminAnalysis'])->name('admin-analysis');
//         Route::get('/resource-analysis', [AdminPermissionLogController::class, 'resourceAnalysis'])->name('resource-analysis');
//         Route::get('/time-trend', [AdminPermissionLogController::class, 'timeTrend'])->name('time-trend');
//         Route::post('/export', [AdminPermissionLogController::class, 'export'])->name('export');
//     });

//     // 시스템 관리 - 운영 로그
//     Route::prefix('/system/operation-logs')->name('operation-logs.')->group(function () {
//         Route::get('/', [SystemOperationLogController::class, 'index'])->name('index');
//         Route::get('/{operationLog}', [SystemOperationLogController::class, 'show'])->name('show')->where('operationLog', '[0-9]+');
//         Route::get('/stats', [SystemOperationLogController::class, 'apiStats'])->name('stats');
//         Route::get('/operation-type-analysis', [SystemOperationLogController::class, 'operationTypeAnalysis'])->name('operation-type-analysis');
//         Route::get('/performer-analysis', [SystemOperationLogController::class, 'performerAnalysis'])->name('performer-analysis');
//         Route::get('/performance-analysis', [SystemOperationLogController::class, 'performanceAnalysis'])->name('performance-analysis');
//         Route::get('/time-trend', [SystemOperationLogController::class, 'timeTrend'])->name('time-trend');
//         Route::get('/error-analysis', [SystemOperationLogController::class, 'errorAnalysis'])->name('error-analysis');
//         Route::post('/export', [SystemOperationLogController::class, 'export'])->name('export');
//     });

//     // 시스템 관리 - 메뉴 관리
//     Route::prefix('/system/menu')->name('admin.system.menu.')->group(function () {
//         Route::get('/', [AdminSideMenuController::class, 'index'])->name('index');
//         Route::get('/edit', [AdminSideMenuController::class, 'edit'])->name('edit');
//         Route::post('/update', [AdminSideMenuController::class, 'update'])->name('update');
//         Route::get('/data', [AdminSideMenuController::class, 'getMenuData'])->name('data');
//         Route::post('/reload', [AdminSideMenuController::class, 'reload'])->name('reload');
//     });

//     // 관리자 메시지 관리
//     Route::prefix('admin/messages')->name('admin.messages.')->group(function () {
//         Route::get('/', [AdminMessageController::class, 'index'])->name('index');
//         Route::get('/create', [AdminMessageController::class, 'create'])->name('create');
//         Route::post('/', [AdminMessageController::class, 'store'])->name('store');
//         // 기타 show, edit, update, destroy 등 필요시 추가
//     });

//     // 관리자 설정 관리
//     Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
//         Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
//         // 기타 general, security, email 등 필요시 추가
//     });

//     // 관리자 권한 관리
//     Route::prefix('admin/permissions')->name('admin.permissions.')->group(function () {
//         Route::get('/', [AdminPermissionController::class, 'index'])->name('index');
//         // 기타 create, store, edit, update, destroy 등 필요시 추가
//     });

//     // 관리자별 권한 할당 관리
//     Route::prefix('admin/user-permissions')->name('admin.user-permissions.')->group(function () {
//         Route::get('/', [AdminUserPermissionController::class, 'index'])->name('index');
//         // 기타 create, store, edit, update, destroy 등 필요시 추가
//     });

//     // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
//     // ... 필요시 chartData, monthlyStats, statusStats, hourlyStats 등도 추가 ...

//     // 데이터베이스 관리 - 마이그레이션 목록/상세
//     Route::prefix('/database/migrations')->name('database.migrations.')->group(function () {
//         Route::get('/', [DatabaseMigrationController::class, 'index'])->name('index');
//         Route::get('/{id}', [DatabaseMigrationController::class, 'show'])->name('show')->where('id', '[0-9]+');
//     });

//     // 데이터베이스 관리 - 마이그레이션 액션
//     Route::prefix('/database/migrations/actions')->name('database.migrations.actions.')->group(function () {
//         Route::post('/run', [DatabaseMigrationActionController::class, 'run'])->name('run');
//         Route::post('/rollback', [DatabaseMigrationActionController::class, 'rollback'])->name('rollback');
//         Route::post('/refresh', [DatabaseMigrationActionController::class, 'refresh'])->name('refresh');
//         Route::post('/reset', [DatabaseMigrationActionController::class, 'reset'])->name('reset');
//         Route::post('/run-specific/{migration}', [DatabaseMigrationActionController::class, 'runSpecific'])->name('run-specific');
//     });

//     // 데이터베이스 관리 - 마이그레이션 상태
//     Route::prefix('/database/migrations/status')->name('database.migrations.status.')->group(function () {
//         Route::get('/', [DatabaseMigrationStatusController::class, 'index'])->name('index');
//         Route::get('/status', [DatabaseMigrationStatusController::class, 'status'])->name('status');
//         Route::get('/api', [DatabaseMigrationStatusController::class, 'statusApi'])->name('api');
//         Route::get('/batches', [DatabaseMigrationStatusController::class, 'batches'])->name('batches');
//         Route::get('/batches/{batch}', [DatabaseMigrationStatusController::class, 'batchMigrations'])->name('batch-migrations');
//     });

// });

