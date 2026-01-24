<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user?->id;
        if (! $userId) {
            return response()->json([
                'message' => 'You must be logged in.',
            ], 401);
        }

        $limit = (int) ($request->query('limit', 200));
        if ($limit < 1) {
            $limit = 200;
        }
        if ($limit > 500) {
            $limit = 500;
        }

        if (!empty($user?->email)) {
            $email = (string) $user->email;

            $unlinked = Order::query()
                ->whereNull('user_id')
                ->where('customer_email', $email)
                ->orderByDesc('placed_at')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit($limit)
                ->get(['id']);

            if ($unlinked->isNotEmpty()) {
                Order::query()
                    ->whereIn('id', $unlinked->pluck('id')->values())
                    ->update(['user_id' => $userId]);
            }
        }

        $loadOrders = function () use ($userId, $limit) {
            return Order::query()
                ->where('user_id', $userId)
                ->with(['items.product', 'payment'])
                ->orderByDesc('placed_at')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        };

        $orders = $loadOrders();

        $orderIds = $orders->pluck('id')->values();

        $reviews = collect();
        if ($orderIds->isNotEmpty() && Schema::hasTable('reviews')) {
            $reviews = Review::query()
                ->where('user_id', $userId)
                ->whereIn('order_id', $orderIds)
                ->get();
        }

        $reviewsByOrder = $reviews->groupBy('order_id')->map(function ($items) {
            return $items->map(function (Review $r) {
                return [
                    'id' => $r->id,
                    'product_id' => $r->product_id,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'created_at' => $r->created_at,
                ];
            })->values();
        });

        $payload = $orders->map(function (Order $order) use ($reviewsByOrder) {
            $data = $order->toArray();
            $data['my_reviews'] = $reviewsByOrder->get($order->id, collect())->toArray();
            return $data;
        })->values();

        return response()->json([
            'data' => $payload,
        ]);
    }

    public function markReceived(Request $request, $id): JsonResponse
    {
        $userId = $request->user()?->id;
        if (! $userId) {
            return response()->json([
                'message' => 'You must be logged in.',
            ], 401);
        }

        $order = Order::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        if (!in_array($order->status, ['shipped', 'delivered'], true)) {
            return response()->json([
                'message' => 'Order cannot be marked as received yet.',
            ], 422);
        }

        if (! $order->received_at) {
            $order->received_at = now();
        }

        $order->status = 'delivered';
        $order->save();

        $order->load(['items.product', 'payment']);

        return response()->json([
            'message' => 'Order marked as received.',
            'order' => $order,
        ]);
    }
}
