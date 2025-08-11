<?php

use Illuminate\Support\Facades\Route;

$adminPrefix = config('admin.settings.prefix', 'admin');

// =============================================================================
// 컨트롤러 Import
// =============================================================================

// 인증 관련 컨트롤러
use Jiny\Admin\App\Http\Controllers\Auth\AdminSetupController;
use Jiny\Admin\App\Http\Controllers\Auth\AdminLoginFormController;
use Jiny\Admin\App\Http\Controllers\Auth\AdminAuthSessionController;
use Jiny\Admin\App\Http\Controllers\Auth\AdminLogoutSessionController;
use Jiny\Admin\App\Http\Controllers\Auth\AdminTwoFactorController;

// =============================================================================
// 라우트 정의 (우선순위 순서)
// =============================================================================

/**
 * 1. 관리자 최초 설정 (최우선)
 * 인증 없이 접근 가능 - 시스템 초기화
 */
Route::prefix($adminPrefix)
    ->middleware(['web'])
    ->name('admin.')
    ->group(function () {
        Route::get('/setup', [AdminSetupController::class, 'index'])->name('setup');
        Route::post('/setup/migrate', [AdminSetupController::class, 'migrate'])->name('setup.migrate');
        Route::post('/setup/superadmin', [AdminSetupController::class, 'createSuperAdmin'])->name('setup.superadmin');
    });

/**
 * 2. 관리자 로그인 (게스트 전용)
 * admin.guest 미들웨어 적용 - 로그인하지 않은 사용자만 접근 가능
 */

Route::prefix($adminPrefix)
    ->middleware(['web', 'admin.guest'])
    ->name('admin.')
    ->group(function () {
        // 로그인 폼
        Route::get('/login', [AdminLoginFormController::class, 'showLoginForm'])->name('login');
        
        // 로그인 처리
        Route::post('/login', [AdminAuthSessionController::class, 'login'])->name('login.store');
        Route::post('/login/ajax', [AdminAuthSessionController::class, 'loginAjax'])->name('login.ajax');
    });

/**
 * 3. 관리자 로그아웃 (인증 필요)
 * admin.auth 미들웨어 적용 - 로그인한 사용자만 접근 가능
 */
Route::prefix($adminPrefix)
    ->middleware(['web', 'admin.auth'])
    ->name('admin.')
    ->group(function () {
        // 로그아웃 처리
        Route::get('/logout', [AdminLogoutSessionController::class, 'logout'])->name('logout');
        Route::post('/logout', [AdminLogoutSessionController::class, 'logout'])->name('logout.post');
        Route::post('/logout/ajax', [AdminLogoutSessionController::class, 'logoutAjax'])->name('logout.ajax');
        
        // 세션 관리
        Route::prefix('session')->name('session.')->group(function () {
            // 세션 정보 조회
            Route::get('/info', [AdminLogoutSessionController::class, 'getCurrentSessionInfo'])->name('info');
            
            // 활성 세션 목록 조회 (super 관리자만)
            Route::get('/active', [AdminLogoutSessionController::class, 'getActiveSessions'])->name('active');
            
            // 모든 세션 강제 종료 (super 관리자만)
            Route::post('/force-logout-all', [AdminLogoutSessionController::class, 'forceLogoutAllSessions'])->name('force-logout-all');
            
            // 특정 관리자 세션 강제 종료 (super 관리자만)
            Route::post('/force-logout-user/{adminUserId}', [AdminLogoutSessionController::class, 'forceLogoutUserSessions'])->name('force-logout-user');
        });
        
        // 2FA 인증
        Route::prefix('2fa')->name('2fa.')->group(function () {
            // 2FA 인증 페이지
            Route::get('/challenge', [AdminTwoFactorController::class, 'challenge'])->name('challenge');
            
            // 2FA 인증 처리
            Route::post('/verify', [AdminTwoFactorController::class, 'verify'])->name('verify');
            
            // 2FA 도움말 페이지
            Route::get('/help', [AdminTwoFactorController::class, 'help'])->name('help');
        });
    });



