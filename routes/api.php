<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminCustomerController;
use App\Http\Controllers\Api\Admin\AdminReportController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::patch('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);

    Route::get('/jar', [CartController::class, 'show']);
    Route::post('/jar/items', [CartController::class, 'addItem']);
    Route::patch('/jar/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/jar/items/{id}', [CartController::class, 'removeItem']);

    Route::post('/checkout', [CheckoutController::class, 'store']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);

        // Products
        Route::get('/products', [AdminProductController::class, 'index']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::patch('/products/{id}', [AdminProductController::class, 'update']);
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);

        // Orders (Jars)
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
        Route::get('/orders/statistics', [AdminOrderController::class, 'statistics']);

        // Customers
        Route::get('/customers', [AdminCustomerController::class, 'index']);
        Route::get('/customers/{id}', [AdminCustomerController::class, 'show']);
        Route::patch('/customers/{id}/status', [AdminCustomerController::class, 'updateStatus']);
        Route::get('/customers/statistics', [AdminCustomerController::class, 'statistics']);
        Route::get('/customers/activity-report', [AdminCustomerController::class, 'activityReport']);

        // Reports
        Route::get('/reports/sales', [AdminReportController::class, 'salesReport']);
        Route::get('/reports/customer-activity', [AdminReportController::class, 'customerActivityReport']);
        Route::get('/reports/inventory', [AdminReportController::class, 'inventoryReport']);
        Route::post('/reports/export', [AdminReportController::class, 'exportReport']);
    });
});
