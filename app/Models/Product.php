<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_name',
        'category',
        'price',
        'cost_price',
        'stock',
        'supplier',
        'barcode',
        'date_added',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock'      => 'integer',
            'date_added' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Profit margin as a percentage: ((price - cost_price) / cost_price) * 100
     * Returns null if cost_price is not set.
     */
    public function profitMarginPercent(): ?float
    {
        if (! $this->cost_price || $this->cost_price <= 0) {
            return null;
        }

        return round(((float) $this->price - (float) $this->cost_price) / (float) $this->cost_price * 100, 1);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            \Illuminate\Support\Facades\Log::debug('Product imageUrl: no image_path', [
                'product_id' => $this->product_id ?? null,
                'product_name' => $this->product_name ?? null,
            ]);

            return null;
        }

        $path = str_replace('\\', '/', $this->image_path);

        if (preg_match('/^https?:\/\//i', $path)) {
            \Illuminate\Support\Facades\Log::debug('Product imageUrl: using absolute URL', [
                'product_id' => $this->product_id ?? null,
                'product_name' => $this->product_name ?? null,
                'image_path' => $this->image_path,
                'url' => $path,
            ]);

            return $path;
        }

        $path = ltrim($path, '/');
        $url = asset(str_starts_with($path, 'storage/') ? $path : 'storage/'.$path);

        \Illuminate\Support\Facades\Log::debug('Product imageUrl: built URL', [
            'product_id' => $this->product_id ?? null,
            'product_name' => $this->product_name ?? null,
            'image_path' => $this->image_path,
            'url' => $url,
        ]);

        return $url;
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'product_id', 'product_id');
    }
}
