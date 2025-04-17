<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\Admin\AdminShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 一般ユーザー向けダッシュボード
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// 一般ユーザー：プロフィール操作
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 一般ユーザー：シフト閲覧・申請
Route::middleware(['auth'])->group(function () {
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/events', [ShiftController::class, 'events'])->name('shifts.events');
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create')->middleware('checkAdmin');
});

// 管理者ページ全体
Route::middleware(['auth', 'checkAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');
    Route::get('/shifts', [AdminShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [AdminShiftController::class, 'store'])->name('shifts.store');
});

require __DIR__.'/auth.php';
