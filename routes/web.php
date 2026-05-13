<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardRedirectController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/admin/dashboard', DashboardController::class)->middleware(['auth', 'verified', 'role:admin'])->name('admin.dashboard');
Route::get('/staff/dashboard', StaffDashboardController::class)->middleware(['auth', 'verified', 'role:staff'])->name('staff.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
