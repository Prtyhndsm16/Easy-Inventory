<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\DeletedProduct;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'deletedProductCount' => DeletedProduct::count(),
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

    public function show(Product $product): View
    {
        $movements = StockMovement::where('product_id', $product->product_id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('products.show', [
            'product'   => $product,
            'movements' => $movements,
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $validated = $request->safe()->except('product_image');
        $product = new Product($validated);

        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product-images', 'public');
            $product->image_path = $imagePath;
        }

        $product->save();

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
        $validated = $request->safe()->except('product_image');

        if ($request->hasFile('product_image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $imagePath = $request->file('product_image')->store('product-images', 'public');
            $product->image_path = $imagePath;
        }

        $product->fill($validated);
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

    public function deleted(Request $request): View
    {
        $search = Str::limit(trim((string) $request->string('search')), 100, '');

        $query = DeletedProduct::query()->latest('deleted_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        return view('products.deleted', [
            'products' => $query->paginate(10)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function restore(DeletedProduct $deletedProduct): RedirectResponse
    {
        $product = Product::withTrashed()->findOrFail($deletedProduct->original_product_id);

        DB::transaction(function () use ($deletedProduct, $product): void {
            $product->restore();
            $deletedProduct->delete();
        });

        AuditLogger::record('product.restored', 'success', [
            'product_id' => $product->getKey(),
            'product_name' => $product->product_name,
            'barcode' => $product->barcode,
        ], $product);

        return redirect()
            ->route('admin.products.deleted')
            ->with('status', 'Product restored successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product): void {
            DeletedProduct::updateOrCreate(
                ['original_product_id' => $product->getKey()],
                [
                    'product_name' => $product->product_name,
                    'category' => $product->category,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'supplier' => $product->supplier,
                    'barcode' => $product->barcode,
                    'date_added' => $product->date_added,
                    'image_path' => $product->image_path,
                    'deleted_by' => request()->user()?->id,
                    'deleted_at' => now(),
                ]
            );

            $product->delete();
        });

        AuditLogger::record('product.deleted', 'success', [
            'product_id' => $product->getKey(),
            'product_name' => $product->product_name,
            'barcode' => $product->barcode,
            'archived_to' => 'deleted_products',
        ], $product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product moved to the deleted_products table. You can restore it anytime.');
    }
}
