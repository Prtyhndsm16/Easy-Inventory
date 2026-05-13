<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Admin Inventory</p>
                <h2 class="section-title">
                    Product Management
                </h2>
                <p class="section-subtitle">Search, filter, update, and maintain inventory records.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add Product
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="panel">
                <form method="GET" action="{{ route('admin.products.index') }}" class="grid gap-4 p-5 lg:grid-cols-[1fr_220px_auto] sm:p-6">
                    <div class="form-field">
                        <label for="search" class="block text-sm font-semibold text-gray-700">Search products</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Name, category, supplier, or barcode"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="form-field">
                        <label for="filter" class="block text-sm font-semibold text-gray-700">Filter</label>
                        <select
                            id="filter"
                            name="filter"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">All products</option>
                            <option value="low-stock" @selected($filter === 'low-stock')>Low stock only</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap items-end gap-3">
                        <button type="submit" class="btn-primary">
                            Apply
                        </button>
                        @if ($search !== '' || $filter !== '')
                            <a href="{{ route('admin.products.index') }}" class="btn-muted">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="table-shell">
                <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950">Inventory List</h3>
                        <p class="section-subtitle">
                            {{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} found.
                            @if ($filter === 'low-stock')
                                Showing products with {{ $lowStockLimit }} stock or below.
                            @endif
                        </p>
                    </div>
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
                                <th class="text-right">Actions</th>
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
                                        <span class="badge {{ $product->stock <= $lowStockLimit ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ number_format($product->stock) }}
                                        </span>
                                    </td>
                                    <td class="text-gray-600">{{ $product->supplier ?? 'No supplier' }}</td>
                                    <td class="text-gray-600">{{ optional($product->date_added)->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <p class="font-semibold text-gray-900">No products found.</p>
                                        <p class="mt-1 text-sm text-gray-500">Add your first inventory item or adjust the current filters.</p>
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
