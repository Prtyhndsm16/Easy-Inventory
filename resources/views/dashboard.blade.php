<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Admin Panel</p>
                <h2 class="text-2xl font-semibold leading-tight text-gray-900">
                    EasyInventory Dashboard
                </h2>
            </div>
            <p class="text-sm text-gray-500">
                {{ now()->format('F d, Y') }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @unless ($productsTableExists)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <span class="font-semibold">Database setup needed:</span>
                    run <code class="rounded bg-amber-100 px-1.5 py-0.5">php artisan migrate</code>
                    so the dashboard can read product records.
                </div>
            @endunless

            <section class="overflow-hidden rounded-lg bg-gray-950 text-white shadow-sm">
                <div class="grid gap-6 px-6 py-7 lg:grid-cols-[1.45fr_0.55fr] lg:items-center">
                    <div>
                        <p class="text-sm font-medium text-emerald-300">Inventory Overview</p>
                        <h3 class="mt-2 text-3xl font-semibold tracking-normal">
                            Monitor products, stock levels, and alerts in one place.
                        </h3>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-300">
                            This dashboard is prepared for product management, low stock monitoring,
                            store activity, and admin controls for EasyInventory Manager.
                        </p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                        <p class="text-sm text-gray-300">Low stock threshold</p>
                        <p class="mt-2 text-4xl font-semibold">{{ $lowStockLimit }}</p>
                        <p class="mt-1 text-sm text-gray-300">items or below</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Products</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-950">
                        {{ number_format($stats['totalProducts']) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-500">Items registered in inventory</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Stock Units</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-950">
                        {{ number_format($stats['totalStock']) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-500">Combined quantity on hand</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Inventory Value</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-950">
                        PHP {{ number_format((float) $stats['inventoryValue'], 2) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-500">Estimated stock value</p>
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-red-700">Low Stock Items</p>
                    <p class="mt-3 text-3xl font-semibold text-red-900">
                        {{ number_format($stats['lowStockCount']) }}
                    </p>
                    <p class="mt-2 text-sm text-red-700">Needs restock attention</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-950">Low Stock Alerts</h3>
                        <p class="mt-1 text-sm text-gray-500">Products at {{ $lowStockLimit }} stock or below.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Product</th>
                                    <th class="px-6 py-3">Category</th>
                                    <th class="px-6 py-3">Supplier</th>
                                    <th class="px-6 py-3 text-right">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $product->product_name }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $product->category ?? 'Uncategorized' }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $product->supplier ?? 'No supplier' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                                {{ number_format($product->stock) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                            No low stock products to show yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-950">Quick Actions</h3>
                        <div class="mt-4 grid gap-3">
                            <a href="#" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-emerald-300 hover:bg-emerald-50">
                                <span>Add new product</span>
                                <span aria-hidden="true">+</span>
                            </a>
                            <a href="#" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-emerald-300 hover:bg-emerald-50">
                                <span>View inventory list</span>
                                <span aria-hidden="true">></span>
                            </a>
                            <a href="#" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-emerald-300 hover:bg-emerald-50">
                                <span>Generate stock report</span>
                                <span aria-hidden="true">></span>
                            </a>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-950">Inventory Summary</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Categories</dt>
                                <dd class="font-semibold text-gray-900">{{ number_format($stats['categoryCount']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Suppliers</dt>
                                <dd class="font-semibold text-gray-900">{{ number_format($stats['supplierCount']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Security</dt>
                                <dd class="font-semibold text-emerald-700">Login enabled</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-950">Recently Added Products</h3>
                        <p class="mt-1 text-sm text-gray-500">Latest inventory records will appear here.</p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($recentProducts as $product)
                            <div class="flex items-center justify-between gap-4 px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-950">{{ $product->product_name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $product->category ?? 'Uncategorized' }} - {{ $product->barcode ?? 'No barcode' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-950">PHP {{ number_format((float) $product->price, 2) }}</p>
                                    <p class="text-sm text-gray-500">{{ number_format($product->stock) }} in stock</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-gray-500">
                                Add products later to populate this activity list.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-950">Admin Checklist</h3>
                    <div class="mt-4 space-y-4">
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Password hashing</p>
                                <p class="text-sm text-gray-500">Laravel authentication stores passwords securely.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Session management</p>
                                <p class="text-sm text-gray-500">Admin dashboard is protected by auth middleware.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Role-based access</p>
                                <p class="text-sm text-gray-500">Ready to add Admin and Staff roles next.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Product CRUD</p>
                                <p class="text-sm text-gray-500">Add, edit, delete, and search pages are the next module.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
