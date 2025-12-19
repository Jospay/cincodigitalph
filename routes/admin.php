<?php

use App\Http\Controllers\Admin\AllocationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EarningController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin Dashboard Home
Route::get('/admin', [DashboardController::class, 'index'])->name('admin.index');

// Admin Users Management (The Table)
Route::get('/admin/users', [UsersController::class, 'index'])->name('users');

Route::get('/admin/allocation', function () {
    return Inertia::render('dashboard/Allocation');
});

Route::get('/admin/allocation', [AllocationController::class, 'index'])->name('admin.index');
Route::post('/admin/allocation', [AllocationController::class, 'store'])->name('admin.allocation.store');
Route::put('/admin/allocation/{allocation}', [AllocationController::class, 'update'])->name('admin.allocation.update');
Route::delete('/admin/allocation/{allocation}', [AllocationController::class, 'destroy'])->name('admin.allocation.destroy');

Route::get('/admin/earning', [EarningController::class, 'index'])->name('admin.earning');
