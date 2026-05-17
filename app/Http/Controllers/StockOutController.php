<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOut;
use App\Support\LowStockNotifier;
use App\Support\StockMovementLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockOutController extends Controller
{
    public static array $reasons = ['Damaged', 'Expired', 'Transferred'];

    public function index(Request $request): View
    {
        $reason = (string) $request->string('reason');
        $search = trim((string) $request->string('search'));

        $query = StockOut::query()
            ->with(['product', 'recorder'])
            ->latest('date')
            ->latest('id');

        if ($reason !== '') {
            $query->where('reason', $reason);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return view('stock-out.index', [
            'stockOuts' => $query->paginate(20)->withQueryString(),
            'products'  => Product::query()->orderBy('product_name')->get(['product_id', 'product_name', 'stock', 'barcode']),
            'reasons'   => self::$reasons,
            'reason'    => $reason,
            'search'    => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'           => ['required', 'exists:products,product_id'],
            'quantity'             => ['required', 'integer', 'min:1'],
            'reason'               => ['required', 'in:Damaged,Expired,Transferred'],
            'date'                 => ['required', 'date', 'before_or_equal:today'],
            'transfer_destination' => ['required_if:reason,Transferred', 'nullable', 'string', 'max:255'],
            'transfer_address'     => ['required_if:reason,Transferred', 'nullable', 'string', 'max:500'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Prevent negative stock
        if ($validated['quantity'] > $product->stock) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => "Only {$product->stock} unit(s) of \"{$product->product_name}\" available in stock.",
            ]);
        }

        $beforeStock = $product->stock;

        // Deduct stock
        $product->decrement('stock', $validated['quantity']);
        $afterStock = $product->fresh()->stock;

        StockOut::create([
            'product_id'           => $product->product_id,
            'product_name'         => $product->product_name,
            'quantity'             => $validated['quantity'],
            'reason'               => $validated['reason'],
            'transfer_destination' => $validated['reason'] === 'Transferred' ? ($validated['transfer_destination'] ?? null) : null,
            'transfer_address'     => $validated['reason'] === 'Transferred' ? ($validated['transfer_address'] ?? null) : null,
            'date'                 => $validated['date'],
            'recorded_by'          => Auth::id(),
            'notes'                => $validated['notes'] ?? null,
        ]);

        StockMovementLogger::record(
            productId:   $product->product_id,
            productName: $product->product_name,
            type:        'stock_out',
            quantity:    -$validated['quantity'],
            beforeStock: $beforeStock,
            afterStock:  $afterStock,
            notes:       $validated['reason'] . ($validated['notes'] ? ': ' . $validated['notes'] : ''),
        );

        LowStockNotifier::check([[
            'id'     => $product->product_id,
            'name'   => $product->product_name,
            'before' => $beforeStock,
            'after'  => $afterStock,
        ]]);

        return redirect()->route('admin.stock-out.index')
            ->with('success', "Stock out recorded. {$validated['quantity']} unit(s) of \"{$product->product_name}\" deducted.");
    }

    public function destroy(StockOut $stockOut): RedirectResponse
    {
        // Restore the stock when a stock-out record is deleted
        if ($stockOut->product) {
            $beforeStock = $stockOut->product->stock;
            $stockOut->product->increment('stock', $stockOut->quantity);
            $afterStock = $stockOut->product->fresh()->stock;

            StockMovementLogger::record(
                productId:   $stockOut->product->product_id,
                productName: $stockOut->product_name,
                type:        'manual',
                quantity:    $stockOut->quantity,
                beforeStock: $beforeStock,
                afterStock:  $afterStock,
                notes:       'Stock-out record deleted — stock restored',
            );
        }

        $stockOut->delete();

        return redirect()->route('admin.stock-out.index')
            ->with('success', 'Stock out record deleted and stock restored.');
    }
}
