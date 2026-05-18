@php
    $selectedDate = old('date_added', optional($product->date_added)->format('Y-m-d') ?? $product->date_added);
@endphp

<div class="space-y-8">
    <section>
        <div class="mb-5 border-b border-gray-100 pb-4">
            <h3 class="text-base font-semibold text-gray-950">Product Details</h3>
            <p class="mt-1 text-sm text-gray-500">Use a clear product name and optional barcode for faster searching later.</p>
        </div>

        <div class="form-grid">
            <div class="form-field md:col-span-2">
                <x-input-label for="product_image" :value="__('Product Image')" />
                <div class="grid gap-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 sm:grid-cols-[7rem_1fr] sm:items-center">
                    <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-white">
                        @if ($product->imageUrl())
                            <img
                                src="{{ $product->imageUrl() }}"
                                alt="{{ $product->product_name }} image"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <span class="px-3 text-center text-xs font-semibold uppercase text-gray-400">No image</span>
                        @endif
                    </div>

                    <div>
                        <input
                            id="product_image"
                            name="product_image"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700"
                        >
                        <p class="form-hint mt-2">Upload JPG, PNG, or WebP image up to 5MB.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('product_image')" />
                    </div>
                </div>
            </div>

            <div class="form-field md:col-span-2">
                <x-input-label for="product_name" :value="__('Product Name')" />
                <x-text-input
                    id="product_name"
                    name="product_name"
                    type="text"
                    class="block w-full"
                    :value="old('product_name', $product->product_name)"
                    placeholder="Example: Wireless Mouse"
                    required
                    autofocus
                    autocomplete="off"
                />
                <x-input-error class="mt-2" :messages="$errors->get('product_name')" />
            </div>

            <div class="form-field">
                <x-input-label for="category" :value="__('Category')" />
                <x-text-input
                    id="category"
                    name="category"
                    type="text"
                    class="block w-full"
                    :value="old('category', $product->category)"
                    placeholder="Example: Accessories"
                    autocomplete="off"
                />
                <p class="form-hint">Group similar products for easier filtering.</p>
                <x-input-error class="mt-2" :messages="$errors->get('category')" />
            </div>

            <div class="form-field md:col-span-2">
                <x-input-label for="barcode" :value="__('Barcode')" />
                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-text-input
                        id="barcode"
                        name="barcode"
                        type="text"
                        class="block w-full"
                        :value="old('barcode', $product->barcode)"
                        placeholder="Scan or type barcode"
                        inputmode="text"
                        autocomplete="off"
                    />
                    <button
                        type="button"
                        id="start-barcode-scanner"
                        class="btn-muted w-full shrink-0 sm:w-auto"
                    >
                        Scan Code
                    </button>
                </div>
                <p class="form-hint">Optional, but must be unique when provided. Scan with your phone camera for best results.</p>
                <x-input-error class="mt-2" :messages="$errors->get('barcode')" />

                <div id="barcode-scanner-panel" class="mt-4 hidden rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Camera Scanner</p>
                            <p id="barcode-scanner-status" class="mt-1 text-sm text-gray-500">Point the camera at a barcode or QR code.</p>
                        </div>
                        <button
                            type="button"
                            id="stop-barcode-scanner"
                            class="rounded-md px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                        >
                            Close
                        </button>
                    </div>

                    <div class="relative mt-4 mx-auto aspect-[3/4] max-h-[72vh] w-full overflow-hidden rounded-xl border border-gray-900/10 bg-gray-950 shadow-inner sm:aspect-video sm:max-h-none">
                        <video
                            id="barcode-scanner-video"
                            class="h-full w-full object-cover"
                            muted
                            playsinline
                        ></video>

                        <div class="pointer-events-none absolute inset-x-6 top-1/2 h-24 -translate-y-1/2 rounded-lg border-2 border-white/80 shadow-[0_0_0_999px_rgba(0,0,0,0.20)] sm:inset-x-16 sm:h-28"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="mb-5 border-b border-gray-100 pb-4">
            <h3 class="text-base font-semibold text-gray-950">Stock and Pricing</h3>
            <p class="mt-1 text-sm text-gray-500">These values drive dashboard totals and low-stock alerts.</p>
        </div>

        <div class="form-grid">
            <div class="form-field">
                <x-input-label for="price" :value="__('Selling Price (PHP)')" />
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-500">PHP</span>
                    <x-text-input
                        id="price"
                        name="price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="block w-full pl-12"
                        :value="old('price', $product->price)"
                        placeholder="0.00"
                        required
                    />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('price')" />
            </div>

            <div class="form-field">
                <x-input-label for="cost_price" :value="__('Cost Price (PHP)')" />
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-500">PHP</span>
                    <x-text-input
                        id="cost_price"
                        name="cost_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="block w-full pl-12"
                        :value="old('cost_price', $product->cost_price)"
                        placeholder="0.00"
                    />
                </div>
                <p class="form-hint">Optional. Used to compute profit margin.</p>
                @php
                    $margin = $product->profitMarginPercent();
                @endphp
                @if ($margin !== null)
                    <p class="mt-1 text-xs font-semibold {{ $margin >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        Current margin: {{ $margin >= 0 ? '+' : '' }}{{ $margin }}%
                    </p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('cost_price')" />
            </div>

            <div class="form-field">
                <x-input-label for="stock" :value="__('Stock Quantity')" />
                <x-text-input
                    id="stock"
                    name="stock"
                    type="number"
                    min="0"
                    class="block w-full"
                    :value="old('stock', $product->stock ?? 0)"
                    placeholder="0"
                    required
                />
                <p class="form-hint">Items at 10 stock or below will appear in alerts.</p>
                <x-input-error class="mt-2" :messages="$errors->get('stock')" />
            </div>

            <div class="form-field">
                <x-input-label for="supplier" :value="__('Supplier')" />
                <x-text-input
                    id="supplier"
                    name="supplier"
                    type="text"
                    class="block w-full"
                    :value="old('supplier', $product->supplier)"
                    placeholder="Example: ABC Trading"
                    autocomplete="off"
                />
                <x-input-error class="mt-2" :messages="$errors->get('supplier')" />
            </div>

            <div class="form-field">
                <x-input-label for="date_added" :value="__('Date Added')" />
                <x-text-input
                    id="date_added"
                    name="date_added"
                    type="date"
                    class="block w-full"
                    :value="$selectedDate"
                />
                <x-input-error class="mt-2" :messages="$errors->get('date_added')" />
            </div>
        </div>
    </section>
</div>
