<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Admin Inventory</p>
                <h2 class="text-2xl font-semibold leading-tight text-gray-900">
                    Edit Product
                </h2>
            </div>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form id="delete-product-form" method="POST" action="{{ route('admin.products.destroy', $product) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('products.partials.form-fields', ['product' => $product])

                    <div class="flex items-center justify-between gap-3">
                        <button
                            type="submit"
                            form="delete-product-form"
                            onclick="return confirm('Delete this product?');"
                            class="inline-flex items-center rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                        >
                            Delete Product
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                Update Product
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
