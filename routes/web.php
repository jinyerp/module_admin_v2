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
use Jiny\Admin\App\Http\Controllers\Auth\AdminAuthSessionController;
use Jiny\Admin\App\Http\Controllers\Auth\AdminLoginFormController;
Route::prefix($adminPrefix)->middleware(['web'])->name('admin.')
    ->group(function () {
    // 로그인 폼
    Route::get('/login', [
        AdminLoginFormController::class,
        'showLoginForm'])->name('login');

    // 로그인 처리
    Route::post('/login', [
        AdminAuthSessionController::class,
        'login'])->name('login.store');

    Route::post('/login/ajax', [
        AdminAuthSessionController::class,
        'loginAjax'])->name('login.ajax');
});

// 관리자 인증 라우트 그룹 (web 미들웨어 적용)
use Jiny\Admin\App\Http\Controllers\Auth\AdminLogoutSessionController;
Route::prefix($adminPrefix)->middleware(['web'])->name('admin.')
    ->group(function () {
    // 로그아웃 처리
    Route::get('/logout', [
        AdminLogoutSessionController::class,
        'logout'])->name('logout');
    
    Route::post('/logout', [
        AdminLogoutSessionController::class,
        'logout'])->name('logout.post');
    
    Route::post('/logout/ajax', [
        AdminLogoutSessionController::class,
        'logoutAjax'])->name('logout.ajax');
});

// 관리자 세션 관리 라우트 (인증 필요)
Route::prefix($adminPrefix)->middleware(['web', 'admin:auth'])->name('admin.')
    ->group(function () {
    // 세션 정보 조회
    Route::get('/session/info', [
        AdminLogoutSessionController::class,
        'getCurrentSessionInfo'])->name('session.info');
    
    // 활성 세션 목록 조회 (super 관리자만)
    Route::get('/session/active', [
        AdminLogoutSessionController::class,
        'getActiveSessions'])->name('session.active');
    
    // 모든 세션 강제 종료 (super 관리자만)
    Route::post('/session/force-logout-all', [
        AdminLogoutSessionController::class,
        'forceLogoutAllSessions'])->name('session.force-logout-all');
    
    // 특정 관리자 세션 강제 종료 (super 관리자만)
    Route::post('/session/force-logout-user/{adminUserId}', [
        AdminLogoutSessionController::class,
        'forceLogoutUserSessions'])->name('session.force-logout-user');
});

/**
 * 3. 2FA 인증
 * 2FA 인증 라우트 (로그인 후, 2FA 검증이 필요한 페이지)
 */
use Jiny\Admin\App\Http\Controllers\Auth\AdminTwoFactorController;
Route::prefix($adminPrefix)->middleware(['web', 'admin:auth'])->name('admin.')
    ->group(function () {
    // 2FA 인증 페이지
    Route::get('/2fa/challenge', [
        AdminTwoFactorController::class,
        'challenge'])->name('2fa.challenge');
    // 2FA 인증 처리
    Route::post('/2fa/verify', [
        AdminTwoFactorController::class,
        'verify'])->name('2fa.verify');
    // 2FA 도움말 페이지
    Route::get('/2fa/help', [
        AdminTwoFactorController::class,
        'help'])->name('2fa.help');
});



