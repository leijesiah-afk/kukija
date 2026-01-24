<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if (! $userId) {
            return response()->json([
                'message' => 'You must be logged in.',
            ], 401);
        }

        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::query()
            ->where('id', $validated['order_id'])
            ->where('user_id', $userId)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        if (!in_array($order->status, ['delivered'], true)) {
            return response()->json([
                'message' => 'You can only review after your order is delivered.',
            ], 422);
        }

        $purchased = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('product_id', $validated['product_id'])
            ->exists();

        if (! $purchased) {
            return response()->json([
                'message' => 'You can only review items from this order.',
            ], 422);
        }

        try {
            $review = Review::query()->create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'product_id' => $validated['product_id'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'You already reviewed this item.',
            ], 409);
        }

        return response()->json([
            'message' => 'Review submitted.',
            'review' => $review,
        ], 201);
    }
}
