<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::limit(trim((string) $request->string('search')), 100, '');
        $filter = (string) $request->string('filter');
        $lowStockLimit = 10;

        $query = Product::query()->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($filter === 'low-stock') {
            $query->where('stock', '<=', $lowStockLimit);
        }

        $products = $query->paginate(10)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'search' => $search,
            'filter' => $filter,
            'lowStockLimit' => $lowStockLimit,
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product([
                'date_added' => now()->toDateString(),
            ]),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        AuditLogger::record('product.created', 'success', [
            'product_name' => $product->product_name,
            'barcode' => $product->barcode,
        ], $product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->fill($request->validated());
        $changedFields = array_keys($product->getDirty());
        $product->save();

        AuditLogger::record('product.updated', 'success', [
            'product_name' => $product->product_name,
            'changed_fields' => $changedFields,
        ], $product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        AuditLogger::record('product.deleted', 'success', [
            'product_id' => $product->getKey(),
            'product_name' => $product->product_name,
            'barcode' => $product->barcode,
        ], $product);

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
