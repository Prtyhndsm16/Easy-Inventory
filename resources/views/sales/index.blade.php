<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Admin</p>
                <h2 class="section-title">Sales History</h2>
                <p class="section-subtitle">Browse, search, and filter all sales transactions.</p>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">

            {{-- Filters --}}
            <section class="panel">
                <form method="GET" action="{{ route('admin.sales.index') }}"
                      class="grid gap-4 p-4 sm:p-6 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_200px_auto]">

                    <div class="form-field">
                        <label for="date_from" class="block text-sm font-semibold text-gray-700">From</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="form-field">
                        <label for="date_to" class="block text-sm font-semibold text-gray-700">To</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="form-field">
                        <label for="payment_method" class="block text-sm font-semibold text-gray-700">Payment Method</label>
                        <select id="payment_method" name="payment_method"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All Methods</option>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method }}" @selected($paymentMethod === $method)>{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="search" class="block text-sm font-semibold text-gray-700">Search</label>
                        <input id="search" name="search" type="text" value="{{ $search }}"
                               placeholder="Receipt no. or customer"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="filter-actions sm:col-span-2 lg:col-span-1">
                        <button type="submit" class="btn-primary">Apply</button>
                        <a href="{{ route('admin.sales.index') }}" class="btn-muted">Reset</a>
                    </div>
                </form>
            </section>

            {{-- Sales Table --}}
            <section class="table-shell">
                <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950">Transactions</h3>
                        <p class="section-subtitle">{{ $sales->total() }} transaction(s) · Period total: <span class="font-semibold text-emerald-700">₱{{ number_format($periodTotal, 2) }}</span></p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Receipt No.</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Cashier</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $sale)
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600">
                                        {{ optional($sale->sold_at)->format('M d, Y') }}<br>
                                        <span class="text-xs">{{ optional($sale->sold_at)->format('h:i A') }}</span>
                                    </td>
                                    <td class="font-semibold text-gray-950">{{ $sale->receipt_number ?? $sale->reference_number }}</td>
                                    <td class="text-gray-600">{{ $sale->customer_name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-blue-100 text-blue-700">{{ ucfirst($sale->payment_method ?? 'N/A') }}</span>
                                    </td>
                                    <td class="text-sm text-gray-700">{{ $sale->creator?->name ?? 'Unknown' }}</td>
                                    <td class="text-right font-semibold text-gray-950">₱{{ number_format((float) $sale->total_amount, 2) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('cashiering.receipts.show', $sale) }}"
                                           target="_blank"
                                           class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                            Receipt
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <p class="font-semibold text-gray-900">No transactions found.</p>
                                        <p class="mt-1 text-sm text-gray-500">Try adjusting your date range or filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($sales->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $sales->links() }}
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
