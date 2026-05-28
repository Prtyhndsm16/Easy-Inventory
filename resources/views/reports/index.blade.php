<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Admin</p>
                <h2 class="section-title">Reports</h2>
                <p class="section-subtitle">View and download inventory, stock out, and sales reports by date range.</p>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">

            {{-- Filters --}}
            <section class="panel">
                <div class="panel-header border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-950">Report Filters</h3>
                    <p class="section-subtitle">Select the date range for stock out and sales data.</p>
                </div>

                <form method="GET" action="{{ route('admin.reports.index') }}"
                      class="grid gap-4 p-4 sm:p-6 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto_auto]">

                    <div class="form-field">
                        <label for="date_from" class="block text-sm font-semibold text-gray-700">Date From</label>
                        <input id="date_from" name="date_from" type="date"
                               value="{{ $dateFrom }}"
                               max="{{ now()->toDateString() }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="form-field">
                        <label for="date_to" class="block text-sm font-semibold text-gray-700">Date To</label>
                        <input id="date_to" name="date_to" type="date"
                               value="{{ $dateTo }}"
                               max="{{ now()->toDateString() }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="form-field">
                        <label for="reason" class="block text-sm font-semibold text-gray-700">Stock Out Reason</label>
                        <select id="reason" name="reason"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All Reasons</option>
                            @foreach ($reasons as $r)
                                <option value="{{ $r }}" @selected($reason === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-actions sm:col-span-2 lg:col-span-1">
                        <button type="submit" class="btn-primary">Generate</button>
                        <a href="{{ route('admin.reports.index') }}" class="btn-muted">Reset</a>
                    </div>

                    <div class="flex items-end sm:col-span-2 lg:col-span-1">
                        <a href="{{ route('admin.reports.download', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'reason' => $reason]) }}"
                           class="btn-primary w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 focus:ring-red-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a1 1 0 001 1h10a1 1 0 001-1v-1M8 12l2 2 2-2M10 4v10"/>
                            </svg>
                            Download PDF
                        </a>
                    </div>
                </form>
                <p class="px-6 pb-4 text-xs text-gray-400">Report generated: {{ $generatedAt }}</p>
            </section>

            {{-- Summary Stats --}}
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Total Products</p>
                            <p class="stat-value">{{ number_format($summary['totalProducts']) }}</p>
                        </div>
                        <span class="stat-icon bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M4 6.5 10 3l6 3.5v7L10 17l-6-3.5v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">{{ number_format($summary['totalStock']) }} total stock units</p>
                </div>

                <div class="stat-card stat-card-alert">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-red-700">Low Stock Items</p>
                            <p class="mt-3 text-3xl font-semibold text-red-900">{{ number_format($summary['lowStockCount']) }}</p>
                        </div>
                        <span class="stat-icon bg-red-100 text-red-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M10 4v6M10 14h.01M3.8 16h12.4L10 4 3.8 16Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-red-700">10 units or below threshold</p>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Stock In (Period)</p>
                            <p class="stat-value">{{ number_format($summary['stockInCount']) }}</p>
                        </div>
                        <span class="stat-icon bg-teal-100 text-teal-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M10 17V7M6 11l4-4 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 3h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">{{ number_format($summary['stockInUnits']) }} units received</p>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Stock Outs</p>
                            <p class="stat-value">{{ number_format($summary['stockOutCount']) }}</p>
                        </div>
                        <span class="stat-icon bg-amber-100 text-amber-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M10 3v10M6 9l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 17h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">{{ number_format($summary['stockOutUnits']) }} units removed in period</p>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="stat-label">Total Sales</p>
                            <p class="stat-value">{{ number_format($summary['salesCount']) }}</p>
                        </div>
                        <span class="stat-icon bg-blue-100 text-blue-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M2 5h16M2 10h10M2 15h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="stat-note">₱{{ number_format($summary['salesTotal'], 2) }} total revenue</p>
                </div>
            </section>

            {{-- Low Stock --}}
            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Low Stock Products</h3>
                    <p class="section-subtitle">Products at 10 units or below — needs attention.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Barcode</th>
                                <th class="text-right">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStock as $product)
                                <tr>
                                    <td class="font-semibold text-gray-950">{{ $product->product_name }}</td>
                                    <td class="text-gray-600">{{ $product->category ?? '—' }}</td>
                                    <td class="text-gray-600">{{ $product->supplier ?? '—' }}</td>
                                    <td class="text-gray-500 text-sm">{{ $product->barcode ?? '—' }}</td>
                                    <td class="text-right">
                                        <span class="badge bg-red-100 text-red-700">{{ $product->stock }} left</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-sm text-gray-500">No low stock products. 🎉</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Stock In --}}
            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Stock In Records</h3>
                    <p class="section-subtitle">{{ $dateFrom }} to {{ $dateTo }} · {{ $stockIns->count() }} record(s) · {{ number_format($summary['stockInUnits']) }} units received</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Qty Received</th>
                                <th>Supplier</th>
                                <th>Reference No.</th>
                                <th>Notes</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stockIns as $item)
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600">{{ $item->date->format('M d, Y') }}</td>
                                    <td class="font-semibold text-gray-950">{{ $item->product_name }}</td>
                                    <td>
                                        <span class="badge bg-teal-100 text-teal-700">+{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-gray-600">{{ $item->supplier ?? '—' }}</td>
                                    <td class="text-sm text-gray-600">{{ $item->reference_no ?? '—' }}</td>
                                    <td class="text-sm text-gray-600">{{ $item->notes ?? '—' }}</td>
                                    <td class="text-sm text-gray-700">{{ $item->receiver?->name ?? 'Unknown' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-gray-500">No stock in records for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($stockIns->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 bg-teal-50">
                                    <td colspan="2" class="px-4 py-3 text-sm font-semibold text-teal-900">Total Units Received</td>
                                    <td class="px-4 py-3 text-sm font-bold text-teal-700">+{{ $stockIns->sum('quantity') }}</td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </section>

            {{-- Stock Outs --}}
            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Stock Out Records</h3>
                    <p class="section-subtitle">{{ $dateFrom }} to {{ $dateTo }} · {{ $stockOuts->count() }} record(s)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Reason</th>
                                <th>Transferred To</th>
                                <th>Notes</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stockOuts as $item)
                                @php
                                    $reasonColors = [
                                        'Damaged'     => 'bg-red-100 text-red-700',
                                        'Expired'     => 'bg-amber-100 text-amber-800',
                                        'Transferred' => 'bg-blue-100 text-blue-700',
                                    ];
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600">{{ $item->date->format('M d, Y') }}</td>
                                    <td class="font-semibold text-gray-950">{{ $item->product_name }}</td>
                                    <td class="font-semibold">{{ $item->quantity }}</td>
                                    <td>
                                        <span class="badge {{ $reasonColors[$item->reason] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $item->reason }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        @if ($item->reason === 'Transferred' && $item->transfer_destination)
                                            <div class="font-medium text-gray-900">{{ $item->transfer_destination }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->transfer_address }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-sm text-gray-600">{{ $item->notes ?? '—' }}</td>
                                    <td class="text-sm text-gray-700">{{ $item->recorder?->name ?? 'Unknown' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-gray-500">No stock out records for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Sales --}}
            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Sales Transactions</h3>
                    <p class="section-subtitle">{{ $dateFrom }} to {{ $dateTo }} · {{ $sales->count() }} transaction(s)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Receipt No.</th>
                                <th>Payment</th>
                                <th>Cashier</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $sale)
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600">{{ optional($sale->sold_at)->format('M d, Y h:i A') }}</td>
                                    <td class="font-semibold text-gray-950">{{ $sale->receipt_number ?? $sale->reference_number }}</td>
                                    <td>
                                        <span class="badge bg-blue-100 text-blue-700">{{ ucfirst($sale->payment_method ?? 'N/A') }}</span>
                                    </td>
                                    <td class="text-sm text-gray-700">{{ $sale->creator?->name ?? 'Unknown' }}</td>
                                    <td class="text-right font-semibold text-gray-950">₱{{ number_format((float) $sale->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-sm text-gray-500">No sales transactions for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($sales->isNotEmpty())
                    <div class="flex justify-end border-t border-gray-100 px-6 py-4">
                        <p class="text-sm font-semibold text-gray-950">
                            Period Total: <span class="text-emerald-700">₱{{ number_format($summary['salesTotal'], 2) }}</span>
                        </p>
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
