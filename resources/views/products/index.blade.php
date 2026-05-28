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
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ route('admin.products.deleted') }}" class="btn-muted w-full sm:w-auto">
                    Deleted Products
                    @if ($deletedProductCount > 0)
                        <span class="badge bg-red-100 text-red-700">{{ number_format($deletedProductCount) }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.products.create') }}" class="btn-primary w-full sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Product
                </a>
            </div>
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
                <form method="GET" action="{{ route('admin.products.index') }}" class="grid gap-4 p-4 sm:p-6 lg:grid-cols-[1fr_220px_auto]">
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

                    <div class="filter-actions">
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
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                                @if ($product->image_path)
                                                    <img
                                                        src="{{ asset('storage/' . $product->image_path) }}"
                                                        alt="Product Image"
                                                        class="h-full w-full object-cover"
                                                    >
                                                @else
                                                    <span class="text-xs font-semibold text-gray-400">IMG</span>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.products.show', $product) }}"
                                                   class="font-semibold text-gray-950 hover:text-emerald-700 hover:underline">
                                                    {{ $product->product_name }}
                                                </a>
                                                <div class="text-xs text-gray-500">{{ $product->barcode ?? 'No barcode' }}</div>
                                            </div>
                                        </div>
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
                                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Move this product to Deleted Products? You can restore it later.');">
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
