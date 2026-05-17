<?php

namespace App\Support;

use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class StockMovementLogger
{
    /**
     * Record a stock movement event.
     *
     * @param  int         $productId
     * @param  string      $productName
     * @param  string      $type        stock_in | stock_out | sale | manual
     * @param  int         $quantity    positive = added, negative = removed
     * @param  int         $beforeStock
     * @param  int         $afterStock
     * @param  string|null $reference   receipt number, stock-out id, etc.
     * @param  string|null $notes
     * @param  int|null    $userId
     */
    public static function record(
        int $productId,
        string $productName,
        string $type,
        int $quantity,
        int $beforeStock,
        int $afterStock,
        ?string $reference = null,
        ?string $notes = null,
        ?int $userId = null,
    ): void {
        StockMovement::create([
            'product_id'   => $productId,
            'product_name' => $productName,
            'type'         => $type,
            'quantity'     => $quantity,
            'before_stock' => $beforeStock,
            'after_stock'  => $afterStock,
            'user_id'      => $userId ?? Auth::id(),
            'reference'    => $reference,
            'notes'        => $notes,
        ]);
    }
}
