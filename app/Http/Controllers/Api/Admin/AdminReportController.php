<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    /**
     * Get sales report
     */
    public function salesReport(Request $request)
    {
        $request->validate([
            'period' => 'sometimes|in:day,week,month,year,custom',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date',
        ]);

        $period = $request->period ?? 'month';
        
        // Determine date range
        switch ($period) {
            case 'day':
                $dateFrom = now()->startOfDay();
                $dateTo = now()->endOfDay();
                break;
            case 'week':
                $dateFrom = now()->startOfWeek();
                $dateTo = now()->endOfWeek();
                break;
            case 'month':
                $dateFrom = now()->startOfMonth();
                $dateTo = now()->endOfMonth();
                break;
            case 'year':
                $dateFrom = now()->startOfYear();
                $dateTo = now()->endOfYear();
                break;
            case 'custom':
                $dateFrom = $request->date_from;
                $dateTo = $request->date_to;
                break;
            default:
                $dateFrom = now()->startOfMonth();
                $dateTo = now()->endOfMonth();
        }

        // Sales summary
        $summary = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as total_revenue,
                SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_orders,
                SUM(CASE WHEN payment_status = "pending" THEN 1 ELSE 0 END) as pending_orders,
                AVG(CASE WHEN payment_status = "paid" THEN total ELSE NULL END) as average_order_value
            ')
            ->first();

        // Sales by day
        $salesByDay = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Sales by product
        $salesByProduct = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->where('orders.payment_status', 'paid')
            ->select(
                'products.name',
                'products.image_path',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.image_path')
            ->orderBy('revenue', 'desc')
            ->get();

        return response()->json([
            'period' => $period,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => $summary,
            'sales_by_day' => $salesByDay,
            'sales_by_product' => $salesByProduct,
        ]);
    }

    /**
     * Get customer activity report
     */
    public function customerActivityReport(Request $request)
    {
        $request->validate([
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
        ]);

        $dateFrom = $request->date_from ?? now()->subMonth();
        $dateTo = $request->date_to ?? now();

        // New customers
        $newCustomers = User::whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        // Active customers (made at least one order)
        $activeCustomers = User::whereHas('orders', function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->count();

        // Customer segments
        $segments = [
            'high_value' => User::whereHas('orders', function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('payment_status', 'paid');
                })
                ->withSum(['orders as total_spent' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('payment_status', 'paid');
                }], 'total')
                ->having('total_spent', '>', 1000)
                ->count(),
            
            'medium_value' => User::whereHas('orders', function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('payment_status', 'paid');
                })
                ->withSum(['orders as total_spent' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('payment_status', 'paid');
                }], 'total')
                ->havingRaw('total_spent BETWEEN 500 AND 1000')
                ->count(),
            
            'low_value' => User::whereHas('orders', function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('payment_status', 'paid');
                })
                ->withSum(['orders as total_spent' => function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('payment_status', 'paid');
                }], 'total')
                ->having('total_spent', '<', 500)
                ->count(),
        ];

        return response()->json([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'new_customers' => $newCustomers,
            'active_customers' => $activeCustomers,
            'customer_segments' => $segments,
        ]);
    }

    /**
     * Get inventory report
     */
    public function inventoryReport()
    {
        $inventory = Inventory::with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_image' => $item->product->image_path,
                    'stock_quantity' => $item->stock_quantity,
                    'reorder_level' => $item->reorder_level,
                    'is_low_stock' => $item->isLowStock(),
                    'is_out_of_stock' => $item->isOutOfStock(),
                    'last_restocked_at' => $item->last_restocked_at,
                ];
            });

        $summary = [
            'total_products' => $inventory->count(),
            'low_stock_products' => $inventory->where('is_low_stock', true)->count(),
            'out_of_stock_products' => $inventory->where('is_out_of_stock', true)->count(),
            'total_stock_value' => $inventory->sum(function ($item) {
                return $item['stock_quantity'] * Product::find($item['product_id'])->price;
            }),
        ];

        return response()->json([
            'summary' => $summary,
            'inventory' => $inventory,
        ]);
    }

    /**
     * Export report (CSV format)
     */
    public function exportReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sales,customers,inventory',
            'format' => 'sometimes|in:csv,pdf',
        ]);

        $type = $request->type;
        $format = $request->format ?? 'csv';

        // For now, we'll just return the data
        // In production, you'd generate actual CSV/PDF files
        
        switch ($type) {
            case 'sales':
                $data = $this->salesReport($request)->getData();
                break;
            case 'customers':
                $data = $this->customerActivityReport($request)->getData();
                break;
            case 'inventory':
                $data = $this->inventoryReport()->getData();
                break;
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }

        return response()->json([
            'message' => 'Report data ready for export',
            'type' => $type,
            'format' => $format,
            'data' => $data,
        ]);
    }
}
