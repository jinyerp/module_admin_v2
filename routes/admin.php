<?php

use Illuminate\Support\Facades\Route;

$adminPrefix = config('admin.settings.prefix', 'admin');

// =============================================================================
// 컨트롤러 Import
// =============================================================================

// 대시보드
use Jiny\Admin\App\Http\Controllers\AdminDashboard;

// 데이터베이스 관리
use Jiny\Admin\Http\Controllers\DatabaseController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationActionController;
use Jiny\Admin\Http\Controllers\DatabaseMigrationStatusController;
use Jiny\Admin\Http\Controllers\MigrationListController;

// 기본 관리
use Jiny\Admin\App\Http\Controllers\Admin\AdminCountryController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminLanguageController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminSettingMailController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminSystemController;

// 관리자 관리
use Jiny\Admin\App\Http\Controllers\Admin\AdminUserController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminSessionController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminUser2FAController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminLevelController;

// 로그 관리
use Jiny\Admin\App\Http\Controllers\Admin\AdminUserLogController;
use Jiny\Admin\App\Http\Controllers\Admin\Admin2FALogController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminActivityLogController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminAuditLogController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminPermissionLogController;

// 시스템 관리
use Jiny\Admin\App\Http\Controllers\Admin\AdminSystemBackupLogController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminSystemMaintenanceLogController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminSystemOperationLogController;
use Jiny\Admin\App\Http\Controllers\Admin\AdminSystemPerformanceLogController;

// 메뉴 관리
use Jiny\Admin\App\Http\Controllers\Admin\AdminSideMenuController;

// =============================================================================
// 메인 관리자 라우트 그룹 (인증 필요)
// =============================================================================

