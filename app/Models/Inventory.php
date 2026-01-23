<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock_quantity',
        'reorder_level',
        'last_restocked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_restocked_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }
}
