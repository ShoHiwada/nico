<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminShiftController;
use App\Http\Controllers\Admin\FixedShiftController;
use App\Http\Controllers\Admin\ShiftSettingController;
use App\Http\Controllers\Admin\DeadlineController;
use App\Http\Controllers\Staff\ShiftRequestController;
use App\Http\Controllers\Admin\AdminShiftRequestController;
use App\Http\Controllers\Staff\ShiftRequestNoteController;
use App\Http\Controllers\Admin\NightShiftController;
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
    Route::get('/branches', fn() => \App\Models\Branch::all());
    Route::get('/departments', fn() => \App\Models\Department::all());
    Route::get('/positions', fn() => \App\Models\Position::all());

    // Route::get('/users', [UserController::class, 'fetchUsers']);
    Route::get('/api/users', [UserController::class, 'apiIndex']);
    Route::get('/shifts/fetch', [AdminShiftController::class, 'fetchShifts']);
    Route::get('/api/shift-types', function () { return \App\Models\ShiftType::select('id', 'name', 'category', 'start_time', 'end_time')->get();});
    Route::get('/api/shift-requests', [AdminShiftRequestController::class, 'apiIndex']);
    Route::get('/api/fixed-shifts', [FixedShiftController::class, 'apiIndex']);

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/shifts', [AdminShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [AdminShiftController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/create', [AdminShiftController::class, 'create'])->name('shifts.create');

    Route::get('/shifts/night', [NightShiftController::class, 'index'])->name('shifts.night.index');
    Route::post('/shifts/night/store', [NightShiftController::class, 'store'])->name('shifts.night.store');

    Route::get('/fixed-shifts', [FixedShiftController::class, 'index'])->name('fixed-shifts.index');
    Route::post('/fixed-shifts', [FixedShiftController::class, 'store'])->name('fixed-shifts.store');
    Route::delete('/fixed-shifts/{id}', [FixedShiftController::class, 'destroy'])->name('fixed-shifts.destroy');
    Route::post('/shifts/apply-fixed', [AdminShiftController::class, 'applyFixed'])->name('shifts.apply-fixed');


    Route::post('/shifts/apply-requests', [AdminShiftController::class, 'applyRequests'])->name('shifts.apply-requests');

    Route::get('/shifts/settings', [ShiftSettingController::class, 'index'])->name('shifts.settings');
    Route::post('/shifts/settings/deadlines', [ShiftSettingController::class, 'storeDeadline'])->name('shifts.settings.deadlines.store');
    Route::delete('/shifts/settings/deadlines/{id}', [ShiftSettingController::class, 'deleteDeadline'])->name('shifts.settings.deadlines.delete');
    Route::get('/shift-requests', [AdminShiftRequestController::class, 'index'])->name('shift-requests');

    Route::delete('/shift-request/{date}', [ShiftRequestController::class, 'destroy'])->middleware('auth')->name('staff.shift-request.destroy');
    Route::post('deadlines', [DeadlineController::class, 'store'])->name('deadlines.store');

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
    Route::get('shift-request-notes/{month}/edit', [ShiftRequestNoteController::class, 'edit'])->name('shift_request_notes.edit');
    Route::post('shift-request-notes/{month}', [ShiftRequestNoteController::class, 'update'])->name('shift_request_notes.update');
});

require __DIR__ . '/auth.php';
