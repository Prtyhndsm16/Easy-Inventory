<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_product_id',
        'product_name',
        'category',
        'price',
        'stock',
        'supplier',
        'barcode',
        'date_added',
        'image_path',
        'deleted_by',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'date_added' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return '/storage/'.ltrim($this->image_path, '/');
    }
}
