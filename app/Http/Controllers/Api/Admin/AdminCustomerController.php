<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCustomerController extends Controller
{
    /**
     * Get all customers with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = User::withCount('orders')
            ->withSum('orders as total_revenue', 'total');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort by
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $customers = $query->paginate($request->per_page ?? 15);

        return response()->json($customers);
    }

    /**
     * Get single customer details with order history
     */
    public function show($id)
    {
        $customer = User::with(['orders' => function ($query) {
                $query->with('items.product')
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            }])
            ->withCount('orders')
            ->withSum('orders as total_revenue', 'total')
            ->findOrFail($id);

        return response()->json($customer);
    }

    /**
     * Update customer status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $customer = User::findOrFail($id);
        $oldStatus = $customer->status;

        $customer->update([
            'status' => $request->status,
        ]);

        // Log activity
        ActivityLog::create([
            'admin_user_id' => $request->user()->id,
            'action_type' => 'customer_update',
            'description' => "Updated customer {$customer->email} status from {$oldStatus} to {$request->status}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Customer status updated successfully',
            'customer' => $customer
        ]);
    }

    /**
     * Get customer statistics
     */
    public function statistics()
    {
        $stats = [
            'total_customers' => User::count(),
            'active_customers' => User::where('status', 'active')->count(),
            'inactive_customers' => User::where('status', 'inactive')->count(),
            'suspended_customers' => User::where('status', 'suspended')->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->count(),
        ];

        // Top customers by revenue
        $topCustomers = User::select('users.*')
            ->selectRaw('(SELECT SUM(total) FROM orders WHERE orders.user_id = users.id AND orders.payment_status = "paid") as total_spent')
            ->selectRaw('(SELECT COUNT(*) FROM orders WHERE orders.user_id = users.id) as total_orders')
            ->having('total_spent', '>', 0)
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();

        // Customer growth (last 6 months)
        $customerGrowth = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'stats' => $stats,
            'top_customers' => $topCustomers,
            'customer_growth' => $customerGrowth,
        ]);
    }

    /**
     * Get customer activity report
     */
    public function activityReport(Request $request)
    {
        $request->validate([
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
        ]);

        $query = User::with(['orders' => function ($q) use ($request) {
            if ($request->has('date_from')) {
                $q->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $q->whereDate('created_at', '<=', $request->date_to);
            }
        }]);

        $customers = $query->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name ?? ($customer->first_name . ' ' . $customer->last_name),
                'email' => $customer->email,
                'orders_count' => $customer->orders->count(),
                'total_spent' => $customer->orders->sum('total'),
                'last_order_date' => $customer->orders->max('created_at'),
            ];
        });

        return response()->json($customers);
    }
}
