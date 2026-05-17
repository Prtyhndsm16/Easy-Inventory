<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'type',
        'quantity',
        'before_stock',
        'after_stock',
        'user_id',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'before_stock' => 'integer',
            'after_stock'  => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
