<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\StaffProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardRedirectController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/admin/dashboard', DashboardController::class)->middleware(['auth', 'verified', 'role:admin'])->name('admin.dashboard');
Route::get('/staff/dashboard', StaffDashboardController::class)->middleware(['auth', 'verified', 'role:staff'])->name('staff.dashboard');
Route::get('/staff/products', [StaffProductController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:staff'])
    ->name('staff.products.index');
Route::resource('/admin/products', ProductController::class)
    ->except(['show'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->names('admin.products');
Route::resource('/admin/users', AdminUserController::class)
    ->except(['show'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->names('admin.users');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
