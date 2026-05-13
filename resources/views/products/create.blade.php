<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="section-kicker">Admin Inventory</p>
                <h2 class="section-title">
                    Add Product
                </h2>
                <p class="section-subtitle">Create a product record with stock, pricing, supplier, and barcode details.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn-muted">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container-narrow">
            <section class="panel">
                <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-6">
                    @csrf

                    <div class="panel-body">
                    @include('products.partials.form-fields', ['product' => $product])
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-5 py-4 sm:px-6">
                        <a href="{{ route('admin.products.index') }}" class="btn-muted">
                            Cancel
                        </a>
                        <button type="submit" class="btn-primary">
                            Save Product
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
