<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Products</p>
                <h2 class="section-title">{{ $product->product_name }}</h2>
                <p class="section-subtitle">Full details and stock movement history.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary">Edit Product</a>
                <a href="{{ route('admin.products.index') }}" class="btn-muted">Back to Products</a>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">

            {{-- Product Details Card --}}
            <section class="panel">
                <div class="panel-header border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-950">Product Details</h3>
                </div>
                <div class="grid gap-6 p-4 sm:p-6 sm:grid-cols-2 lg:grid-cols-3">

                    @if ($product->imageUrl())
                        <div class="sm:col-span-2 lg:col-span-1 flex justify-center lg:justify-start">
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->product_name }}"
                                 class="h-40 w-40 rounded-xl border border-gray-200 object-cover shadow-sm">
                        </div>
                    @else
                        @if ($product->image_path)
                            <div class="sm:col-span-2 lg:col-span-1 flex justify-center lg:justify-start">
                                <div class="h-40 w-40 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center text-xs text-gray-500">
                                    IMG
                                </div>
                                <div class="mt-2 text-[11px] text-gray-500 break-all">image_path: {{ $product->image_path }}</div>
                            </div>
                        @endif
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 sm:col-span-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Product Name</p>
                            <p class="mt-1 font-semibold text-gray-950">{{ $product->product_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Barcode</p>
                            <p class="mt-1 font-mono text-gray-700">{{ $product->barcode ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Category</p>
                            <p class="mt-1 text-gray-700">{{ $product->category ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Supplier</p>
                            <p class="mt-1 text-gray-700">{{ $product->supplier ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Selling Price</p>
                            <p class="mt-1 font-semibold text-gray-950">₱{{ number_format((float) $product->price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Cost Price</p>
                            <p class="mt-1 font-semibold text-gray-950">
                                {{ $product->cost_price ? '₱' . number_format((float) $product->cost_price, 2) : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Profit Margin</p>
                            @php $margin = $product->profitMarginPercent(); @endphp
                            <p class="mt-1 font-semibold {{ $margin === null ? 'text-gray-400' : ($margin >= 0 ? 'text-emerald-700' : 'text-red-600') }}">
                                {{ $margin !== null ? ($margin >= 0 ? '+' : '') . $margin . '%' : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Current Stock</p>
                            <p class="mt-1">
                                <span class="badge text-base {{ $product->stock <= 10 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ number_format($product->stock) }} units
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Date Added</p>
                            <p class="mt-1 text-gray-700">{{ optional($product->date_added)->format('M d, Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Inventory Value</p>
                            <p class="mt-1 font-semibold text-gray-950">₱{{ number_format((float) $product->price * $product->stock, 2) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Stock Movement History --}}
            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Stock Movement History</h3>
                    <p class="section-subtitle">Every stock change — deliveries, sales, removals, and manual adjustments.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Qty Change</th>
                                <th>Before</th>
                                <th>After</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $mv)
                                @php
                                    $typeConfig = [
                                        'stock_in'  => ['label' => 'Stock In',  'class' => 'bg-emerald-100 text-emerald-700'],
                                        'stock_out' => ['label' => 'Stock Out', 'class' => 'bg-red-100 text-red-700'],
                                        'sale'      => ['label' => 'Sale',      'class' => 'bg-blue-100 text-blue-700'],
                                        'manual'    => ['label' => 'Manual',    'class' => 'bg-gray-100 text-gray-700'],
                                    ];
                                    $cfg = $typeConfig[$mv->type] ?? ['label' => $mv->type, 'class' => 'bg-gray-100 text-gray-700'];
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600 text-sm">{{ $mv->created_at->format('M d, Y h:i A') }}</td>
                                    <td><span class="badge {{ $cfg['class'] }}">{{ $cfg['label'] }}</span></td>
                                    <td class="font-semibold {{ $mv->quantity >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                        {{ $mv->quantity >= 0 ? '+' : '' }}{{ $mv->quantity }}
                                    </td>
                                    <td class="text-gray-600">{{ $mv->before_stock }}</td>
                                    <td class="font-semibold text-gray-950">{{ $mv->after_stock }}</td>
                                    <td class="text-sm text-gray-600">{{ $mv->reference ?? '—' }}</td>
                                    <td class="text-sm text-gray-600">{{ $mv->notes ?? '—' }}</td>
                                    <td class="text-sm text-gray-700">{{ $mv->user?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center">
                                        <p class="font-semibold text-gray-900">No stock movement history yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">History is logged when stock changes through Stock In, Stock Out, or Sales.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($movements->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $movements->links() }}
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
