<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="section-kicker">Receipt</p>
                <h2 class="section-title">{{ $sale->receipt_number ?? $sale->reference_number }}</h2>
                <p class="section-subtitle">Saved transaction record for {{ optional($sale->sold_at)->format('F d, Y h:i A') }}.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="button" onclick="window.print()" class="btn-muted w-full sm:w-auto">Print Receipt</button>
                <a href="{{ route('cashiering.index') }}" class="btn-primary w-full sm:w-auto">New Sale</a>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="mx-auto w-full max-w-3xl px-3 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="panel overflow-hidden">
                <div class="border-b border-gray-100 px-5 py-6 text-center sm:px-8">
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Easy Inventory & Sales Manager</p>
                    <h3 class="mt-2 text-2xl font-semibold text-gray-950">Sales Receipt</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $sale->receipt_number ?? $sale->reference_number }}</p>
                </div>

                <div class="grid gap-4 border-b border-gray-100 px-5 py-5 text-sm sm:grid-cols-2 sm:px-8">
                    <div>
                        <p class="font-semibold text-gray-950">Customer</p>
                        <p class="mt-1 text-gray-600">{{ $sale->customer_name ?: 'Walk-in customer' }}</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="font-semibold text-gray-950">Cashier</p>
                        <p class="mt-1 text-gray-600">
                            {{ $sale->creator?->name ?? 'Unknown user' }}
                            @if ($sale->created_by)
                                <span class="text-gray-400">#{{ $sale->created_by }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-950">Date</p>
                        <p class="mt-1 text-gray-600">{{ optional($sale->sold_at)->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="font-semibold text-gray-950">Payment</p>
                        <p class="mt-1 text-gray-600">{{ ucfirst((string) $sale->payment_method) }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-5 py-3 sm:px-8">Item</th>
                                <th class="px-5 py-3 text-right sm:px-8">Qty</th>
                                <th class="px-5 py-3 text-right sm:px-8">Price</th>
                                <th class="px-5 py-3 text-right sm:px-8">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="px-5 py-4 sm:px-8">
                                        <p class="font-semibold text-gray-950">{{ $item->product_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->barcode ?? 'No barcode' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right text-gray-600 sm:px-8">{{ number_format($item->quantity) }}</td>
                                    <td class="px-5 py-4 text-right text-gray-600 sm:px-8">PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="px-5 py-4 text-right font-semibold text-gray-950 sm:px-8">PHP {{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-5 py-5 sm:px-8">
                    <div class="ml-auto max-w-sm space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-semibold text-gray-950">PHP {{ number_format((float) $sale->subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Discount</span>
                            <span class="font-semibold text-gray-950">PHP {{ number_format((float) $sale->discount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-lg">
                            <span class="font-semibold text-gray-950">Total</span>
                            <span class="font-semibold text-gray-950">PHP {{ number_format((float) $sale->total_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Amount Paid</span>
                            <span class="font-semibold text-gray-950">PHP {{ number_format((float) $sale->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Change</span>
                            <span class="font-semibold text-emerald-700">PHP {{ number_format((float) $sale->change_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            window.localStorage.removeItem('cashiering.cart');
        </script>
    @endpush
</x-app-layout>
