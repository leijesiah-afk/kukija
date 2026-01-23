<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request, true);

        if (! $cart) {
            return response()->json([
                'message' => 'You must be logged in to start a jar.',
            ], 401);
        }

        $cart->load(['items.product.category:id,name,slug']);

        return response()->json($this->cartResponse($cart));
    }

    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $qty = (int) ($validated['quantity'] ?? 1);

        $cart = $this->resolveCart($request, true);

        if (! $cart) {
            return response()->json([
                'message' => 'You must be logged in to start a jar.',
            ], 401);
        }

        try {
            DB::transaction(function () use ($validated, $qty, $cart) {
                $product = Product::query()
                    ->where('id', $validated['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $item = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                $newQty = $qty + ($item?->quantity ?? 0);
                if ($product->stock < $newQty) {
                    throw new \RuntimeException('Insufficient stock for this product.');
                }

                if ($item) {
                    $item->update([
                        'quantity' => $newQty,
                        'unit_price' => $product->price,
                    ]);
                } else {
                    CartItem::query()->create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $product->price,
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $cart->refresh()->load(['items.product.category:id,name,slug']);

        return response()->json($this->cartResponse($cart));
    }

    public function updateItem(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->resolveCart($request, false);

        if (! $cart) {
            throw (new ModelNotFoundException())->setModel(Cart::class);
        }

        try {
            DB::transaction(function () use ($cart, $id, $validated) {
                $item = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product = Product::query()
                    ->where('id', $item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < (int) $validated['quantity']) {
                    throw new \RuntimeException('Insufficient stock for this product.');
                }

                $item->update([
                    'quantity' => (int) $validated['quantity'],
                    'unit_price' => $product->price,
                ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $cart->refresh()->load(['items.product.category:id,name,slug']);

        return response()->json($this->cartResponse($cart));
    }

    public function removeItem(Request $request, int $id): JsonResponse
    {
        $cart = $this->resolveCart($request, false);

        if (! $cart) {
            throw (new ModelNotFoundException())->setModel(Cart::class);
        }

        CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('id', $id)
            ->delete();

        $cart->refresh()->load(['items.product.category:id,name,slug']);

        return response()->json($this->cartResponse($cart));
    }

    private function resolveCart(Request $request, bool $createIfMissing): ?Cart
    {
        $userId = $request->user()?->id;

        if (! $userId) {
            return null;
        }

        $cart = Cart::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (! $cart && $createIfMissing) {
            $cart = Cart::query()->create([
                'user_id' => $userId,
                'status' => 'active',
            ]);
        }

        return $cart;
    }

    private function cartResponse(Cart $cart): array
    {
        $items = $cart->items->map(function (CartItem $item) {
            $product = $item->product;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => (float) $item->unit_price * (int) $item->quantity,
                'product' => $product ? [
                    'id' => $product->id,
                    'category_id' => $product->category_id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image_path' => $product->image_path,
                    'sticker' => $product->sticker,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                ] : null,
            ];
        })->values();

        $subtotal = $items->sum('line_total');

        return [
            'cart' => [
                'id' => $cart->id,
                'status' => $cart->status,
                'created_at' => $cart->created_at,
                'updated_at' => $cart->updated_at,
            ],
            'items' => $items,
            'totals' => [
                'subtotal' => $subtotal,
                'total_items' => $items->sum('quantity'),
            ],
        ];
    }
}
