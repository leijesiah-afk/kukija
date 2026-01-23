<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    /**
     * Get all orders with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'payment'])
            ->select('orders.*')
            ->selectRaw('(SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) as item_count');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by order number or customer
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($orders);
    }

    /**
     * Get single order details
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'payment'])
            ->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
        ]);

        $order = Order::findOrFail($id);
        
        $oldStatus = $order->status;
        $oldPaymentStatus = $order->payment_status;

        $order->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status ?? $order->payment_status,
        ]);

        // Log activity
        ActivityLog::create([
            'admin_user_id' => $request->user()->id,
            'action_type' => 'order_update',
            'description' => "Updated order #{$order->order_number} status from {$oldStatus} to {$request->status}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->fresh(['user', 'items.product', 'payment'])
        ]);
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request)
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'pending_payments' => Order::where('payment_status', 'pending')->sum('total'),
        ];

        // Sales by day (last 7 days)
        $salesByDay = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Top products
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->select(
                'products.name',
                'products.image_path',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.line_total) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.image_path')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'sales_by_day' => $salesByDay,
            'top_products' => $topProducts,
        ]);
    }
}
