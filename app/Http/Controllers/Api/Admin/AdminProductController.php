<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Product::query()->with(['category:id,name,slug']);

        if (! empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        $perPage = $validated['per_page'] ?? 20;
        $products = $query->orderByDesc('id')->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'sticker' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slugBase = $validated['slug'] ?? Str::slug($validated['name']);
        $slug = $slugBase;

        $i = 2;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$i;
            $i++;
        }

        $product = Product::query()->create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image_path' => $validated['image_path'] ?? null,
            'sticker' => $validated['sticker'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $product->load(['category:id,name,slug']);

        return response()->json([
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'sticker' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('slug', $validated)) {
            $slugBase = trim($validated['slug']) !== '' ? $validated['slug'] : Str::slug($validated['name'] ?? $product->name);
            $slug = $slugBase;

            $i = 2;
            while (Product::query()->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $slugBase.'-'.$i;
                $i++;
            }

            $validated['slug'] = $slug;
        }

        $product->update($validated);
        $product->refresh()->load(['category:id,name,slug']);

        return response()->json([
            'data' => $product,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Deleted.',
        ]);
    }
}
