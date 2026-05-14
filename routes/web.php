<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ActivityLogController;

// ─── Authenticated admin routes ───────────────────────────────────────────────
Route::middleware(['auth'])->prefix('admin')->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('',          [DashboardController::class, 'index']);

    // Profile
    Route::get('profile',                        [UserController::class, 'profile'])->name('profile');
    Route::post('profile/{user}',                [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}', [UserController::class, 'updatePassword'])->name('update-password');

    // Notifications
    Route::get('notification',      [NotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::get('notification-read', [NotificationController::class, 'read'])->name('read');

    Route::post('logout', [LogoutController::class, 'index'])->name('logout');

    // Users / RBAC
    Route::resource('users',       UserController::class);
    Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);
    Route::put('permission',       [PermissionController::class, 'update'])->name('permissions.update');
    Route::resource('roles',       RoleController::class);

    // Suppliers & categories
    Route::resource('suppliers',  SupplierController::class);
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
    Route::put('categories',      [CategoryController::class, 'update'])->name('categories.update');

    // Purchases — custom routes BEFORE resource so they are not swallowed by {purchase}
    Route::get('purchases/reports',  [PurchaseController::class, 'reports'])->name('purchases.report');
    Route::post('purchases/reports', [PurchaseController::class, 'generateReport']);
    Route::resource('purchases', PurchaseController::class)->except('show');

    // Products — custom routes BEFORE resource
    Route::get('products/outstock', [ProductController::class, 'outstock'])->name('outstock');
    Route::get('products/expired',  [ProductController::class, 'expired'])->name('expired');
    Route::resource('products', ProductController::class)->except('show');

    // Sales — custom routes BEFORE resource
    Route::get('sales/reports',  [SaleController::class, 'reports'])->name('sales.report');
    Route::post('sales/reports', [SaleController::class, 'generateReport']);
    Route::resource('sales', SaleController::class)->except('show');

    // Backup
    Route::get('backup',                          [BackupController::class, 'index'])->name('backup.index');
    Route::put('backup/create',                   [BackupController::class, 'create'])->name('backup.store');
    Route::get('backup/download/{file_name?}',    [BackupController::class, 'download'])->name('backup.download');
    Route::delete('backup/delete/{file_name?}',   [BackupController::class, 'destroy'])
        ->where('file_name', '(.*)')
        ->name('backup.destroy');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings');

    // Activity Log
    Route::get('activity',    [ActivityLogController::class, 'index'])->name('activity.index');
    Route::delete('activity', [ActivityLogController::class, 'destroy'])->name('activity.destroy');
});

// ─── Guest routes (rate-limited) ─────────────────────────────────────────────
Route::middleware(['guest', 'throttle:auth'])->prefix('admin')->group(function () {
    Route::get('login',  [LoginController::class, 'index'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('register',  [RegisterController::class, 'index'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('forgot-password',   [ForgotPasswordController::class, 'index'])->name('password.request');
    Route::post('forgot-password',  [ForgotPasswordController::class, 'requestEmail']);
    Route::get('reset-password/{token}',  [ResetPasswordController::class, 'index'])->name('password.reset');
    Route::post('reset-password',         [ResetPasswordController::class, 'resetPassword'])->name('password.update');
});

// ─── Root redirect ────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('dashboard'));
