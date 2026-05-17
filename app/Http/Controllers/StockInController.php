<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Support\AuditLogger;
use App\Support\StockMovementLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockInController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $query = StockIn::query()
            ->with(['product', 'receiver'])
            ->latest('date')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return view('stock-in.index', [
            'stockIns' => $query->paginate(20)->withQueryString(),
            'products' => Product::query()->orderBy('product_name')->get(['product_id', 'product_name', 'stock', 'barcode']),
            'search'   => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'existing');

        if ($mode === 'new') {
            return $this->storeWithNewProduct($request);
        }

        return $this->storeExisting($request);
    }

    // ── Mode: existing product ────────────────────────────────────────────────

    private function storeExisting(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'   => ['required', 'exists:products,product_id'],
            'quantity'     => ['required', 'integer', 'min:1'],
            'supplier'     => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'date'         => ['required', 'date', 'before_or_equal:today'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $product     = Product::findOrFail($validated['product_id']);
        $beforeStock = $product->stock;

        $product->increment('stock', $validated['quantity']);
        $afterStock = $product->fresh()->stock;

        StockIn::create([
            'product_id'   => $product->product_id,
            'product_name' => $product->product_name,
            'quantity'     => $validated['quantity'],
            'supplier'     => $validated['supplier'] ?? null,
            'reference_no' => $validated['reference_no'] ?? null,
            'date'         => $validated['date'],
            'received_by'  => Auth::id(),
            'notes'        => $validated['notes'] ?? null,
        ]);

        StockMovementLogger::record(
            productId:   $product->product_id,
            productName: $product->product_name,
            type:        'stock_in',
            quantity:    $validated['quantity'],
            beforeStock: $beforeStock,
            afterStock:  $afterStock,
            reference:   $validated['reference_no'] ?? null,
            notes:       $validated['notes'] ?? null,
        );

        return redirect()->route('admin.stock-in.index')
            ->with('success', "{$validated['quantity']} unit(s) of \"{$product->product_name}\" added to stock.");
    }

    // ── Mode: new product + stock in ─────────────────────────────────────────

    private function storeWithNewProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'new_product_name'     => ['required', 'string', 'max:255'],
            'new_product_price'    => ['required', 'numeric', 'min:0'],
            'new_product_category' => ['nullable', 'string', 'max:255'],
            'new_product_barcode'  => ['nullable', 'string', 'max:255'],
            'quantity'             => ['required', 'integer', 'min:1'],
            'supplier'             => ['nullable', 'string', 'max:255'],
            'reference_no'         => ['nullable', 'string', 'max:255'],
            'date'                 => ['required', 'date', 'before_or_equal:today'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        // Create the new product with initial stock = 0
        $product = Product::create([
            'product_name' => $validated['new_product_name'],
            'price'        => $validated['new_product_price'],
            'category'     => $validated['new_product_category'] ?? null,
            'barcode'      => $validated['new_product_barcode'] ?? null,
            'supplier'     => $validated['supplier'] ?? null,
            'stock'        => 0,
            'date_added'   => $validated['date'],
        ]);

        AuditLogger::record('product.created_via_stock_in', 'success', [
            'product_id'   => $product->product_id,
            'product_name' => $product->product_name,
            'note'         => 'Created automatically during stock-in receiving.',
        ]);

        // Now increment with the received quantity
        $beforeStock = 0;
        $product->increment('stock', $validated['quantity']);
        $afterStock = $product->fresh()->stock;

        StockIn::create([
            'product_id'   => $product->product_id,
            'product_name' => $product->product_name,
            'quantity'     => $validated['quantity'],
            'supplier'     => $validated['supplier'] ?? null,
            'reference_no' => $validated['reference_no'] ?? null,
            'date'         => $validated['date'],
            'received_by'  => Auth::id(),
            'notes'        => ($validated['notes'] ?? '') . ' [New product registered during receiving]',
        ]);

        StockMovementLogger::record(
            productId:   $product->product_id,
            productName: $product->product_name,
            type:        'stock_in',
            quantity:    $validated['quantity'],
            beforeStock: $beforeStock,
            afterStock:  $afterStock,
            reference:   $validated['reference_no'] ?? null,
            notes:       'Initial stock from receiving — product created on the spot',
        );

        return redirect()->route('admin.stock-in.index')
            ->with('success', "New product \"{$product->product_name}\" registered and {$validated['quantity']} unit(s) added to stock.");
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(StockIn $stockIn): RedirectResponse
    {
        $product = $stockIn->product;

        if ($product) {
            $beforeStock = $product->stock;
            $product->decrement('stock', $stockIn->quantity);
            $afterStock = $product->fresh()->stock;

            StockMovementLogger::record(
                productId:   $product->product_id,
                productName: $product->product_name,
                type:        'manual',
                quantity:    -$stockIn->quantity,
                beforeStock: $beforeStock,
                afterStock:  $afterStock,
                notes:       'Stock-in record deleted — stock reversed',
            );
        }

        $stockIn->delete();

        return redirect()->route('admin.stock-in.index')
            ->with('success', 'Stock in record deleted and stock reversed.');
    }
}
