<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOut extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'quantity',
        'reason',
        'transfer_destination',
        'transfer_address',
        'date',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'     => 'date',
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
