<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InvoiceController;

// Ruta principal - formulario de tracking público
Route::get('/', [TrackingController::class, 'show'])->name('tracking.show.es');

// Ruta para realizar el tracking
Route::post('/track', [TrackingController::class, 'track'])->name('tracking.track');
Route::get('/track/{trackingNumber}', [TrackingController::class, 'track'])->name('tracking.show.result');

// Rutas de autenticación
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/track', [DashboardController::class, 'track'])->name('dashboard.track');
    Route::get('/shipments/{id}', [DashboardController::class, 'show'])->name('dashboard.shipments.show');
    Route::get('/pending-tracking', [DashboardController::class, 'pending'])->name('pending.tracking');
    Route::delete('/pending-tracking/{pendingTracking}', [DashboardController::class, 'deletePending'])->name('pending-tracking.delete');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
});

// Rutas de admin (requieren autenticación y rol admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/clients', [AdminController::class, 'clients'])->name('clients');
    Route::get('/client/create', [AdminController::class, 'createClient'])->name('client.create');
    Route::post('/client/create', [AdminController::class, 'storeClient'])->name('client.store');
    Route::get('/client/{id}', [AdminController::class, 'clientDetails'])->name('client');
    Route::get('/client/{id}/edit', [AdminController::class, 'editClient'])->name('client.edit');
    Route::put('/client/{id}', [AdminController::class, 'updateClient'])->name('client.update');
    Route::put('/client/{id}/password', [AdminController::class, 'updateClientPassword'])->name('client.password.update');
    Route::get('/client/{id}/assign', [AdminController::class, 'assignPackage'])->name('client.assign');
    Route::post('/client/{id}/assign', [AdminController::class, 'assignPackagePost'])->name('client.assign.post');
    Route::post('/client/{id}/reset-password', [AdminController::class, 'resetClientPassword'])->name('client.reset-password');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/inventory/report/received-ch', [InventoryController::class, 'downloadReceivedCHReport'])->name('inventory.report.received-ch');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
});
