@php
    $totalProducts = (int) ($stats['totalProducts'] ?? 0);
    $totalStock = (int) ($stats['totalStock'] ?? 0);
    $lowStockCount = (int) ($stats['lowStockCount'] ?? 0);
    $inventoryValue = (float) ($stats['inventoryValue'] ?? 0);
    $todaySales = (float) ($stats['todaySales'] ?? 0);
    $yesterdaySales = (float) ($stats['yesterdaySales'] ?? 0);
    $monthSales = (float) ($stats['monthSales'] ?? 0);
    $todayOrders = (int) ($stats['todayOrders'] ?? 0);
    $todayItemsSold = (int) ($stats['todayItemsSold'] ?? 0);
    $averageSale = (float) ($stats['averageSale'] ?? 0);
    $salesDeltaPercent = $stats['salesDeltaPercent'] ?? null;
    $stockOutsToday = (int) ($stats['stockOutsToday'] ?? 0);
    $stockOutUnitsToday = (int) ($stats['stockOutUnitsToday'] ?? 0);
    $lowStockPercent = $totalProducts > 0 ? min(100, round(($lowStockCount / $totalProducts) * 100)) : 0;
    $averageStock = $totalProducts > 0 ? round($totalStock / $totalProducts) : 0;
    $salesTrend = collect($salesTrend ?? []);
    $topProducts = collect($topProducts ?? []);
    $topProductsMaxUnits = max(1, (int) $topProducts->max('units_sold'));
    $trendHasSales = $salesTrend->sum('total') > 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Store Owner View</p>
                <h2 class="section-title">
                    Business Dashboard
                </h2>
                <p class="section-subtitle">
                    Daily sales, product movement, and inventory risks for {{ now()->format('F d, Y') }}.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ route('admin.products.create') }}" class="btn-primary w-full sm:w-auto">
                    Add Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn-muted w-full sm:w-auto">
                    View Inventory
                </a>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">
            @unless ($productsTableExists)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <span class="font-semibold">Database setup needed:</span>
                    run <code class="rounded bg-amber-100 px-1.5 py-0.5">php artisan migrate</code>
                    so the dashboard can read product records.
                </div>
            @endunless

            @unless ($salesTableExists)
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                    <span class="font-semibold">Sales reporting is ready:</span>
                    run <code class="rounded bg-blue-100 px-1.5 py-0.5">php artisan migrate</code>
                    to create the sales tables. Revenue charts will fill in once sales records are saved.
                </div>
            @endunless

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="stat-card border-emerald-200 bg-emerald-50">
                    <p class="stat-label text-emerald-700">Sales Today</p>
                    <p class="stat-value text-emerald-950">PHP {{ number_format($todaySales, 2) }}</p>
                    <p class="stat-note text-emerald-700">
                        @if ($salesDeltaPercent === null)
                            No yesterday baseline yet
                        @else
                            {{ $salesDeltaPercent >= 0 ? '+' : '' }}{{ number_format($salesDeltaPercent, 1) }}% vs yesterday
                        @endif
                    </p>
                </div>

                <div class="stat-card">
                    <p class="stat-label">Yesterday Sales</p>
                    <p class="stat-value">PHP {{ number_format($yesterdaySales, 2) }}</p>
                    <p class="stat-note">Use this as today's comparison target</p>
                </div>

                <div class="stat-card">
                    <p class="stat-label">Month-to-Date Sales</p>
                    <p class="stat-value">PHP {{ number_format($monthSales, 2) }}</p>
                    <p class="stat-note">{{ number_format($todayOrders) }} transaction{{ $todayOrders === 1 ? '' : 's' }} today</p>
                </div>

                <div class="stat-card">
                    <p class="stat-label">Items Sold Today</p>
                    <p class="stat-value">{{ number_format($todayItemsSold) }}</p>
                    <p class="stat-note">Average sale: PHP {{ number_format($averageSale, 2) }}</p>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Stock Outs Today</p>
                            <p class="stat-value">{{ number_format($stockOutsToday) }}</p>
                        </div>
                        <span class="stat-icon bg-amber-100 text-amber-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M10 3v10M6 9l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 17h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">{{ number_format($stockOutUnitsToday) }} units removed today</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-12">
                <div class="panel xl:col-span-8">
                    <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950">7-Day Sales Trend</h3>
                            <p class="section-subtitle">A quick chart for spotting slow and strong sales days.</p>
                        </div>
                        <span class="badge {{ $trendHasSales ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $trendHasSales ? 'Live sales data' : 'Waiting for sales' }}
                        </span>
                    </div>

                    <div class="panel-body">
                        <div class="flex h-56 items-end gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-4 sm:gap-4 sm:px-5">
                            @foreach ($salesTrend as $day)
                                <div class="flex h-full min-w-0 flex-1 flex-col justify-end gap-2">
                                    <div class="flex flex-1 items-end">
                                        <div
                                            class="w-full rounded-t-lg bg-emerald-500 transition"
                                            style="height: {{ $day['height'] }}%"
                                            title="{{ $day['date'] }}: PHP {{ number_format((float) $day['total'], 2) }}"
                                        ></div>
                                    </div>
                                    <div class="text-center">
                                        <p class="truncate text-xs font-semibold text-gray-700">{{ $day['label'] }}</p>
                                        <p class="truncate text-[11px] text-gray-500">PHP {{ number_format((float) $day['total'], 0) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <aside class="panel xl:col-span-4">
                    <div class="panel-header">
                        <h3 class="text-lg font-semibold text-gray-950">Accounting Snapshot</h3>
                        <p class="section-subtitle">Numbers a store owner checks first.</p>
                    </div>

                    <div class="panel-body space-y-4">
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <p class="text-sm font-medium text-gray-500">Inventory Value</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-950">PHP {{ number_format($inventoryValue, 2) }}</p>
                            <p class="mt-1 text-xs text-gray-500">Estimated from current price x stock</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <div class="rounded-lg border border-gray-100 p-4">
                                <p class="text-sm font-medium text-gray-500">Products</p>
                                <p class="mt-1 text-xl font-semibold text-gray-950">{{ number_format($totalProducts) }}</p>
                            </div>
                            <div class="rounded-lg border border-gray-100 p-4">
                                <p class="text-sm font-medium text-gray-500">Stock Units</p>
                                <p class="mt-1 text-xl font-semibold text-gray-950">{{ number_format($totalStock) }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ number_format($averageStock) }} average per product</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-600">Low-stock exposure</span>
                                <span class="font-semibold text-gray-950">{{ $lowStockPercent }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-red-500" style="width: {{ $lowStockPercent }}%"></div>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="grid gap-6 xl:grid-cols-12">
                <div class="panel xl:col-span-7">
                    <div class="panel-header">
                        <h3 class="text-lg font-semibold text-gray-950">Top 5 Selling Products</h3>
                        <p class="section-subtitle">Best movers from the last 30 days, ranked by units sold.</p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($topProducts as $product)
                            @php
                                $unitsSold = (int) $product->units_sold;
                                $share = round(($unitsSold / $topProductsMaxUnits) * 100);
                            @endphp

                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-950">{{ $product->product_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $product->barcode ?? 'No barcode' }}</p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="font-semibold text-gray-950">{{ number_format($unitsSold) }} sold</p>
                                        <p class="text-sm text-gray-500">PHP {{ number_format((float) $product->revenue, 2) }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $share }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <p class="font-semibold text-gray-900">No sales recorded yet.</p>
                                <p class="mt-1 text-sm text-gray-500">Once sales are saved, best-selling products will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="table-shell xl:col-span-5">
                    <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950">Restock Watch</h3>
                            <p class="section-subtitle">Products at {{ $lowStockLimit }} stock or below.</p>
                        </div>
                        <a href="{{ route('admin.products.index', ['filter' => 'low-stock']) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                            Open list
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 sm:px-6">Product</th>
                                    <th class="px-4 py-3 text-right sm:px-6">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($lowStockProducts as $product)
                                    <tr class="transition hover:bg-gray-50">
                                        <td class="px-4 py-4 align-top sm:px-6">
                                            <div class="font-semibold text-gray-950">{{ $product->product_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->supplier ?? 'No supplier' }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-right align-top sm:px-6">
                                            <span class="badge bg-red-100 text-red-700">
                                                {{ number_format($product->stock) }} left
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-12 text-center">
                                            <p class="font-semibold text-gray-900">No low-stock products.</p>
                                            <p class="mt-1 text-sm text-gray-500">Inventory is above the alert threshold.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950">Recently Added Products</h3>
                        <p class="section-subtitle">Latest products created by the team.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        See all products
                    </a>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($recentProducts as $product)
                        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <p class="font-semibold text-gray-950">{{ $product->product_name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $product->category ?? 'Uncategorized' }} / {{ $product->barcode ?? 'No barcode' }}
                                </p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="font-semibold text-gray-950">PHP {{ number_format((float) $product->price, 2) }}</p>
                                <p class="text-sm text-gray-500">{{ number_format($product->stock) }} in stock</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm text-gray-500">
                            Add products to populate the activity list.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Recent Stock Outs --}}
            @if (isset($recentStockOuts) && $recentStockOuts->isNotEmpty())
            <section class="panel">
                <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950">Recent Stock Outs</h3>
                        <p class="section-subtitle">Latest items removed from inventory today.</p>
                    </div>
                    <a href="{{ route('admin.stock-out.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">View all</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($recentStockOuts as $item)
                        @php
                            $reasonColors = ['Damaged'=>'bg-red-100 text-red-700','Expired'=>'bg-amber-100 text-amber-800','Transferred'=>'bg-blue-100 text-blue-700'];
                        @endphp
                        <div class="flex flex-col gap-2 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <p class="font-semibold text-gray-950">{{ $item->product_name }}</p>
                                <p class="text-sm text-gray-500">{{ $item->date->format('M d, Y') }} &bull; {{ $item->recorder?->name ?? 'Unknown' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="badge {{ $reasonColors[$item->reason] ?? 'bg-gray-100 text-gray-700' }}">{{ $item->reason }}</span>
                                <span class="font-semibold text-gray-950">{{ $item->quantity }} units</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </div>
</x-app-layout>
