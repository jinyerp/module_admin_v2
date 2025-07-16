<?php
use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\Auth\AdminSessionLogin;
use Jiny\Admin\Http\Controllers\AdminDashboard;

use Jiny\Admin\Http\Controllers\Logs\AdminUserLogController;
use Jiny\Admin\Http\Controllers\Logs\AdminActivityLogController;
use Jiny\Admin\Http\Controllers\Logs\AdminAuditLogController;

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

// 관리자 대시보드 (인증 필요)
Route::prefix('admin')->middleware(['web', 'admin:auth'])->name('admin.')->group(function () {
    Route::get('/dashboard', [
        AdminDashboard::class,
        'index'])->name('dashboard');
});


/**
 * admin/admin 관리기능능
 */
Route::prefix('admin/admin')->middleware(['web', 'admin:auth'])
    ->name('admin.admin.')->group(function () {

    // 관리자 사용자 로그 CRUD 라우트
    Route::prefix('logs/user')->name('logs.user.')->group(function () {
        Route::get('/', [AdminUserLogController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserLogController::class, 'create'])->name('create');
        Route::post('/', [AdminUserLogController::class, 'store'])->name('store');
        Route::get('/stats', [AdminUserLogController::class, 'stats'])->name('stats');
        Route::get('/admin/{adminUserId}/stats', [AdminUserLogController::class, 'adminStats'])->name('admin-stats');
        Route::post('/export', [AdminUserLogController::class, 'export'])->name('export');
        Route::post('/bulk-delete', [AdminUserLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/cleanup', [AdminUserLogController::class, 'cleanup'])->name('cleanup');

        // UUID 기반 라우트
        Route::get('/{userLog}', [AdminUserLogController::class, 'show'])->name('show')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::get('/{userLog}/edit', [AdminUserLogController::class, 'edit'])->name('edit')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::put('/{userLog}', [AdminUserLogController::class, 'update'])->name('update')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::delete('/{userLog}', [AdminUserLogController::class, 'destroy'])->name('destroy')->where('userLog', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    });


    // 로그 관리 - 활동 로그
    Route::prefix('logs/activity')->name('logs.activity.')->group(function () {
        Route::get('/', [AdminActivityLogController::class, 'index'])->name('index');
        Route::get('/create', [AdminActivityLogController::class, 'create'])->name('create');
        Route::post('/', [AdminActivityLogController::class, 'store'])->name('store');
        Route::get('/stats', [AdminActivityLogController::class, 'stats'])->name('stats');
        Route::get('/admin/{adminId}/stats', [AdminActivityLogController::class, 'adminStats'])->name('admin-stats');
        Route::post('/export', [AdminActivityLogController::class, 'export'])->name('export');
        Route::post('/bulk-delete', [AdminActivityLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/cleanup', [AdminActivityLogController::class, 'cleanup'])->name('cleanup');
        Route::get('/{activityLog}', [AdminActivityLogController::class, 'show'])->name('show');
        Route::get('/{activityLog}/edit', [AdminActivityLogController::class, 'edit'])->name('edit');
        Route::put('/{activityLog}', [AdminActivityLogController::class, 'update'])->name('update');
        Route::delete('/{activityLog}', [AdminActivityLogController::class, 'destroy'])->name('destroy');

    });


    // 로그 관리 - 감사 로그
    Route::prefix('logs/audit')->name('logs.audit.')->group(function () {
        Route::get('/', [AdminAuditLogController::class, 'index'])->name('index');
        Route::get('/create', [AdminAuditLogController::class, 'create'])->name('create');
        Route::post('/', [AdminAuditLogController::class, 'store'])->name('store');
        Route::get('/stats', [AdminAuditLogController::class, 'stats'])->name('stats');
        Route::get('/admin/{adminId}/stats', [AdminAuditLogController::class, 'adminStats'])->name('admin-stats');
        Route::post('/export', [AdminAuditLogController::class, 'export'])->name('export');
        Route::post('/bulk-delete', [AdminAuditLogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/cleanup', [AdminAuditLogController::class, 'cleanup'])->name('cleanup');
        Route::get('/{auditLog}', [AdminAuditLogController::class, 'show'])->name('show');
        Route::get('/{auditLog}/edit', [AdminAuditLogController::class, 'edit'])->name('edit');
        Route::put('/{auditLog}', [AdminAuditLogController::class, 'update'])->name('update');
        Route::delete('/{auditLog}', [AdminAuditLogController::class, 'destroy'])->name('destroy');
    });

    // 관리자 회원 목록록
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
            'destroy'])->name('destroy');//->where('country', '[0-9]+'); // 삭제
        Route::post('/bulk-delete', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'bulkDelete'])->name('bulk-delete'); // 선택 삭제
        // CSV 다운로드 라우트 추가
        Route::get('/download-csv', [
            \Jiny\Admin\Http\Controllers\AdminUserController::class,
            'downloadCsv'])->name('downloadCsv');
    });





});
