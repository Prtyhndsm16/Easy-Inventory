<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $lowStockLimit = 10;
        $productsTableExists = Schema::hasTable('products');

        $stats = [
            'totalProducts' => 0,
            'totalStock' => 0,
            'lowStockCount' => 0,
            'inventoryValue' => 0,
            'categoryCount' => 0,
            'supplierCount' => 0,
            'userCount' => User::count(),
            'staffCount' => User::where('role', 'staff')->count(),
        ];

        $lowStockProducts = collect();
        $recentProducts = collect();

        if ($productsTableExists) {
            $stats = [
                'totalProducts' => Product::count(),
                'totalStock' => Product::sum('stock'),
                'lowStockCount' => Product::where('stock', '<=', $lowStockLimit)->count(),
                'inventoryValue' => Product::selectRaw('COALESCE(SUM(price * stock), 0) as value')->value('value'),
                'categoryCount' => Product::whereNotNull('category')->distinct('category')->count('category'),
                'supplierCount' => Product::whereNotNull('supplier')->distinct('supplier')->count('supplier'),
                'userCount' => User::count(),
                'staffCount' => User::where('role', 'staff')->count(),
            ];

            $lowStockProducts = Product::where('stock', '<=', $lowStockLimit)
                ->orderBy('stock')
                ->orderBy('product_name')
                ->limit(5)
                ->get();

            $recentProducts = Product::latest()
                ->limit(6)
                ->get();
        }

        return view('dashboard', [
            'lowStockLimit' => $lowStockLimit,
            'productsTableExists' => $productsTableExists,
            'stats' => $stats,
            'lowStockProducts' => $lowStockProducts,
            'recentProducts' => $recentProducts,
        ]);
    }
}
