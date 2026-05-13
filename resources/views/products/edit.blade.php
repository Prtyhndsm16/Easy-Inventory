<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="section-kicker">Admin Inventory</p>
                <h2 class="section-title">
                    Edit Product
                </h2>
                <p class="section-subtitle">Update product details while keeping inventory records searchable.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn-muted w-full sm:w-auto">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="app-page">
        <div class="page-container-narrow">
            <section class="panel">
                <form id="delete-product-form" method="POST" action="{{ route('admin.products.destroy', $product) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="panel-body">
                    @include('products.partials.form-fields', ['product' => $product])
                    </div>

                    <div class="form-actions-between">
                        <button
                            type="submit"
                            form="delete-product-form"
                            onclick="return confirm('Delete this product?');"
                            class="btn-danger"
                        >
                            Delete Product
                        </button>

                        <div class="form-action-group">
                            <a href="{{ route('admin.products.index') }}" class="btn-muted">
                                Cancel
                            </a>
                            <button type="submit" class="btn-primary">
                                Update Product
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
