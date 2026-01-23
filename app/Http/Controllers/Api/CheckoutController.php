<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $userId = $request->user()?->id;
        if (! $userId) {
            return response()->json([
                'message' => 'You must be logged in to checkout.',
            ], 401);
        }

        $order = null;
        $payment = null;

        try {
            DB::transaction(function () use ($userId, $validated, &$order, &$payment) {
                $cart = Cart::query()
                    ->where('user_id', $userId)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if (! $cart) {
                    throw (new ModelNotFoundException())->setModel(Cart::class);
                }

                $items = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->lockForUpdate()
                    ->get();

                if ($items->isEmpty()) {
                    throw new \RuntimeException('Cart is empty.');
                }

                $productIds = $items->pluck('product_id')->unique()->values();

                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $product = $products->get($item->product_id);
                    if (! $product) {
                        throw new \RuntimeException('One or more products in your cart are unavailable.');
                    }

                    if ($product->stock < $item->quantity) {
                        throw new \RuntimeException("Insufficient stock for {$product->name}.");
                    }
                }

                $subtotal = $items->sum(function (CartItem $item) {
                    return (float) $item->unit_price * (int) $item->quantity;
                });

                $shippingFee = 0.00;
                $total = $subtotal + $shippingFee;

                $orderNumber = 'KUK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

                $order = Order::query()->create([
                    'user_id' => $userId,
                    'order_number' => $orderNumber,
                    'status' => 'paid',
                    'customer_name' => $validated['name'],
                    'customer_email' => $validated['email'],
                    'shipping_address' => $validated['address'],
                    'customer_phone' => $validated['phone'] ?? null,
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'total' => $total,
                    'currency' => 'PHP',
                    'placed_at' => now(),
                ]);

                foreach ($items as $item) {
                    $product = $products->get($item->product_id);

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => $item->quantity,
                        'line_total' => (float) $product->price * (int) $item->quantity,
                    ]);

                    $product->update([
                        'stock' => $product->stock - (int) $item->quantity,
                    ]);
                }

                $payment = Payment::query()->create([
                    'order_id' => $order->id,
                    'provider' => 'dummy',
                    'status' => 'paid',
                    'amount' => $total,
                    'currency' => 'PHP',
                    'reference' => 'DUMMY-' . Str::upper(Str::random(10)),
                    'paid_at' => now(),
                    'raw_payload' => [
                        'type' => 'dummy',
                        'note' => 'Simulated payment for demo.',
                    ],
                ]);

                $cart->update(['status' => 'checked_out']);

                Cart::query()->create([
                    'user_id' => $userId,
                    'status' => 'active',
                ]);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart not found.',
            ], 404);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $order->load(['items', 'payment']);

        return response()->json([
            'message' => 'Checkout successful (dummy payment).',
            'order' => $order,
            'payment' => $payment,
        ]);
    }
}
