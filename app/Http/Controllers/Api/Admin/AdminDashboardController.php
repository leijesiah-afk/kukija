<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $salesToday = (float) Order::query()
            ->whereBetween('placed_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total');

        $ordersToday = (int) Order::query()
            ->whereBetween('placed_at', [$todayStart, $todayEnd])
            ->count();

        $lowStock = (int) Product::query()
            ->where('is_active', true)
            ->where('stock', '<=', 5)
            ->count();

        $totalProducts = (int) Product::query()
            ->where('is_active', true)
            ->count();

        return response()->json([
            'sales_today' => $salesToday,
            'orders_today' => $ordersToday,
            'low_stock' => $lowStock,
            'total_products' => $totalProducts,
        ]);
    }
}
