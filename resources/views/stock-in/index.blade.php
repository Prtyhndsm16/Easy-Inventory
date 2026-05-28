<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Inventory</p>
                <h2 class="section-title">Stock In / Receiving</h2>
                <p class="section-subtitle">Scan a barcode to receive stock. Unknown products can be registered here directly.</p>
            </div>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container space-y-6">

            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Please fix the following errors:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <section class="panel">
                <div class="panel-header border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-950">Record Stock In</h3>
                    <p class="section-subtitle">Scan a barcode — existing products are auto-selected; new ones can be registered on the spot.</p>
                </div>

                {{-- Scanner UI --}}
                <div class="border-b border-gray-100 px-4 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <label for="barcode-input-in" class="block text-sm font-semibold text-gray-700">Scan Barcode / QR Code</label>
                            <input id="barcode-input-in" type="text" autocomplete="off"
                                   placeholder="Scan or type barcode then press Enter"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <button id="scan-camera-btn-in" type="button"
                                class="btn-muted flex items-center gap-2 whitespace-nowrap">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 4a1 1 0 011-1h3M3 4v3M3 16a1 1 0 001 1h3M17 16a1 1 0 01-1 1h-3M17 4a1 1 0 00-1-1h-3M17 4v3M3 10h14"/>
                            </svg>
                            Use Camera
                        </button>
                    </div>
                    <div id="camera-container-in" class="mt-3 hidden">
                        <video id="camera-preview-in" class="w-full max-w-sm rounded-lg border border-gray-200" autoplay muted playsinline></video>
                        <p class="mt-2 text-xs text-gray-500">Point at barcode/QR code. Detection is automatic.</p>
                        <button id="stop-camera-btn-in" type="button" class="mt-2 text-sm font-semibold text-red-600 hover:text-red-700">Stop Camera</button>
                    </div>
                    <p id="scanner-status-in" class="mt-2 text-xs text-gray-400"></p>
                </div>

                <form method="POST" action="{{ route('admin.stock-in.store') }}"
                      class="p-4 sm:p-6 space-y-6" id="stock-in-form">
                    @csrf
                    {{-- Hidden flag for mode --}}
                    <input type="hidden" name="mode" id="stock-in-mode" value="{{ old('mode', 'existing') }}">

                    {{-- ── EXISTING PRODUCT section ───────────────────────────────────────── --}}
                    <div id="existing-product-section">
                        <div class="form-field">
                            <label for="product_id_in" class="block text-sm font-semibold text-gray-700">
                                Product <span class="text-red-500">*</span>
                            </label>
                            <select id="product_id_in" name="product_id"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('product_id') border-red-500 @enderror">
                                <option value="">— Select a product —</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->product_id }}"
                                            data-barcode="{{ $product->barcode }}"
                                            data-stock="{{ $product->stock }}"
                                            @selected(old('product_id') == $product->product_id)>
                                        {{ $product->product_name }}
                                        ({{ $product->barcode ?? 'no barcode' }})
                                        — {{ $product->stock }} in stock
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="mt-3 text-xs text-gray-500">
                            Product not in the list?
                            <button type="button" id="switch-to-new-btn"
                                    class="font-semibold text-emerald-700 hover:underline">
                                Register it as a new product →
                            </button>
                        </p>
                    </div>

                    {{-- ── NEW PRODUCT section ─────────────────────────────────────────────── --}}
                    <div id="new-product-section" class="{{ old('mode') === 'new' ? '' : 'hidden' }}">
                        <div class="rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 px-4 py-5 sm:px-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-amber-900">📦 New Product Registration</h4>
                                    <p class="text-xs text-amber-700 mt-0.5">This product is not yet in the system. Fill in the details below — it will be created and stocked in one step.</p>
                                </div>
                                <button type="button" id="switch-to-existing-btn"
                                        class="text-xs font-semibold text-amber-700 hover:underline whitespace-nowrap">
                                    ← Back to existing
                                </button>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="form-field sm:col-span-2">
                                    <label for="new_product_name" class="block text-sm font-semibold text-gray-700">
                                        Product Name <span class="text-red-500">*</span>
                                    </label>
                                    <input id="new_product_name" name="new_product_name" type="text"
                                           value="{{ old('new_product_name') }}"
                                           placeholder="e.g. Coca-Cola 1.5L"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('new_product_name') border-red-500 @enderror">
                                    @error('new_product_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-field">
                                    <label for="new_product_price" class="block text-sm font-semibold text-gray-700">
                                        Selling Price (₱) <span class="text-red-500">*</span>
                                    </label>
                                    <input id="new_product_price" name="new_product_price" type="number"
                                           min="0" step="0.01" value="{{ old('new_product_price') }}"
                                           placeholder="0.00"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('new_product_price') border-red-500 @enderror">
                                    @error('new_product_price')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-field">
                                    <label for="new_product_category" class="block text-sm font-semibold text-gray-700">Category</label>
                                    <input id="new_product_category" name="new_product_category" type="text"
                                           value="{{ old('new_product_category') }}"
                                           placeholder="e.g. Beverages"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>

                                <div class="form-field">
                                    <label for="new_product_barcode" class="block text-sm font-semibold text-gray-700">Barcode</label>
                                    <input id="new_product_barcode" name="new_product_barcode" type="text"
                                           value="{{ old('new_product_barcode') }}"
                                           placeholder="Auto-filled from scan"
                                           class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <p class="mt-1 text-xs text-gray-400">Auto-filled when scanned. Edit if needed.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Shared fields (both modes) ─────────────────────────────────────── --}}
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="form-field">
                            <label for="quantity_in" class="block text-sm font-semibold text-gray-700">
                                Quantity Received <span class="text-red-500">*</span>
                            </label>
                            <input id="quantity_in" name="quantity" type="number" min="1"
                                   value="{{ old('quantity') }}" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('quantity') border-red-500 @enderror">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="supplier_in" class="block text-sm font-semibold text-gray-700">Supplier</label>
                            <input id="supplier_in" name="supplier" type="text" value="{{ old('supplier') }}"
                                   placeholder="e.g. ABC Trading"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="form-field">
                            <label for="reference_no_in" class="block text-sm font-semibold text-gray-700">Reference No.</label>
                            <input id="reference_no_in" name="reference_no" type="text" value="{{ old('reference_no') }}"
                                   placeholder="e.g. DR-2026-001"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="form-field">
                            <label for="date_in" class="block text-sm font-semibold text-gray-700">
                                Date Received <span class="text-red-500">*</span>
                            </label>
                            <input id="date_in" name="date" type="date" required
                                   value="{{ old('date', now()->toDateString()) }}"
                                   max="{{ now()->toDateString() }}"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-field sm:col-span-2">
                            <label for="notes_in" class="block text-sm font-semibold text-gray-700">Notes</label>
                            <input id="notes_in" name="notes" type="text" value="{{ old('notes') }}"
                                   placeholder="Optional remarks"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary" id="submit-stock-in">Save Stock In</button>
                    </div>
                </form>
            </section>

            {{-- Records Table --}}
            <section class="table-shell">
                <div class="panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950">Stock In Records</h3>
                        <p class="section-subtitle">{{ $stockIns->total() }} record(s) found.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.stock-in.index') }}" class="flex gap-2">
                        <input name="search" type="text" value="{{ $search }}" placeholder="Search product, supplier…"
                               class="block rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <button type="submit" class="btn-muted text-sm">Search</button>
                        @if ($search) <a href="{{ route('admin.stock-in.index') }}" class="btn-muted text-sm">Clear</a> @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Qty Added</th>
                                <th>Supplier</th>
                                <th>Reference No.</th>
                                <th>Notes</th>
                                <th>Received By</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stockIns as $item)
                                <tr>
                                    <td class="whitespace-nowrap text-gray-600">{{ $item->date->format('M d, Y') }}</td>
                                    <td>
                                        <div class="font-semibold text-gray-950">{{ $item->product_name }}</div>
                                        @if ($item->product)
                                            <div class="text-xs text-gray-400">{{ $item->product->barcode ?? 'no barcode' }}</div>
                                        @else
                                            <div class="text-xs text-amber-600">⚠ Product may have been deleted</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-emerald-100 text-emerald-700">+{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-gray-600">{{ $item->supplier ?? '—' }}</td>
                                    <td class="text-sm text-gray-600">{{ $item->reference_no ?? '—' }}</td>
                                    <td class="text-sm text-gray-600">{{ $item->notes ?? '—' }}</td>
                                    <td class="text-sm text-gray-700">{{ $item->receiver?->name ?? 'Unknown' }}</td>
                                    <td>
                                        <div class="flex justify-end">
                                            <form method="POST" action="{{ route('admin.stock-in.destroy', $item) }}"
                                                  onsubmit="return confirm('Delete this record? This will reverse the stock addition.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center">
                                        <p class="font-semibold text-gray-900">No stock in records yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">Use the form above to record a delivery or stock receipt.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($stockIns->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $stockIns->links() }}
                    </div>
                @endif
            </section>

        </div>
    </div>

    <script>
    (function () {
        const existingSection  = document.getElementById('existing-product-section');
        const newSection       = document.getElementById('new-product-section');
        const modeInput        = document.getElementById('stock-in-mode');
        const productSelect    = document.getElementById('product_id_in');
        const switchToNew      = document.getElementById('switch-to-new-btn');
        const switchToExisting = document.getElementById('switch-to-existing-btn');
        const submitBtn        = document.getElementById('submit-stock-in');
        const barcodeInput     = document.getElementById('barcode-input-in');
        const newBarcodeInput  = document.getElementById('new_product_barcode');
        const statusEl         = document.getElementById('scanner-status-in');

        function setMode(mode) {
            modeInput.value = mode;
            if (mode === 'new') {
                existingSection.classList.add('hidden');
                newSection.classList.remove('hidden');
                // Product dropdown no longer required in new mode
                productSelect.removeAttribute('required');
                submitBtn.textContent = 'Register Product & Save Stock In';
            } else {
                existingSection.classList.remove('hidden');
                newSection.classList.add('hidden');
                productSelect.setAttribute('required', '');
                submitBtn.textContent = 'Save Stock In';
            }
        }

        if (switchToNew)      switchToNew.addEventListener('click',      () => setMode('new'));
        if (switchToExisting) switchToExisting.addEventListener('click', () => setMode('existing'));

        // Expose to scanner
        window.stockInMatchBarcode = function (code) {
            const options = Array.from(productSelect.options);
            const match   = options.find(o => o.dataset.barcode === code);

            if (match) {
                // ── Existing product found ──
                setMode('existing');
                productSelect.value = match.value;
                const name = match.text.split('(')[0].trim();
                if (statusEl) {
                    statusEl.textContent = `✓ Found: ${name}`;
                    statusEl.className   = 'mt-2 text-xs text-emerald-600 font-semibold';
                }
                const qtyInput = document.getElementById('quantity_in');
                if (qtyInput) qtyInput.focus();
            } else {
                // ── No match → switch to new product mode ──
                setMode('new');
                if (newBarcodeInput) newBarcodeInput.value = code;
                if (barcodeInput)    barcodeInput.value    = code;
                if (statusEl) {
                    statusEl.textContent = `⚠ Barcode "${code}" not found — fill in the new product details below.`;
                    statusEl.className   = 'mt-2 text-xs text-amber-700 font-semibold';
                }
                const nameInput = document.getElementById('new_product_name');
                if (nameInput) nameInput.focus();
                // Scroll new section into view
                newSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };

        // Manual barcode input (Enter key)
        if (barcodeInput) {
            barcodeInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.stockInMatchBarcode(barcodeInput.value.trim());
                }
            });
        }

        // Restore mode on validation error
        const savedMode = modeInput.value;
        if (savedMode === 'new') setMode('new');
    })();
    </script>
</x-app-layout>
