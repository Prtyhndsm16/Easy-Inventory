<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
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
        Product::create($request->validated());

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
        $product->update($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