Route::prefix($adminPrefix)
    ->middleware(['web', 'admin.auth'])
    ->name('admin.')
    ->group(function () {
        
        // 대시보드
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');
        
        // =============================================================================
        // 데이터베이스 관리
        // =============================================================================
        Route::prefix('database')->name('database.')->group(function () {
            Route::get('/', [DatabaseController::class, 'index'])->name('index');
            
            // 마이그레이션 관리
            Route::prefix('migrations')->name('migrations.')->group(function () {
                Route::get('/', [MigrationListController::class, 'index'])->name('index');
                Route::get('/{id}', [MigrationListController::class, 'show'])
                    ->where('id', '[0-9]+')->name('show');
                
                // 마이그레이션 액션
                Route::post('/run', [DatabaseMigrationActionController::class, 'run'])->name('run');
                Route::post('/rollback', [DatabaseMigrationActionController::class, 'rollback'])->name('rollback');
                Route::post('/refresh', [DatabaseMigrationActionController::class, 'refresh'])->name('refresh');
                Route::post('/reset', [DatabaseMigrationActionController::class, 'reset'])->name('reset');
                Route::post('/run-specific/{migration}', [DatabaseMigrationActionController::class, 'runSpecific'])->name('run-specific');
                
                // 마이그레이션 상태 확인 (AJAX용)
                Route::get('/status/check', [DatabaseMigrationActionController::class, 'status'])->name('status-check');
                Route::get('/status', [DatabaseMigrationStatusController::class, 'status'])->name('status');
                Route::get('/status/api', [DatabaseMigrationStatusController::class, 'statusApi'])->name('status-api');
                Route::get('/batches', [DatabaseMigrationStatusController::class, 'batches'])->name('batches');
                Route::get('/batches/{batch}/migrations', [DatabaseMigrationStatusController::class, 'batchMigrations'])->name('batch-migrations');
            });
        });
        
        // =============================================================================
        // 기본 관리
        // =============================================================================
        
        // 국가 관리
        Route::prefix('country')->name('country.')->group(function () {
            Route::get('/', [AdminCountryController::class, 'index'])->name('index');
            Route::get('/create', [AdminCountryController::class, 'create'])->name('create');
            Route::post('/', [AdminCountryController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminCountryController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminCountryController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminCountryController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminCountryController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/confirm', [AdminCountryController::class, 'deleteConfirm'])->name('confirm')->where('id', '[0-9]+');
            Route::post('/bulk-delete', [AdminCountryController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/{id}/toggle-enable', [AdminCountryController::class, 'toggleEnableAjax'])->name('toggle-enable')->where('id', '[0-9]+');
            Route::post('/enable-all', [AdminCountryController::class, 'enableAllAjax'])->name('enable-all');
        });

        // 언어 관리
        Route::prefix('language')->name('language.')->group(function () {
            Route::get('/', [AdminLanguageController::class, 'index'])->name('index');
            Route::get('/create', [AdminLanguageController::class, 'create'])->name('create');
            Route::post('/', [AdminLanguageController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminLanguageController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminLanguageController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminLanguageController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminLanguageController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/confirm', [AdminLanguageController::class, 'deleteConfirm'])->name('confirm')->where('id', '[0-9]+');
            Route::post('/bulk-delete', [AdminLanguageController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/{id}/toggle-enable', [AdminLanguageController::class, 'toggleEnableAjax'])->name('toggle-enable')->where('id', '[0-9]+');
            Route::post('/enable-all', [AdminLanguageController::class, 'enableAllAjax'])->name('enable-all');
            
            // 기본 언어 설정 및 로케일 동기화
            Route::post('/set-default', [AdminLanguageController::class, 'setDefault'])->name('set-default');
            Route::post('/sync-locale', [AdminLanguageController::class, 'syncLocale'])->name('sync-locale');
        });
        
        // 메일 환경설정
        Route::prefix('setting')->name('setting.')->group(function () {
            Route::get('/mail', [AdminSettingMailController::class, 'index'])->name('mail');
            Route::put('/mail', [AdminSettingMailController::class, 'update'])->name('mail.update');
            Route::post('/mail/test', [AdminSettingMailController::class, 'test'])->name('mail.test');
        });
        
        // 시스템 정보
        Route::prefix('systems')->name('systems.')->group(function () {
            Route::get('/', [AdminSystemController::class, 'index'])->name('index');
            Route::get('/status', [AdminSystemController::class, 'status'])->name('status');
            Route::get('/php', [AdminSystemController::class, 'phpDetail'])->name('php');
            Route::get('/laravel', [AdminSystemController::class, 'laravelDetail'])->name('laravel');
            Route::get('/database', [AdminSystemController::class, 'databaseDetail'])->name('database');
            Route::get('/session', [AdminSystemController::class, 'sessionDetail'])->name('session');
        });
        
        // =============================================================================
        // 관리자 관리
        // =============================================================================
        
        // 관리자 사용자 관리
        Route::prefix('admin/users')->name('admin.users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminUserController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminUserController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/confirm', [AdminUserController::class, 'deleteConfirm'])->name('confirm')->where('id', '[0-9]+');
            Route::post('/bulk-delete', [AdminUserController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/download-csv', [AdminUserController::class, 'downloadCsv'])->name('downloadCsv');
            
            // 관리자별 2FA 설정
            Route::prefix('{id}/2fa')->name('2fa.')->group(function () {
                Route::get('/setup', [AdminUser2FAController::class, 'setup'])->name('setup')->where('id', '[0-9]+');
                Route::post('/enable', [AdminUser2FAController::class, 'enable'])->name('enable')->where('id', '[0-9]+');
                Route::get('/manage', [AdminUser2FAController::class, 'manage'])->name('manage')->where('id', '[0-9]+');
                Route::post('/disable', [AdminUser2FAController::class, 'disable'])->name('disable')->where('id', '[0-9]+');
                Route::post('/regenerate-backup-codes', [AdminUser2FAController::class, 'regenerateBackupCodes'])->name('regenerate-backup-codes')->where('id', '[0-9]+');
            });
        });

        // 관리자 등급 관리
        Route::prefix('admin/levels')->name('admin.levels.')->group(function () {
            Route::get('/', [AdminLevelController::class, 'index'])->name('index');
            Route::get('/create', [AdminLevelController::class, 'create'])->name('create');
            Route::post('/', [AdminLevelController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminLevelController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminLevelController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminLevelController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminLevelController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/confirm', [AdminLevelController::class, 'deleteConfirm'])->name('confirm')->where('id', '[0-9]+');
            Route::post('/bulk-delete', [AdminLevelController::class, 'bulkDelete'])->name('bulk-delete');
        });

        // 관리자 세션 관리
        Route::prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/', [AdminSessionController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminSessionController::class, 'show'])->name('show')->where('id', '[0-9a-zA-Z]+');
            Route::get('/{id}/confirm', [AdminSessionController::class, 'confirm'])->name('confirm')->where('id', '[0-9a-zA-Z]+');
            Route::delete('/{id}', [AdminSessionController::class, 'destroy'])->name('destroy')->where('id', '[0-9a-zA-Z]+');
            Route::post('/{id}/refresh', [AdminSessionController::class, 'refresh'])->name('refresh')->where('id', '[0-9a-zA-Z]+');
            Route::post('/bulk-delete', [AdminSessionController::class, 'bulkDelete'])->name('bulk-delete');
        });
        
        // =============================================================================
        // 로그 관리
        // =============================================================================
        
        // 관리자 사용자 로그
        Route::prefix('admin/user-logs')->name('admin.user-logs.')->group(function () {
            Route::get('/', [AdminUserLogController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserLogController::class, 'create'])->name('create');
            Route::post('/', [AdminUserLogController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminUserLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminUserLogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminUserLogController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminUserLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/confirm', [AdminUserLogController::class, 'deleteConfirm'])->name('confirm')->where('id', '[0-9]+');
            Route::post('/bulk-delete', [AdminUserLogController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/download-csv', [AdminUserLogController::class, 'downloadCsv'])->name('downloadCsv');
            Route::post('/export', [AdminUserLogController::class, 'export'])->name('export');
            Route::post('/cleanup', [AdminUserLogController::class, 'cleanup'])->name('cleanup');
            Route::get('/stats', [AdminUserLogController::class, 'stats'])->name('stats');
            Route::get('/admin/{adminUserId}/stats', [AdminUserLogController::class, 'adminStats'])->name('admin-stats');
        });

        // 2FA 로그 관리
        Route::prefix('admin/user-2fa-logs')->name('admin.user-2fa-logs.')->group(function () {
            Route::get('/', [Admin2FALogController::class, 'index'])->name('index');
            Route::get('/create', [Admin2FALogController::class, 'create'])->name('create');
            Route::post('/', [Admin2FALogController::class, 'store'])->name('store');
            Route::get('/{id}', [Admin2FALogController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [Admin2FALogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [Admin2FALogController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [Admin2FALogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/confirm', [Admin2FALogController::class, 'deleteConfirm'])->name('confirm')->where('id', '[0-9]+');
            Route::get('/stats', [Admin2FALogController::class, 'stats'])->name('stats');
            Route::post('/export', [Admin2FALogController::class, 'export'])->name('export');
            Route::get('/download-csv', [Admin2FALogController::class, 'downloadCsv'])->name('downloadCsv');
            Route::post('/bulk-delete', [Admin2FALogController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/cleanup', [Admin2FALogController::class, 'cleanup'])->name('cleanup');
        });

        // 활동 로그 관리
        Route::prefix('admin/activity-log')->name('admin.activity-log.')->group(function () {
            Route::get('/', [AdminActivityLogController::class, 'index'])->name('index');
            Route::get('/create', [AdminActivityLogController::class, 'create'])->name('create');
            Route::post('/', [AdminActivityLogController::class, 'store'])->name('store');
            Route::get('/stats', [AdminActivityLogController::class, 'stats'])->name('stats');
            Route::get('/admin/{adminId}/stats', [AdminActivityLogController::class, 'adminStats'])->name('admin-stats');
            Route::post('/export', [AdminActivityLogController::class, 'export'])->name('export');
            Route::post('/bulk-delete', [AdminActivityLogController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/cleanup', [AdminActivityLogController::class, 'cleanup'])->name('cleanup');
            Route::get('/{id}', [AdminActivityLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminActivityLogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminActivityLogController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminActivityLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/confirm', [AdminActivityLogController::class, 'deleteConfirm'])->name('confirm')->where('id', '[0-9]+');
            Route::get('/download-csv', [AdminActivityLogController::class, 'downloadCsv'])->name('downloadCsv');
        });

        // 감사 로그 관리
        Route::prefix('admin/audit-logs')->name('admin.audit-logs.')->group(function () {
            Route::get('/', [AdminAuditLogController::class, 'index'])->name('index');
            Route::get('/create', [AdminAuditLogController::class, 'create'])->name('create');
            Route::post('/', [AdminAuditLogController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminAuditLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AdminAuditLogController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}', [AdminAuditLogController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [AdminAuditLogController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/bulk-delete', [AdminAuditLogController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/download-csv', [AdminAuditLogController::class, 'downloadCsv'])->name('downloadCsv');
        });

        // 권한 로그 관리
        Route::prefix('admin/permission-logs')->name('admin.permission-logs.')->group(function () {
            Route::get('/', [AdminPermissionLogController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminPermissionLogController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/stats', [AdminPermissionLogController::class, 'stats'])->name('stats');
            Route::get('/download-csv', [AdminPermissionLogController::class, 'downloadCsv'])->name('downloadCsv');
        });
        
        // =============================================================================
        // 시스템 관리
        // =============================================================================
        
        // 시스템 로그 관리
        Route::prefix('systems')->name('systems.')->group(function () {
            
            // 백업 로그 관리
            Route::prefix('backup-logs')->name('backup-logs.')->group(function () {
                Route::get('/', [AdminSystemBackupLogController::class, 'index'])->name('index');
                Route::get('/create', [AdminSystemBackupLogController::class, 'create'])->name('create');
                Route::post('/', [AdminSystemBackupLogController::class, 'store'])->name('store');
                Route::get('/{id}', [AdminSystemBackupLogController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [AdminSystemBackupLogController::class, 'edit'])->name('edit');
                Route::put('/{id}', [AdminSystemBackupLogController::class, 'update'])->name('update');
                Route::delete('/{id}', [AdminSystemBackupLogController::class, 'destroy'])->name('destroy');
                Route::put('/{id}/status', [AdminSystemBackupLogController::class, 'updateStatus'])->name('update-status');
                Route::get('/stats', [AdminSystemBackupLogController::class, 'stats'])->name('stats');
                Route::post('/bulk-delete', [AdminSystemBackupLogController::class, 'bulkDelete'])->name('bulk-delete');
                Route::post('/export', [AdminSystemBackupLogController::class, 'export'])->name('export');
                
                // 백업 실행 및 다운로드
                Route::get('/create-backup', [AdminSystemBackupLogController::class, 'createBackup'])->name('create-backup');
                Route::post('/execute-backup', [AdminSystemBackupLogController::class, 'executeBackup'])->name('execute-backup');
                Route::get('/{id}/download', [AdminSystemBackupLogController::class, 'downloadBackup'])->name('download');
                Route::delete('/{id}/delete-file', [AdminSystemBackupLogController::class, 'deleteBackupFile'])->name('delete-file');
            });
            
            // 유지보수 로그 관리
            Route::prefix('maintenance-logs')->name('maintenance-logs.')->group(function () {
                Route::get('/', [AdminSystemMaintenanceLogController::class, 'index'])->name('index');
                Route::get('/create', [AdminSystemMaintenanceLogController::class, 'create'])->name('create');
                Route::post('/', [AdminSystemMaintenanceLogController::class, 'store'])->name('store');
                Route::get('/{id}', [AdminSystemMaintenanceLogController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [AdminSystemMaintenanceLogController::class, 'edit'])->name('edit');
                Route::put('/{id}', [AdminSystemMaintenanceLogController::class, 'update'])->name('update');
                Route::delete('/{id}', [AdminSystemMaintenanceLogController::class, 'destroy'])->name('destroy');
                Route::patch('/{id}/status', [AdminSystemMaintenanceLogController::class, 'updateStatus'])->name('update-status');
                Route::get('/stats', [AdminSystemMaintenanceLogController::class, 'stats'])->name('stats');
                Route::post('/bulk-delete', [AdminSystemMaintenanceLogController::class, 'bulkDelete'])->name('bulk-delete');
                Route::post('/export', [AdminSystemMaintenanceLogController::class, 'export'])->name('export');
            });
            
            // 운영 로그 관리
            Route::prefix('operation-logs')->name('operation-logs.')->group(function () {
                Route::get('/', [AdminSystemOperationLogController::class, 'index'])->name('index');
                Route::get('/{id}', [AdminSystemOperationLogController::class, 'show'])->name('show');
                Route::get('/stats', [AdminSystemOperationLogController::class, 'stats'])->name('stats');
                Route::post('/bulk-delete', [AdminSystemOperationLogController::class, 'bulkDelete'])->name('bulk-delete');
                Route::post('/export', [AdminSystemOperationLogController::class, 'export'])->name('export');
                
                // API 라우트
                Route::prefix('api')->name('api.')->group(function () {
                    Route::get('/', [AdminSystemOperationLogController::class, 'apiIndex'])->name('index');
                    Route::get('/{id}', [AdminSystemOperationLogController::class, 'apiShow'])->name('show');
                    Route::get('/stats', [AdminSystemOperationLogController::class, 'apiStats'])->name('stats');
                    Route::get('/operation-type-analysis', [AdminSystemOperationLogController::class, 'operationTypeAnalysis'])->name('operation-type-analysis');
                    Route::get('/performer-analysis', [AdminSystemOperationLogController::class, 'performerAnalysis'])->name('performer-analysis');
                    Route::get('/performance-analysis', [AdminSystemOperationLogController::class, 'performanceAnalysis'])->name('performance-analysis');
                    Route::get('/time-trend', [AdminSystemOperationLogController::class, 'timeTrend'])->name('time-trend');
                    Route::get('/error-analysis', [AdminSystemOperationLogController::class, 'errorAnalysis'])->name('error-analysis');
                });
            });

            // 시스템 성능 로그 관리
            Route::prefix('performance-logs')->name('performance-logs.')->group(function () {
                Route::get('/', [AdminSystemPerformanceLogController::class, 'index'])->name('index');
                Route::get('/create', [AdminSystemPerformanceLogController::class, 'create'])->name('create');
                Route::post('/', [AdminSystemPerformanceLogController::class, 'store'])->name('store');
                Route::get('/{id}', [AdminSystemPerformanceLogController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [AdminSystemPerformanceLogController::class, 'edit'])->name('edit');
                Route::put('/{id}', [AdminSystemPerformanceLogController::class, 'update'])->name('update');
                Route::delete('/{id}', [AdminSystemPerformanceLogController::class, 'destroy'])->name('destroy');
                Route::put('/{id}/status', [AdminSystemPerformanceLogController::class, 'updateStatus'])->name('update-status');
                Route::get('/stats', [AdminSystemPerformanceLogController::class, 'stats'])->name('stats');
                Route::post('/bulk-delete', [AdminSystemPerformanceLogController::class, 'bulkDelete'])->name('bulk-delete');
                Route::post('/export', [AdminSystemPerformanceLogController::class, 'export'])->name('export');
                Route::get('/realtime', [AdminSystemPerformanceLogController::class, 'realtime'])->name('realtime');
            });
        });
        
        // =============================================================================
        // 메뉴 관리
        // =============================================================================
        
        // 시스템 메뉴 관리
        Route::prefix('system/menu')->name('system.menu.')->group(function () {
            Route::get('/', [AdminSideMenuController::class, 'index'])->name('index');
            Route::get('/edit', [AdminSideMenuController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminSideMenuController::class, 'update'])->name('update');
            Route::get('/data', [AdminSideMenuController::class, 'getMenuData'])->name('data');
            Route::post('/reload', [AdminSideMenuController::class, 'reload'])->name('reload');
        });
    });
