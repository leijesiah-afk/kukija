<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'string', 'in:price_asc,price_desc,newest,oldest,name_asc,name_desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Product::query()
            ->with(['category:id,name,slug'])
            ->where('is_active', true);

        if (! empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (! empty($validated['category'])) {
            $category = $validated['category'];
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category)
                    ->orWhere('name', $category);
            });
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        $sort = $validated['sort'] ?? 'newest';
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'oldest' => $query->orderBy('created_at'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = $validated['per_page'] ?? 12;

        $products = $query->paginate($perPage, [
            'id',
            'category_id',
            'name',
            'slug',
            'description',
            'price',
            'stock',
            'image_path',
            'sticker',
            'created_at',
        ]);

        return response()->json($products);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with(['category:id,name,slug'])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail([
                'id',
                'category_id',
                'name',
                'slug',
                'description',
                'price',
                'stock',
                'image_path',
                'sticker',
                'created_at',
            ]);

        return response()->json([
            'data' => $product,
        ]);
    }
}
