<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffProductController extends Controller
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

        return view('staffproducts', [
            'products' => $products,
            'search' => $search,
            'filter' => $filter,
            'lowStockLimit' => $lowStockLimit,
        ]);
    }
}
