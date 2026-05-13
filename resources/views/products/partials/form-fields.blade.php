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

            <div class="form-field">
                <x-input-label for="barcode" :value="__('Barcode')" />
                <x-text-input
                    id="barcode"
                    name="barcode"
                    type="text"
                    class="block w-full"
                    :value="old('barcode', $product->barcode)"
                    placeholder="Scan or type barcode"
                    inputmode="numeric"
                    autocomplete="off"
                />
                <p class="form-hint">Optional, but must be unique when provided.</p>
                <x-input-error class="mt-2" :messages="$errors->get('barcode')" />
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
                <x-input-label for="price" :value="__('Price')" />
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
