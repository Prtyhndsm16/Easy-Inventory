<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Inventory</p>
                <h2 class="section-title">Stock Out</h2>
                <p class="section-subtitle">Record items removed from inventory — damaged, expired, or transferred.</p>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">

            {{-- Flash Message --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Record Stock Out Form --}}
            <section class="panel">
                <div class="panel-header border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-950">Record Stock Out</h3>
                    <p class="section-subtitle">Fill in the details of the items being removed from stock.</p>
                </div>

                <form method="POST" action="{{ route('admin.stock-out.store') }}" class="grid gap-5 p-4 sm:p-6 sm:grid-cols-2 lg:grid-cols-3">
                    @csrf

                    {{-- Barcode / QR Scanner --}}
                    <div class="form-field sm:col-span-2 lg:col-span-3">
                        <label for="stockout-barcode" class="block text-sm font-semibold text-gray-700">Scan Barcode / QR Code</label>
                        <div class="mt-1 grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                            <input
                                id="stockout-barcode"
                                type="text"
                                placeholder="Scan or type barcode / QR code"
                                autocomplete="off"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                            <button type="button" id="stockout-lookup-btn" class="btn-primary self-end">
                                Find Product
                            </button>
                            <button type="button" id="stockout-start-scanner" class="btn-muted self-end">
                                Open Camera
                            </button>
                        </div>

                        {{-- Scan feedback message --}}
                        <p id="stockout-scan-msg" class="mt-2 hidden rounded-lg border px-3 py-2 text-sm"></p>

                        {{-- Camera panel --}}
                        <div id="stockout-scanner-panel" class="mt-3 hidden rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Camera Scanner</p>
                                    <p id="stockout-scanner-status" class="mt-1 text-sm text-gray-500">Point the camera at the product barcode or QR code.</p>
                                </div>
                                <button type="button" id="stockout-stop-scanner" class="rounded-md px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 hover:text-gray-900">
                                    Close
                                </button>
                            </div>

                            <div class="relative mt-4 mx-auto aspect-video max-h-72 w-full overflow-hidden rounded-xl border border-gray-900/10 bg-gray-950 shadow-inner">
                                <video id="stockout-scanner-video" class="h-full w-full object-cover" muted playsinline></video>
                                <div class="pointer-events-none absolute inset-x-16 top-1/2 h-28 -translate-y-1/2 rounded-lg border-2 border-white/80 shadow-[0_0_0_999px_rgba(0,0,0,0.20)]"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Product --}}
                    <div class="form-field lg:col-span-1">
                        <label for="product_id" class="block text-sm font-semibold text-gray-700">
                            Product <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="product_id"
                            name="product_id"
                            required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('product_id') border-red-400 @enderror"
                        >
                            <option value="">— Select a product —</option>
                            @foreach ($products as $product)
                                <option
                                    value="{{ $product->product_id }}"
                                    data-barcode="{{ $product->barcode }}"
                                    @selected(old('product_id') == $product->product_id)
                                >
                                    {{ $product->product_name }} (Stock: {{ $product->stock }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Quantity --}}
                    <div class="form-field">
                        <label for="quantity" class="block text-sm font-semibold text-gray-700">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="quantity"
                            name="quantity"
                            type="number"
                            min="1"
                            value="{{ old('quantity', 1) }}"
                            required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('quantity') border-red-400 @enderror"
                        >
                        @error('quantity')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Reason --}}
                    <div class="form-field">
                        <label for="reason" class="block text-sm font-semibold text-gray-700">
                            Reason <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="reason"
                            name="reason"
                            required
                            onchange="toggleTransferFields(this.value)"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('reason') border-red-400 @enderror"
                        >
                            <option value="">— Select reason —</option>
                            @foreach ($reasons as $r)
                                <option value="{{ $r }}" @selected(old('reason') === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('reason')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Date --}}
                    <div class="form-field">
                        <label for="date" class="block text-sm font-semibold text-gray-700">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="date"
                            name="date"
                            type="date"
                            value="{{ old('date', now()->toDateString()) }}"
                            max="{{ now()->toDateString() }}"
                            required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('date') border-red-400 @enderror"
                        >
                        @error('date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recorded By (auto) --}}
                    <div class="form-field">
                        <label class="block text-sm font-semibold text-gray-700">Recorded By</label>
                        <input
                            type="text"
                            value="{{ Auth::user()->name }}"
                            disabled
                            class="block w-full rounded-lg border-gray-200 bg-gray-50 text-gray-500 shadow-sm cursor-not-allowed"
                        >
                    </div>

                    {{-- Transfer Fields (shown only when reason = Transferred) --}}
                    <div id="transfer-fields" class="contents" style="display:none">
                        <div class="form-field">
                            <label for="transfer_destination" class="block text-sm font-semibold text-gray-700">
                                Store / Bodega Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="transfer_destination"
                                name="transfer_destination"
                                type="text"
                                value="{{ old('transfer_destination') }}"
                                placeholder="e.g. Main Warehouse, Branch 2"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('transfer_destination') border-red-400 @enderror"
                            >
                            @error('transfer_destination')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-field sm:col-span-2">
                            <label for="transfer_address" class="block text-sm font-semibold text-gray-700">
                                Complete Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="transfer_address"
                                name="transfer_address"
                                type="text"
                                value="{{ old('transfer_address') }}"
                                placeholder="e.g. 123 Rizal St., Brgy. San Isidro, Makati City"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('transfer_address') border-red-400 @enderror"
                            >
                            @error('transfer_address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="form-field sm:col-span-2 lg:col-span-3">
                        <label for="notes" class="block text-sm font-semibold text-gray-700">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="2"
                            placeholder="Any additional details..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="sm:col-span-2 lg:col-span-3">
                        <button type="submit" class="btn-primary">
                            Record Stock Out
                        </button>
                    </div>
                </form>
            </section>

            {{-- JS: barcode lookup + transfer fields toggle --}}
            <script>
                /* ── Transfer fields show/hide ── */
                function toggleTransferFields(value) {
                    const section = document.getElementById('transfer-fields');
                    const inputs  = section.querySelectorAll('input');
                    const show    = value === 'Transferred';

                    section.style.display = show ? 'contents' : 'none';
                    inputs.forEach(function(input) {
                        input.required = show;
                    });
                }

                /* ── Barcode product lookup ── */
                function showScanMsg(text, isError) {
                    const el = document.getElementById('stockout-scan-msg');
                    el.textContent = text;
                    el.className = 'mt-2 rounded-lg border px-3 py-2 text-sm ' +
                        (isError
                            ? 'border-red-200 bg-red-50 text-red-700'
                            : 'border-emerald-200 bg-emerald-50 text-emerald-700');
                    el.classList.remove('hidden');
                }

                function lookupBarcode(code) {
                    const trimmed = (code || '').trim();

                    if (!trimmed) {
                        showScanMsg('Enter or scan a barcode first.', true);
                        return;
                    }

                    const select  = document.getElementById('product_id');
                    const options = Array.from(select.options);

                    // Try matching by data-barcode attribute first, then by product_id value
                    const match = options.find(function(opt) {
                        return opt.dataset.barcode === trimmed || opt.value === trimmed;
                    });

                    if (match) {
                        select.value = match.value;
                        showScanMsg('✓ Product found: ' + match.text, false);
                        document.getElementById('stockout-barcode').value = '';
                        document.getElementById('quantity').focus();
                    } else {
                        showScanMsg('No product found for barcode: ' + trimmed, true);
                    }
                }

                document.addEventListener('DOMContentLoaded', function () {
                    // Restore transfer fields state after validation failure
                    const reasonSelect = document.getElementById('reason');
                    if (reasonSelect) toggleTransferFields(reasonSelect.value);

                    // Manual "Find Product" button
                    const lookupBtn     = document.getElementById('stockout-lookup-btn');
                    const barcodeInput  = document.getElementById('stockout-barcode');

                    if (lookupBtn && barcodeInput) {
                        lookupBtn.addEventListener('click', function() {
                            lookupBarcode(barcodeInput.value);
                        });

                        // Also trigger on Enter key in the barcode field
                        barcodeInput.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                lookupBarcode(barcodeInput.value);
                            }
                        });
                    }

                    // Listen for camera scan event from initStockOutScanner()
                    window.addEventListener('stockout:barcode-scanned', function(e) {
                        lookupBarcode(e.detail.code);
                    });
                });
            </script>


            {{-- Filter --}}
            <section class="panel">
                <form method="GET" action="{{ route('admin.stock-out.index') }}" class="grid gap-4 p-4 sm:p-6 sm:grid-cols-[1fr_200px_auto]">
                    <div class="form-field">
                        <label for="search" class="block text-sm font-semibold text-gray-700">Search</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Product name or notes..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="form-field">
                        <label for="filter_reason" class="block text-sm font-semibold text-gray-700">Reason</label>
                        <select id="filter_reason" name="reason" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All reasons</option>
                            @foreach ($reasons as $r)
                                <option value="{{ $r }}" @selected($reason === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">Apply</button>
                        @if ($search !== '' || $reason !== '')
                            <a href="{{ route('admin.stock-out.index') }}" class="btn-muted">Reset</a>
                        @endif
                    </div>
                </form>
            </section>

            {{-- Table --}}
            <section class="table-shell">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Stock Out Records</h3>
                    <p class="section-subtitle">{{ $stockOuts->total() }} record{{ $stockOuts->total() === 1 ? '' : 's' }} found.</p>
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stockOuts as $item)
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600">
                                        {{ $item->date->format('M d, Y') }}
                                    </td>
                                    <td class="font-semibold text-gray-950">
                                        {{ $item->product_name }}
                                    </td>
                                    <td class="font-semibold text-gray-950">
                                        {{ $item->quantity }}
                                    </td>
                                    <td>
                                        @php
                                            $reasonColors = [
                                                'Damaged'     => 'bg-red-100 text-red-700',
                                                'Expired'     => 'bg-amber-100 text-amber-800',
                                                'Transferred' => 'bg-blue-100 text-blue-700',
                                            ];
                                        @endphp
                                        <span class="badge {{ $reasonColors[$item->reason] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $item->reason }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        @if ($item->reason === 'Transferred' && $item->transfer_destination)
                                            <div class="font-semibold text-gray-900">{{ $item->transfer_destination }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->transfer_address ?? '—' }}</div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs text-sm text-gray-600">
                                        {{ $item->notes ?? '—' }}
                                    </td>
                                    <td class="text-sm text-gray-700">
                                        {{ $item->recorder?->name ?? 'Unknown' }}
                                    </td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('admin.stock-out.destroy', $item) }}" onsubmit="return confirm('Delete this record? The stock will be restored.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center">
                                        <p class="font-semibold text-gray-900">No stock out records yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">Use the form above to record items leaving your inventory.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($stockOuts->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $stockOuts->links() }}
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
