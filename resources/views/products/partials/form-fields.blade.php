@php
    $selectedDate = old('date_added', optional($product->date_added)->format('Y-m-d') ?? $product->date_added);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="product_name" :value="__('Product Name')" />
        <x-text-input id="product_name" name="product_name" type="text" class="mt-1 block w-full" :value="old('product_name', $product->product_name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('product_name')" />
    </div>

    <div>
        <x-input-label for="category" :value="__('Category')" />
        <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" :value="old('category', $product->category)" />
        <x-input-error class="mt-2" :messages="$errors->get('category')" />
    </div>

    <div>
        <x-input-label for="supplier" :value="__('Supplier')" />
        <x-text-input id="supplier" name="supplier" type="text" class="mt-1 block w-full" :value="old('supplier', $product->supplier)" />
        <x-input-error class="mt-2" :messages="$errors->get('supplier')" />
    </div>

    <div>
        <x-input-label for="price" :value="__('Price')" />
        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $product->price)" required />
        <x-input-error class="mt-2" :messages="$errors->get('price')" />
    </div>

    <div>
        <x-input-label for="stock" :value="__('Stock')" />
        <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', $product->stock ?? 0)" required />
        <x-input-error class="mt-2" :messages="$errors->get('stock')" />
    </div>

    <div>
        <x-input-label for="barcode" :value="__('Barcode')" />
        <x-text-input id="barcode" name="barcode" type="text" class="mt-1 block w-full" :value="old('barcode', $product->barcode)" />
        <x-input-error class="mt-2" :messages="$errors->get('barcode')" />
    </div>

    <div>
        <x-input-label for="date_added" :value="__('Date Added')" />
        <x-text-input id="date_added" name="date_added" type="date" class="mt-1 block w-full" :value="$selectedDate" />
        <x-input-error class="mt-2" :messages="$errors->get('date_added')" />
    </div>
</div>
