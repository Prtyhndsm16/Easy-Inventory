<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $lowStockLimit = 10;
        $productsTableExists = Schema::hasTable('products');
        $salesTableExists = Schema::hasTable('sales') && Schema::hasTable('sale_items');
        $today = now();
        $yesterday = now()->subDay();

        $stats = [
            'totalProducts'      => 0,
            'totalStock'         => 0,
            'lowStockCount'      => 0,
            'inventoryValue'     => 0,
            'categoryCount'      => 0,
            'supplierCount'      => 0,
            'userCount'          => User::count(),
            'staffCount'         => User::where('role', 'staff')->count(),
            'todaySales'         => 0,
            'yesterdaySales'     => 0,
            'salesDeltaPercent'  => null,
            'todayOrders'        => 0,
            'todayItemsSold'     => 0,
            'monthSales'         => 0,
            'averageSale'        => 0,
            'stockOutsToday'     => 0,
            'stockOutUnitsToday' => 0,
        ];

        $lowStockProducts = collect();
        $recentProducts = collect();
        $salesTrend = collect(range(6, 0))->map(function (int $daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'date' => $date->format('M d'),
                'total' => 0,
                'orders' => 0,
                'height' => 6,
            ];
        });
        $topProducts = collect();

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
                'todaySales' => 0,
                'yesterdaySales' => 0,
                'salesDeltaPercent' => null,
                'todayOrders' => 0,
                'todayItemsSold' => 0,
                'monthSales' => 0,
                'averageSale' => 0,
            ];

            $lowStockProducts = Product::where('stock', '<=', $lowStockLimit)
                ->orderBy('stock')
                ->orderBy('product_name')
                ->limit(5)
                ->get();

            $recentProducts = Product::latest()
                ->limit(6)
                ->get();

            if (Schema::hasTable('stock_outs')) {
                $stats['stockOutsToday']     = StockOut::whereDate('date', today())->count();
                $stats['stockOutUnitsToday'] = (int) StockOut::whereDate('date', today())->sum('quantity');
            }
        }

        if ($salesTableExists) {
            $todaySales = (float) DB::table('sales')
                ->whereDate('sold_at', $today->toDateString())
                ->sum('total_amount');
            $yesterdaySales = (float) DB::table('sales')
                ->whereDate('sold_at', $yesterday->toDateString())
                ->sum('total_amount');
            $todayOrders = (int) DB::table('sales')
                ->whereDate('sold_at', $today->toDateString())
                ->count();
            $todayItemsSold = (int) DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereDate('sales.sold_at', $today->toDateString())
                ->sum('sale_items.quantity');
            $monthSales = (float) DB::table('sales')
                ->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_amount');
            $averageSale = $todayOrders > 0 ? $todaySales / $todayOrders : 0;

            $stats['todaySales'] = $todaySales;
            $stats['yesterdaySales'] = $yesterdaySales;
            $stats['salesDeltaPercent'] = $yesterdaySales > 0
                ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
                : null;
            $stats['todayOrders'] = $todayOrders;
            $stats['todayItemsSold'] = $todayItemsSold;
            $stats['monthSales'] = $monthSales;
            $stats['averageSale'] = $averageSale;

            $trendStart = now()->subDays(6)->startOfDay();
            $trendRows = DB::table('sales')
                ->selectRaw('DATE(sold_at) as sale_date, COALESCE(SUM(total_amount), 0) as total, COUNT(*) as orders')
                ->whereBetween('sold_at', [$trendStart, now()->endOfDay()])
                ->groupByRaw('DATE(sold_at)')
                ->orderBy('sale_date')
                ->get()
                ->keyBy('sale_date');
            $maxTrendSales = max(1, (float) $trendRows->max('total'));
            $salesTrend = collect(range(6, 0))->map(function (int $daysAgo) use ($trendRows, $maxTrendSales) {
                $date = now()->subDays($daysAgo);
                $row = $trendRows->get($date->toDateString());
                $total = (float) ($row->total ?? 0);

                return [
                    'label' => $date->format('D'),
                    'date' => $date->format('M d'),
                    'total' => $total,
                    'orders' => (int) ($row->orders ?? 0),
                    'height' => max(6, round(($total / $maxTrendSales) * 100)),
                ];
            });

            $topProducts = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->selectRaw('sale_items.product_id, sale_items.product_name, sale_items.barcode, SUM(sale_items.quantity) as units_sold, SUM(sale_items.line_total) as revenue')
                ->whereBetween('sales.sold_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
                ->groupBy('sale_items.product_id', 'sale_items.product_name', 'sale_items.barcode')
                ->orderByDesc('units_sold')
                ->orderByDesc('revenue')
                ->limit(5)
                ->get();
        }

        return view('dashboard', [
            'lowStockLimit'      => $lowStockLimit,
            'productsTableExists' => $productsTableExists,
            'salesTableExists'   => $salesTableExists,
            'stats'              => $stats,
            'lowStockProducts'   => $lowStockProducts,
            'recentProducts'     => $recentProducts,
            'salesTrend'         => $salesTrend,
            'topProducts'        => $topProducts,
            'recentStockOuts'    => Schema::hasTable('stock_outs')
                ? StockOut::with('recorder')->whereDate('date', today())->latest('id')->limit(5)->get()
                : collect(),
        ]);
    }
}
