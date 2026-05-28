<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashierCheckoutRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Support\AuditLogger;
use App\Support\LowStockNotifier;
use App\Support\StockMovementLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashieringController extends Controller
{
    public function index(): View
    {
        return view('cashiering.index', [
            'starterProducts' => Product::query()
                ->where('stock', '>', 0)
                ->orderBy('product_name')
                ->limit(12)
                ->get()
                ->map(fn (Product $product): array => $this->productPayload($product))
                ->values(),
            'recentSales' => Sale::query()
                ->with('creator')
                ->latest('sold_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $search = Str::limit(trim((string) $request->string('search')), 100, '');

        $products = Product::query()
            ->where('stock', '>', 0)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('product_name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderBy('product_name')
            ->limit(12)
            ->get()
            ->map(fn (Product $product): array => $this->productPayload($product))
            ->values();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $code = Str::limit(trim((string) $request->string('code')), 100, '');

        if ($code === '') {
            return response()->json(['message' => 'Enter or scan a barcode first.'], 422);
        }

        $product = Product::query()
            ->where('stock', '>', 0)
            ->where(function ($query) use ($code): void {
                $query->where('barcode', $code);

                if (ctype_digit($code)) {
                    $query->orWhere('product_id', (int) $code);
                }
            })
            ->first();

        if (! $product) {
            return response()->json(['message' => 'No active product found for that barcode.'], 404);
        }

        return response()->json([
            'product' => $this->productPayload($product),
        ]);
    }

    public function checkout(CashierCheckoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $cartItems = collect($validated['items'])
            ->map(fn (array $item): array => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->groupBy('product_id')
            ->map(fn ($items, int $productId): array => [
                'product_id' => $productId,
                'quantity' => $items->sum('quantity'),
            ])
            ->values();

        $sale = DB::transaction(function () use ($cartItems, $validated, $request): Sale {
            $products = Product::query()
                ->whereIn('product_id', $cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $subtotal = 0;
            $saleLines = [];

            foreach ($cartItems as $item) {
                /** @var Product|null $product */
                $product = $products->get($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the cart products no longer exists.',
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "{$product->product_name} only has {$product->stock} stock left.",
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $item['quantity'], 2);
                $subtotal += $lineTotal;

                $saleLines[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $total = round($subtotal, 2);
            $amountPaid = round((float) $validated['amount_paid'], 2);

            if ($amountPaid < $total) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Amount paid is less than the total amount due.',
                ]);
            }

            $receiptNumber = $this->generateReceiptNumber();

            $sale = Sale::create([
                'sold_at' => now(),
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total_amount' => $total,
                'amount_paid' => $amountPaid,
                'change_amount' => round($amountPaid - $total, 2),
                'payment_method' => $validated['payment_method'],
                'reference_number' => $receiptNumber,
                'receipt_number' => $receiptNumber,
                'customer_name' => $validated['customer_name'] ?? null,
                'created_by' => $request->user()?->getKey(),
            ]);

            foreach ($saleLines as $line) {
                /** @var Product $product */
                $product = $line['product'];

                $beforeStock = $product->stock;

                $sale->items()->create([
                    'product_id'   => $product->getKey(),
                    'product_name' => $product->product_name,
                    'barcode'      => $product->barcode,
                    'quantity'     => $line['quantity'],
                    'unit_price'   => $line['unit_price'],
                    'line_total'   => $line['line_total'],
                ]);

                $product->decrement('stock', $line['quantity']);
                $afterStock = $product->fresh()->stock;

                StockMovementLogger::record(
                    productId:   $product->getKey(),
                    productName: $product->product_name,
                    type:        'sale',
                    quantity:    -$line['quantity'],
                    beforeStock: $beforeStock,
                    afterStock:  $afterStock,
                    reference:   $receiptNumber,
                    notes:       "Sold via cashiering — receipt {$receiptNumber}",
                );

                $stockChanges[] = [
                    'id'     => $product->getKey(),
                    'name'   => $product->product_name,
                    'before' => $beforeStock,
                    'after'  => $afterStock,
                ];
            }

            AuditLogger::record('sale.created', 'success', [
                'receipt_number' => $receiptNumber,
                'total_amount'   => $total,
                'items'          => count($saleLines),
            ], $sale);

            return $sale;
        });

        // Fire low-stock notifications outside the transaction
        LowStockNotifier::check($stockChanges ?? []);

        return redirect()
            ->route('cashiering.receipts.show', $sale)
            ->with('status', 'Payment completed and receipt saved.');
    }

    public function receipt(Sale $sale): View
    {
        return view('cashiering.receipt', [
            'sale' => $sale->load(['items', 'creator']),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->getKey(),
            'name' => $product->product_name,
            'barcode' => $product->barcode,
            'category' => $product->category,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'image_url' => $product->imageUrl(),
        ];
    }

    private function generateReceiptNumber(): string
    {
        do {
            $receiptNumber = 'RCPT-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (Sale::where('receipt_number', $receiptNumber)->exists());

        return $receiptNumber;
    }
}
