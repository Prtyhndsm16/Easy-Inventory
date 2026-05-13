<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-blue-700">Staff Panel</p>
                <h2 class="text-2xl font-semibold leading-tight text-gray-900">
                    Staff Dashboard
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
                    Product database is not ready yet. Please ask an admin to run the migration.
                </div>
            @endunless

            <section class="rounded-lg bg-blue-950 px-6 py-7 text-white shadow-sm">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-sm font-medium text-blue-200">Inventory Access</p>
                        <h3 class="mt-2 text-3xl font-semibold tracking-normal">
                            Check inventory status without admin controls.
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-blue-100">
                            Staff users can view product counts, stock levels, low stock alerts,
                            and recent inventory activity. Editing, deleting, account management,
                            and system settings are reserved for admins.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('staff.products.index') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-50">
                            Open inventory page
                        </a>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Products</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-950">
                        {{ number_format($stats['totalProducts']) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-500">Products available to view</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Stock Units</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-950">
                        {{ number_format($stats['totalStock']) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-500">Total quantity on hand</p>
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-red-700">Low Stock Items</p>
                    <p class="mt-3 text-3xl font-semibold text-red-900">
                        {{ number_format($stats['lowStockCount']) }}
                    </p>
                    <p class="mt-2 text-sm text-red-700">Notify admin for restock</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-950">Low Stock Watchlist</h3>
                        <p class="mt-1 text-sm text-gray-500">Items with {{ $lowStockLimit }} stock or below.</p>
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
                                            No low stock products to show.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-950">Staff Access Limits</h3>
                    <div class="mt-4 space-y-4 text-sm">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="font-medium text-gray-900">Allowed</p>
                            <p class="mt-1 text-gray-500">View dashboard summaries, inventory status, and low stock alerts.</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="font-medium text-gray-900">Restricted</p>
                            <p class="mt-1 text-gray-500">Add, edit, delete, price updates, user roles, and system settings.</p>
                        </div>
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                            <p class="font-medium text-blue-900">Next module</p>
                            <p class="mt-1 text-blue-700">The read-only inventory page is now available for staff use.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-950">Recent Inventory Records</h3>
                    <p class="mt-1 text-sm text-gray-500">Read-only view of the latest products.</p>
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
                                <p class="font-semibold text-gray-950">{{ number_format($product->stock) }} stock</p>
                                <p class="text-sm text-gray-500">{{ $product->supplier ?? 'No supplier' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-500">
                            No product records available yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
