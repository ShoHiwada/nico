<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminShiftController;
use App\Http\Controllers\Staff\ShiftRequestController;
use Illuminate\Support\Facades\Route;

// トップページ
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

// 一般ユーザー：職員用ページ
Route::middleware(['auth'])->group(function () {
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/events', [ShiftController::class, 'events'])->name('shifts.events');
    Route::get('/shift-request', fn() => view('staff.shift-request'))->name('staff.shift-request');
    Route::post('/shift-request', [\App\Http\Controllers\Staff\ShiftRequestController::class, 'store'])->name('staff.shift-request.store'); // ← 追加
    Route::get('/attendance', fn() => view('staff.attendance'))->name('staff.attendance');
    Route::get('/work-history', fn() => view('staff.work-history'))->name('staff.work-history');
    Route::get('/manual', fn() => view('common.manual'))->name('common.manual');
    Route::get('/notifications', fn() => view('common.notifications'))->name('common.notifications');
});

// 管理者ページ
Route::middleware(['auth', 'checkAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/shifts', [AdminShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [AdminShiftController::class, 'store'])->name('shifts.store');

    Route::post('/shifts/apply-requests', [AdminShiftController::class, 'applyRequests'])->name('shifts.apply-requests');

    Route::get('/shifts/settings', [ShiftSettingController::class, 'index'])->name('shifts.settings');
    Route::post('/shifts/settings/deadlines', [ShiftSettingController::class, 'storeDeadline'])->name('shifts.settings.deadlines.store');
    Route::delete('/shifts/settings/deadlines/{id}', [ShiftSettingController::class, 'deleteDeadline'])->name('shifts.settings.deadlines.delete');


    Route::get('/shift-requests', fn() => view('admin.shift-requests'))->name('shift-requests');
    Route::get('/attendance', fn() => view('admin.attendance'))->name('attendance');
    Route::get('/reports', fn() => view('admin.reports'))->name('reports');
    Route::get('/activity-log', fn() => view('admin.activity-log'))->name('activity-log');
    Route::get('/roles', fn() => view('admin.roles'))->name('roles');
    Route::get('/settings', fn() => view('admin.settings'))->name('settings');
    Route::get('/notifications', fn() => view('common.notifications'))->name('notifications');
});

// シフト希望申請
Route::middleware(['auth'])->group(function () {
    Route::get('/shift-request', [ShiftRequestController::class, 'create'])->name('staff.shift-request');
    Route::post('/shift-request', [ShiftRequestController::class, 'store'])->name('staff.shift-request.store');
    Route::get('/shift-request/events', [ShiftRequestController::class, 'events'])->name('staff.shift-request.events');
});

require __DIR__ . '/auth.php';
