<?php
use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\AdminSessionController;


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


