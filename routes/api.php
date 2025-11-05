<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScrapingController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ShipmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes (public)
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Public Tracking Routes (no authentication required)
Route::get('/track/{trackingNumber}', [ScrapingController::class, 'scrapeShipment'])->name('api.track');
Route::get('/public/track/{trackingNumber}', [ScrapingController::class, 'publicTrack'])->name('api.public.track');

// Scraping Routes
Route::prefix('scraping')->group(function () {
    Route::get('/', [ScrapingController::class, 'index']);
    Route::get('/status', [ScrapingController::class, 'status']);
    Route::post('/warehouses', [ScrapingController::class, 'scrapeWarehouses']);
    Route::post('/shipments', [ScrapingController::class, 'scrapeShipments']);
    Route::get('/shipment/{trackingNumber}', [ScrapingController::class, 'scrapeShipment']);
});

// Warehouse Routes
Route::apiResource('warehouses', WarehouseController::class);

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication Routes (protected)
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    
    // User Shipment Routes (private - only user's shipments)
    Route::apiResource('shipments', ShipmentController::class);
    Route::get('/shipments/track/{trackingNumber}', [ShipmentController::class, 'track'])->name('api.shipments.track');
    Route::post('/shipments/track-and-save', [ShipmentController::class, 'trackAndSave'])->name('api.shipments.track-and-save');
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'CH Logistic API'
    ]);
});
