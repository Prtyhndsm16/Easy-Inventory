@php
    $totalProducts = (int) ($stats['totalProducts'] ?? 0);
    $totalStock = (int) ($stats['totalStock'] ?? 0);
    $lowStockCount = (int) ($stats['lowStockCount'] ?? 0);
    $inventoryValue = (float) ($stats['inventoryValue'] ?? 0);
    $lowStockPercent = $totalProducts > 0 ? min(100, round(($lowStockCount / $totalProducts) * 100)) : 0;
    $averageStock = $totalProducts > 0 ? round($totalStock / $totalProducts) : 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Admin Panel</p>
                <h2 class="section-title">
                    Inventory Dashboard
                </h2>
                <p class="section-subtitle">
                    Stock health, product movement, and account overview for {{ now()->format('F d, Y') }}.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ route('admin.products.create') }}" class="btn-primary w-full sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn-muted w-full sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4 6h12M4 10h12M4 14h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
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

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Total Products</p>
                            <p class="stat-value">{{ number_format($totalProducts) }}</p>
                        </div>
                        <span class="stat-icon bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M4 6.5 10 3l6 3.5v7L10 17l-6-3.5v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="m4 6.5 6 3.5 6-3.5M10 10v7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">Registered inventory items</p>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Stock Units</p>
                            <p class="stat-value">{{ number_format($totalStock) }}</p>
                        </div>
                        <span class="stat-icon bg-blue-100 text-blue-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5 15V8M10 15V5M15 15v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">{{ number_format($averageStock) }} average units per product</p>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Inventory Value</p>
                            <p class="stat-value">PHP {{ number_format($inventoryValue, 2) }}</p>
                        </div>
                        <span class="stat-icon bg-violet-100 text-violet-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5 6h6.5a3.5 3.5 0 0 1 0 7H5M5 3v14M4 6h10M4 10h9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">Estimated value from price x stock</p>
                </div>

                <div class="stat-card stat-card-alert">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-red-700">Low Stock Items</p>
                            <p class="mt-3 text-3xl font-semibold text-red-900">{{ number_format($lowStockCount) }}</p>
                        </div>
                        <span class="stat-icon bg-red-100 text-red-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M10 4v6M10 14h.01M3.8 16h12.4L10 4 3.8 16Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-red-700">{{ $lowStockPercent }}% of products need attention</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-12">
                <div class="table-shell xl:col-span-8">
                    <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950">Low Stock Priority</h3>
                            <p class="section-subtitle">Products at {{ $lowStockLimit }} stock or below, sorted by urgency.</p>
                        </div>
                        <a href="{{ route('admin.products.index', ['filter' => 'low-stock']) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                            Open filtered list
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Supplier</th>
                                    <th class="text-right">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td>
                                            <div class="font-semibold text-gray-950">{{ $product->product_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->barcode ?? 'No barcode' }}</div>
                                        </td>
                                        <td class="text-gray-600">{{ $product->category ?? 'Uncategorized' }}</td>
                                        <td class="text-gray-600">{{ $product->supplier ?? 'No supplier' }}</td>
                                        <td class="text-right">
                                            <span class="badge bg-red-100 text-red-700">
                                                {{ number_format($product->stock) }} left
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center">
                                            <div class="mx-auto max-w-sm">
                                                <p class="font-semibold text-gray-900">No low stock products.</p>
                                                <p class="mt-1 text-sm text-gray-500">Inventory is currently above the alert threshold.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <aside class="panel xl:col-span-4">
                    <div class="panel-header">
                        <h3 class="text-lg font-semibold text-gray-950">Inventory Coverage</h3>
                        <p class="section-subtitle">A quick read on catalog depth and staffing.</p>
                    </div>

                    <div class="panel-body space-y-5">
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-600">Low-stock exposure</span>
                                <span class="font-semibold text-gray-950">{{ $lowStockPercent }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-red-500" style="width: {{ $lowStockPercent }}%"></div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-sm font-medium text-gray-500">Categories</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-950">{{ number_format($stats['categoryCount']) }}</p>
                            </div>
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-sm font-medium text-gray-500">Suppliers</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-950">{{ number_format($stats['supplierCount']) }}</p>
                            </div>
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-sm font-medium text-gray-500">User Accounts</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-950">{{ number_format($stats['userCount']) }}</p>
                            </div>
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-sm font-medium text-gray-500">Staff Accounts</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-950">{{ number_format($stats['staffCount']) }}</p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <a href="{{ route('admin.users.index') }}" class="btn-muted w-full">
                                Manage Users
                            </a>
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn-muted w-full">
                                View Audit Logs
                            </a>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="panel">
                <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950">Recently Added Products</h3>
                        <p class="section-subtitle">Latest inventory records added by the team.</p>
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
        </div>
    </div>
</x-app-layout>
