<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Admin Inventory</p>
                <h2 class="text-2xl font-semibold leading-tight text-gray-900">
                    Product Management
                </h2>
            </div>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Add Product
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form method="GET" action="{{ route('admin.products.index') }}" class="grid gap-4 lg:grid-cols-[1fr_auto_auto]">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search products</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Search by name, category, supplier, or barcode"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div>
                        <label for="filter" class="block text-sm font-medium text-gray-700">Filter</label>
                        <select
                            id="filter"
                            name="filter"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">All products</option>
                            <option value="low-stock" @selected($filter === 'low-stock')>Low stock only</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                            Apply
                        </button>
                        @if ($search !== '' || $filter !== '')
                            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-950">Inventory List</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} found.
                        @if ($filter === 'low-stock')
                            Showing products with {{ $lowStockLimit }} stock or below.
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Price</th>
                                <th class="px-6 py-3">Stock</th>
                                <th class="px-6 py-3">Supplier</th>
                                <th class="px-6 py-3">Date Added</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $product->product_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $product->barcode ?? 'No barcode' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $product->category ?? 'Uncategorized' }}</td>
                                    <td class="px-6 py-4 text-gray-900">PHP {{ number_format((float) $product->price, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->stock <= $lowStockLimit ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ number_format($product->stock) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $product->supplier ?? 'No supplier' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ optional($product->date_added)->format('M d, Y') ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md border border-red-200 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No products found yet. Start by adding your first inventory item.
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
