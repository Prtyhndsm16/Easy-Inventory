<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CashieringController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\StaffProductController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function (string $path) {
    $path = ltrim(str_replace('\\', '/', $path), '/');

    abort_if($path === '' || str_contains($path, '..'), 404);

    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    return $disk->response($path);
})->where('path', '.*')->name('storage.public');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardRedirectController::class)->middleware(['auth', 'account.active', 'verified'])->name('dashboard');
Route::get('/admin/dashboard', DashboardController::class)->middleware(['auth', 'account.active', 'verified', 'role:admin'])->name('admin.dashboard');
Route::get('/staff/dashboard', StaffDashboardController::class)->middleware(['auth', 'account.active', 'verified', 'role:staff'])->name('staff.dashboard');
Route::get('/staff/products', [StaffProductController::class, 'index'])
    ->middleware(['auth', 'account.active', 'verified', 'role:staff'])
    ->name('staff.products.index');
Route::middleware(['auth', 'account.active', 'verified', 'role:admin,staff'])
    ->prefix('cashiering')
    ->name('cashiering.')
    ->group(function (): void {
        Route::get('/', [CashieringController::class, 'index'])->name('index');
        Route::get('/products', [CashieringController::class, 'products'])->name('products');
        Route::get('/lookup', [CashieringController::class, 'lookup'])->name('lookup');
        Route::post('/checkout', [CashieringController::class, 'checkout'])->name('checkout');
        Route::get('/receipts/{sale}', [CashieringController::class, 'receipt'])->name('receipts.show');
    });
Route::get('/admin/products/deleted', [ProductController::class, 'deleted'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.products.deleted');
Route::patch('/admin/products/deleted/{deletedProduct}/restore', [ProductController::class, 'restore'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.products.restore');
Route::resource('/admin/products', ProductController::class)
    ->except(['show'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->names('admin.products');
Route::get('/admin/products/{product}', [ProductController::class, 'show'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.products.show');
Route::patch('/admin/users/{user}/lock', [AdminUserController::class, 'lock'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.users.lock');
Route::patch('/admin/users/{user}/unlock', [AdminUserController::class, 'unlock'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.users.unlock');
Route::patch('/admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.users.reset-password');
Route::resource('/admin/users', AdminUserController::class)
    ->except(['show'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->names('admin.users');
Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.audit-logs.index');

Route::get('/admin/stock-out', [StockOutController::class, 'index'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin,staff'])
    ->name('admin.stock-out.index');
Route::post('/admin/stock-out', [StockOutController::class, 'store'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin,staff'])
    ->name('admin.stock-out.store');
Route::delete('/admin/stock-out/{stockOut}', [StockOutController::class, 'destroy'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin,staff'])
    ->name('admin.stock-out.destroy');

Route::get('/admin/stock-in', [StockInController::class, 'index'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin,staff'])
    ->name('admin.stock-in.index');
Route::post('/admin/stock-in', [StockInController::class, 'store'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin,staff'])
    ->name('admin.stock-in.store');
Route::delete('/admin/stock-in/{stockIn}', [StockInController::class, 'destroy'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin,staff'])
    ->name('admin.stock-in.destroy');

Route::get('/admin/sales', [SalesController::class, 'index'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.sales.index');

Route::get('/admin/reports', [ReportController::class, 'index'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.reports.index');
Route::get('/admin/reports/download', [ReportController::class, 'download'])
    ->middleware(['auth', 'account.active', 'verified', 'role:admin'])
    ->name('admin.reports.download');

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
