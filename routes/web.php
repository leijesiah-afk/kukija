<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

$basePath = trim(parse_url(config('app.url'), PHP_URL_PATH) ?? '', '/');

if ($basePath !== '') {
    Route::prefix($basePath . '/api')->middleware('api')->withoutMiddleware([VerifyCsrfToken::class])->group(function () {
        Route::get('/health', function () {
            return response()->json(['status' => 'ok']);
        });

        Route::get('/categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
        Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
        Route::get('/products/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'show']);
            Route::post('/cart/items', [\App\Http\Controllers\Api\CartController::class, 'addItem']);
            Route::patch('/cart/items/{id}', [\App\Http\Controllers\Api\CartController::class, 'updateItem']);
            Route::delete('/cart/items/{id}', [\App\Http\Controllers\Api\CartController::class, 'removeItem']);

            Route::get('/jar', [\App\Http\Controllers\Api\CartController::class, 'show']);
            Route::post('/jar/items', [\App\Http\Controllers\Api\CartController::class, 'addItem']);
            Route::patch('/jar/items/{id}', [\App\Http\Controllers\Api\CartController::class, 'updateItem']);
            Route::delete('/jar/items/{id}', [\App\Http\Controllers\Api\CartController::class, 'removeItem']);

            Route::post('/checkout', [\App\Http\Controllers\Api\CheckoutController::class, 'store']);

            Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
            Route::patch('/orders/{id}/received', [\App\Http\Controllers\Api\OrderController::class, 'markReceived']);

            Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
        });

        Route::prefix('auth')->group(function () {
            Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
            Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
            Route::middleware('auth:sanctum')->group(function () {
                Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
                Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
            });
        });

        Route::prefix('admin')->group(function () {
            Route::post('/login', [\App\Http\Controllers\Api\Admin\AdminAuthController::class, 'login'])->middleware('throttle:5,1');

            Route::middleware(['auth:sanctum', 'admin'])->group(function () {
                Route::get('/me', [\App\Http\Controllers\Api\Admin\AdminAuthController::class, 'me']);
                Route::post('/logout', [\App\Http\Controllers\Api\Admin\AdminAuthController::class, 'logout']);

                Route::get('/dashboard/stats', [\App\Http\Controllers\Api\Admin\AdminDashboardController::class, 'stats']);

                Route::get('/products', [\App\Http\Controllers\Api\Admin\AdminProductController::class, 'index']);
                Route::post('/products', [\App\Http\Controllers\Api\Admin\AdminProductController::class, 'store']);
                Route::patch('/products/{id}', [\App\Http\Controllers\Api\Admin\AdminProductController::class, 'update']);
                Route::delete('/products/{id}', [\App\Http\Controllers\Api\Admin\AdminProductController::class, 'destroy']);

                Route::get('/orders', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'index']);
                Route::get('/orders/{id}', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'show']);
                Route::patch('/orders/{id}/status', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'updateStatus']);
                Route::get('/orders/statistics', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'statistics']);

                Route::get('/customers', [\App\Http\Controllers\Api\Admin\AdminCustomerController::class, 'index']);
                Route::get('/customers/{id}', [\App\Http\Controllers\Api\Admin\AdminCustomerController::class, 'show']);
                Route::patch('/customers/{id}/status', [\App\Http\Controllers\Api\Admin\AdminCustomerController::class, 'updateStatus']);
                Route::get('/customers/statistics', [\App\Http\Controllers\Api\Admin\AdminCustomerController::class, 'statistics']);
                Route::get('/customers/activity-report', [\App\Http\Controllers\Api\Admin\AdminCustomerController::class, 'activityReport']);

                Route::get('/reports/sales', [\App\Http\Controllers\Api\Admin\AdminReportController::class, 'salesReport']);
                Route::get('/reports/customer-activity', [\App\Http\Controllers\Api\Admin\AdminReportController::class, 'customerActivityReport']);
                Route::get('/reports/inventory', [\App\Http\Controllers\Api\Admin\AdminReportController::class, 'inventoryReport']);
                Route::post('/reports/export', [\App\Http\Controllers\Api\Admin\AdminReportController::class, 'exportReport']);
            });
        });
    });
}

$excludedPrefixes = ['api'];
if ($basePath !== '') {
    $excludedPrefixes[] = preg_quote($basePath, '#') . '/api';
}

Route::view('/{any?}', 'app')
    ->where('any', '^(?!(?:' . implode('|', $excludedPrefixes) . ')(?:/|$)).*');
