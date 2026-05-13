<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-700">Staff Inventory</p>
                <h2 class="section-title">
                    Product List
                </h2>
                <p class="section-subtitle">Search and monitor inventory records in read-only mode.</p>
            </div>
            <a href="{{ route('staff.dashboard') }}" class="btn-muted">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">
            <section class="panel">
                <form method="GET" action="{{ route('staff.products.index') }}" class="grid gap-4 p-5 lg:grid-cols-[1fr_220px_auto] sm:p-6">
                    <div class="form-field">
                        <label for="search" class="block text-sm font-semibold text-gray-700">Search inventory</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Name, category, supplier, or barcode"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div class="form-field">
                        <label for="filter" class="block text-sm font-semibold text-gray-700">Filter</label>
                        <select
                            id="filter"
                            name="filter"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All products</option>
                            <option value="low-stock" @selected($filter === 'low-stock')>Low stock only</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap items-end gap-3">
                        <button type="submit" class="btn-primary-blue">
                            Apply
                        </button>
                        @if ($search !== '' || $filter !== '')
                            <a href="{{ route('staff.products.index') }}" class="btn-muted">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Inventory Records</h3>
                    <p class="section-subtitle">
                        {{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} available for viewing.
                        @if ($filter === 'low-stock')
                            Showing products with {{ $lowStockLimit }} stock or below.
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Supplier</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-gray-950">{{ $product->product_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $product->barcode ?? 'No barcode' }}</div>
                                    </td>
                                    <td class="text-gray-600">{{ $product->category ?? 'Uncategorized' }}</td>
                                    <td class="font-medium text-gray-950">PHP {{ number_format((float) $product->price, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $product->stock <= $lowStockLimit ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ number_format($product->stock) }}
                                        </span>
                                    </td>
                                    <td class="text-gray-600">{{ $product->supplier ?? 'No supplier' }}</td>
                                    <td class="text-gray-600">{{ optional($product->date_added)->format('M d, Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <p class="font-semibold text-gray-900">No products available.</p>
                                        <p class="mt-1 text-sm text-gray-500">Ask an admin to add inventory records.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $products->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
