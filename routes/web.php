<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\Admin\AdminShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/events', [ShiftController::class, 'events'])->name('shifts.events');
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create')->middleware('checkAdmin');
});

Route::middleware(['auth', 'checkAdmin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

Route::middleware(['auth', 'checkAdmin'])->group(function () {
    Route::get('/admin/shifts', [ShiftController::class, 'adminIndex'])->name('admin.shifts.index');
    Route::post('/admin/shifts', [ShiftController::class, 'adminStore'])->name('admin.shifts.store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/shifts', [AdminShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [AdminShiftController::class, 'store'])->name('shifts.store');
});


require __DIR__.'/auth.php';
