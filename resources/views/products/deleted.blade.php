<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Admin Inventory</p>
                <h2 class="section-title">
                    Deleted Products
                </h2>
                <p class="section-subtitle">Restore products that were removed from the active inventory list.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn-muted w-full sm:w-auto">
                Back to Products
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
                <form method="GET" action="{{ route('admin.products.deleted') }}" class="grid gap-4 p-4 sm:p-6 lg:grid-cols-[1fr_auto]">
                    <div class="form-field">
                        <label for="search" class="block text-sm font-semibold text-gray-700">Search deleted products</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Name, category, supplier, or barcode"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">
                            Apply
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('admin.products.deleted') }}" class="btn-muted">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Deleted Products Table</h3>
                    <p class="section-subtitle">
                        {{ $products->total() }} deleted product{{ $products->total() === 1 ? '' : 's' }} available to restore.
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
                                <th>Deleted At</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                                @if ($product->imageUrl())
                                                    <img
                                                        src="{{ $product->imageUrl() }}"
                                                        alt="{{ $product->product_name }} image"
                                                        class="h-full w-full object-cover"
                                                    >
                                                @else
                                                    <span class="text-xs font-semibold text-gray-400">IMG</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-950">{{ $product->product_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $product->barcode ?? 'No barcode' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-gray-600">{{ $product->category ?? 'Uncategorized' }}</td>
                                    <td class="font-medium text-gray-950">PHP {{ number_format((float) $product->price, 2) }}</td>
                                    <td class="text-gray-600">{{ number_format($product->stock) }}</td>
                                    <td class="text-gray-600">{{ $product->supplier ?? 'No supplier' }}</td>
                                    <td class="text-gray-600">{{ optional($product->deleted_at)->format('M d, Y h:i A') ?? '-' }}</td>
                                    <td>
                                        <div class="flex justify-end">
                                            <form method="POST" action="{{ route('admin.products.restore', $product) }}" onsubmit="return confirm('Restore this product to active inventory?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                                    Restore
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <p class="font-semibold text-gray-900">No deleted products.</p>
                                        <p class="mt-1 text-sm text-gray-500">Deleted products will appear here for restore.</p>
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
