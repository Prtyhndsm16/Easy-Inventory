<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function __invoke(): View
    {
        $lowStockLimit = 10;
        $productsTableExists = Schema::hasTable('products');

        $stats = [
            'totalProducts' => 0,
            'totalStock' => 0,
            'lowStockCount' => 0,
        ];

        $lowStockProducts = collect();
        $recentProducts = collect();

        if ($productsTableExists) {
            $stats = [
                'totalProducts' => Product::count(),
                'totalStock' => Product::sum('stock'),
                'lowStockCount' => Product::where('stock', '<=', $lowStockLimit)->count(),
            ];

            $lowStockProducts = Product::where('stock', '<=', $lowStockLimit)
                ->orderBy('stock')
                ->orderBy('product_name')
                ->limit(5)
                ->get();

            $recentProducts = Product::latest()
                ->limit(5)
                ->get();
        }

        return view('staffdashboard', [
            'lowStockLimit' => $lowStockLimit,
            'productsTableExists' => $productsTableExists,
            'stats' => $stats,
            'lowStockProducts' => $lowStockProducts,
            'recentProducts' => $recentProducts,
        ]);
    }
}
