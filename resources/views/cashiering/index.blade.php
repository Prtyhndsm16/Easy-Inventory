<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="section-kicker">Point of Sale</p>
                <h2 class="section-title">Cashiering</h2>
                <p class="section-subtitle">Scan, search, add to cart, collect payment, and save the receipt.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-muted w-full sm:w-auto">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div
        class="app-page"
        x-data="cashieringTerminal({
            starterProducts: @js($starterProducts),
            routes: {
                products: @js(route('cashiering.products')),
                lookup: @js(route('cashiering.lookup')),
            },
        })"
        x-init="init()"
    >
        <div class="page-container space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Payment was not saved.</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_26rem]">
                <section class="space-y-6">
                    <div class="panel">
                        <div class="panel-header">
                            <h3 class="text-lg font-semibold text-gray-950">Scan or Type Barcode</h3>
                            <p class="section-subtitle">Use the camera scanner, or type the barcode / QR value manually.</p>
                        </div>

                        <div class="panel-body space-y-4">
                            <div class="grid gap-3 lg:grid-cols-[1fr_auto_auto]">
                                <div class="form-field">
                                    <label for="cashier-barcode" class="block text-sm font-semibold text-gray-700">Barcode or QR Code</label>
                                    <input
                                        id="cashier-barcode"
                                        type="text"
                                        x-model="barcode"
                                        x-on:keydown.enter.prevent="lookupBarcode()"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                        placeholder="Scan or enter code"
                                        autocomplete="off"
                                    >
                                </div>

                                <button type="button" x-on:click="lookupBarcode()" class="btn-primary self-end">
                                    Add Code
                                </button>

                                <button type="button" id="cashier-start-scanner" class="btn-muted self-end">
                                    Open Camera
                                </button>
                            </div>

                            <p x-show="message" x-text="message" class="rounded-lg border px-3 py-2 text-sm" :class="messageIsError ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'"></p>

                            <div id="cashier-scanner-panel" class="hidden rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Camera Scanner</p>
                                        <p id="cashier-scanner-status" class="mt-1 text-sm text-gray-500">Point the camera at the product barcode or QR code.</p>
                                    </div>
                                    <button
                                        type="button"
                                        id="cashier-stop-scanner"
                                        class="rounded-md px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        Close
                                    </button>
                                </div>

                                <div class="relative mt-4 mx-auto aspect-[3/4] max-h-[72vh] w-full overflow-hidden rounded-xl border border-gray-900/10 bg-gray-950 shadow-inner sm:aspect-video sm:max-h-none">
                                    <video
                                        id="cashier-scanner-video"
                                        class="h-full w-full object-cover"
                                        muted
                                        playsinline
                                    ></video>
                                    <div class="pointer-events-none absolute inset-x-6 top-1/2 h-24 -translate-y-1/2 rounded-lg border-2 border-white/80 shadow-[0_0_0_999px_rgba(0,0,0,0.20)] sm:inset-x-16 sm:h-28"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h3 class="text-lg font-semibold text-gray-950">Select Product</h3>
                            <p class="section-subtitle">Search by product name, category, or barcode.</p>
                        </div>

                        <div class="panel-body space-y-4">
                            <input
                                type="search"
                                x-model.debounce.350ms="search"
                                x-on:input.debounce.350ms="searchProducts()"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="Search products"
                            >

                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <template x-for="product in products" :key="product.id">
                                    <button
                                        type="button"
                                        x-on:click="addProduct(product)"
                                        class="rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                                <img x-show="product.image_url" :src="product.image_url" :alt="product.name" class="h-full w-full object-cover">
                                                <span x-show="!product.image_url" class="text-xs font-semibold text-gray-400">ITEM</span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-gray-950" x-text="product.name"></p>
                                                <p class="truncate text-xs text-gray-500" x-text="product.barcode || 'No barcode'"></p>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex items-center justify-between gap-3">
                                            <span class="font-semibold text-gray-950" x-text="money(product.price)"></span>
                                            <span class="badge bg-emerald-100 text-emerald-700"><span x-text="product.stock"></span>&nbsp;left</span>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            <div x-show="products.length === 0" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                                No products found.
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="panel xl:sticky xl:top-24 xl:self-start">
                    <div class="panel-header">
                        <h3 class="text-lg font-semibold text-gray-950">Cart</h3>
                        <p class="section-subtitle"><span x-text="cart.length"></span> product line<span x-show="cart.length !== 1">s</span></p>
                    </div>

                    <form method="POST" action="{{ route('cashiering.checkout') }}" x-on:submit="beforeCheckout($event)">
                        @csrf

                        <template x-for="(item, index) in cart" :key="item.id">
                            <div>
                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.id">
                                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                            </div>
                        </template>

                        <div class="max-h-[34rem] divide-y divide-gray-100 overflow-y-auto">
                            <template x-for="item in cart" :key="item.id">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-950" x-text="item.name"></p>
                                            <p class="text-sm text-gray-500" x-text="money(item.price)"></p>
                                        </div>
                                        <button type="button" x-on:click="removeItem(item.id)" class="rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Remove
                                        </button>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div class="inline-flex items-center rounded-lg border border-gray-200">
                                            <button type="button" x-on:click="decrement(item.id)" class="px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">-</button>
                                            <span class="min-w-10 px-3 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                            <button type="button" x-on:click="increment(item.id)" class="px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">+</button>
                                        </div>
                                        <p class="font-semibold text-gray-950" x-text="money(item.price * item.quantity)"></p>
                                    </div>
                                </div>
                            </template>

                            <div x-show="cart.length === 0" class="px-6 py-12 text-center text-sm text-gray-500">
                                Scan or select products to start a sale.
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-gray-100 px-4 py-5 sm:px-6">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Subtotal</span>
                                    <span class="font-semibold text-gray-950" x-text="money(subtotal)"></span>
                                </div>
                                <div class="flex items-center justify-between text-lg font-semibold">
                                    <span>Total</span>
                                    <span x-text="money(subtotal)"></span>
                                </div>
                            </div>

                            <div class="form-field">
                                <label for="customer_name" class="block text-sm font-semibold text-gray-700">Customer Name <span class="font-normal text-gray-400">(optional)</span></label>
                                <input id="customer_name" name="customer_name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Buyer name for receipt">
                            </div>

                            <div class="form-field">
                                <label for="payment_method" class="block text-sm font-semibold text-gray-700">Payment Method</label>
                                <select id="payment_method" name="payment_method" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
                                    <option value="card">Card</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-field">
                                <label for="amount_paid" class="block text-sm font-semibold text-gray-700">Amount Paid</label>
                                <input
                                    id="amount_paid"
                                    name="amount_paid"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    x-model.number="amountPaid"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="0.00"
                                    required
                                >
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Change</span>
                                    <span class="font-semibold" :class="change < 0 ? 'text-red-700' : 'text-emerald-700'" x-text="money(Math.max(change, 0))"></span>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary w-full" :disabled="cart.length === 0">
                                Proceed Payment
                            </button>
                        </div>
                    </form>
                </aside>
            </div>

            <section class="panel">
                <div class="panel-header">
                    <h3 class="text-lg font-semibold text-gray-950">Recent Receipts</h3>
                    <p class="section-subtitle">Latest cashiering transactions.</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($recentSales as $sale)
                        <a href="{{ route('cashiering.receipts.show', $sale) }}" class="flex flex-col gap-2 px-5 py-4 transition hover:bg-gray-50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <p class="font-semibold text-gray-950">{{ $sale->receipt_number ?? $sale->reference_number }}</p>
                                <p class="text-sm text-gray-500">{{ optional($sale->sold_at)->format('M d, Y h:i A') }} by {{ $sale->creator?->name ?? 'Unknown user' }}</p>
                            </div>
                            <p class="font-semibold text-gray-950">PHP {{ number_format((float) $sale->total_amount, 2) }}</p>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500">
                            No receipts yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            function cashieringTerminal(config) {
                return {
                    products: config.starterProducts || [],
                    routes: config.routes,
                    cart: [],
                    search: '',
                    barcode: '',
                    amountPaid: null,
                    message: '',
                    messageIsError: false,

                    get subtotal() {
                        return this.cart.reduce((total, item) => total + (Number(item.price) * Number(item.quantity)), 0);
                    },

                    get change() {
                        return Number(this.amountPaid || 0) - this.subtotal;
                    },

                    init() {
                        this.cart = JSON.parse(window.localStorage.getItem('cashiering.cart') || '[]');
                        window.addEventListener('cashiering:barcode-scanned', (event) => {
                            this.barcode = event.detail.code;
                            this.lookupBarcode();
                        });
                    },

                    money(value) {
                        return new Intl.NumberFormat('en-PH', {
                            style: 'currency',
                            currency: 'PHP',
                        }).format(Number(value || 0));
                    },

                    persistCart() {
                        window.localStorage.setItem('cashiering.cart', JSON.stringify(this.cart));
                    },

                    setMessage(message, isError = false) {
                        this.message = message;
                        this.messageIsError = isError;
                    },

                    async searchProducts() {
                        const url = new URL(this.routes.products);
                        url.searchParams.set('search', this.search);

                        const response = await fetch(url, {
                            headers: { Accept: 'application/json' },
                        });
                        const data = await response.json();
                        this.products = data.products || [];
                    },

                    async lookupBarcode() {
                        const code = this.barcode.trim();

                        if (!code) {
                            this.setMessage('Enter or scan a product barcode first.', true);
                            return;
                        }

                        const url = new URL(this.routes.lookup);
                        url.searchParams.set('code', code);

                        const response = await fetch(url, {
                            headers: { Accept: 'application/json' },
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            this.setMessage(data.message || 'Product was not found.', true);
                            return;
                        }

                        this.addProduct(data.product);
                        this.barcode = '';
                    },

                    addProduct(product) {
                        const existing = this.cart.find((item) => item.id === product.id);

                        if (existing) {
                            if (existing.quantity >= product.stock) {
                                this.setMessage(`${product.name} only has ${product.stock} stock left.`, true);
                                return;
                            }

                            existing.quantity += 1;
                        } else {
                            this.cart.push({ ...product, quantity: 1 });
                        }

                        this.persistCart();
                        this.setMessage(`${product.name} added to cart.`);
                    },

                    increment(productId) {
                        const item = this.cart.find((cartItem) => cartItem.id === productId);

                        if (!item) {
                            return;
                        }

                        if (item.quantity >= item.stock) {
                            this.setMessage(`${item.name} only has ${item.stock} stock left.`, true);
                            return;
                        }

                        item.quantity += 1;
                        this.persistCart();
                    },

                    decrement(productId) {
                        const item = this.cart.find((cartItem) => cartItem.id === productId);

                        if (!item) {
                            return;
                        }

                        item.quantity -= 1;

                        if (item.quantity <= 0) {
                            this.removeItem(productId);
                            return;
                        }

                        this.persistCart();
                    },

                    removeItem(productId) {
                        this.cart = this.cart.filter((item) => item.id !== productId);
                        this.persistCart();
                    },

                    beforeCheckout(event) {
                        if (this.cart.length === 0) {
                            event.preventDefault();
                            this.setMessage('Add at least one product before payment.', true);
                            return;
                        }

                        if (this.change < 0) {
                            event.preventDefault();
                            this.setMessage('Amount paid is less than the total.', true);
                        }
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
