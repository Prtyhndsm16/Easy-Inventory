<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashieringTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_lookup_product_by_barcode(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::create([
            'product_name' => 'Chocolate Bar',
            'category' => 'Snacks',
            'price' => 45,
            'stock' => 12,
            'supplier' => 'Snack Supply',
            'barcode' => 'CHOCO-001',
            'date_added' => now(),
        ]);

        $this->actingAs($staff)
            ->getJson(route('cashiering.lookup', ['code' => 'CHOCO-001']))
            ->assertOk()
            ->assertJsonPath('product.id', $product->getKey())
            ->assertJsonPath('product.name', 'Chocolate Bar');
    }

    public function test_cashier_checkout_saves_receipt_and_deducts_stock(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::create([
            'product_name' => 'Iced Tea',
            'category' => 'Drinks',
            'price' => 35,
            'stock' => 10,
            'supplier' => 'Drink Supply',
            'barcode' => 'TEA-001',
            'date_added' => now(),
        ]);

        $response = $this->actingAs($staff)->post(route('cashiering.checkout'), [
            'customer_name' => 'Maria Santos',
            'payment_method' => 'cash',
            'amount_paid' => 100,
            'items' => [
                [
                    'product_id' => $product->getKey(),
                    'quantity' => 2,
                ],
            ],
        ]);

        $sale = Sale::with('items')->firstOrFail();

        $response->assertRedirect(route('cashiering.receipts.show', $sale, absolute: false));
        $this->assertSame('Maria Santos', $sale->customer_name);
        $this->assertSame($staff->id, $sale->created_by);
        $this->assertNotEmpty($sale->receipt_number);
        $this->assertEquals(70.00, (float) $sale->total_amount);
        $this->assertEquals(30.00, (float) $sale->change_amount);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(1, $sale->items->count());
        $this->assertSame('Iced Tea', $sale->items->first()->product_name);

        $this->actingAs($staff)
            ->get(route('cashiering.receipts.show', $sale))
            ->assertOk()
            ->assertSee($sale->receipt_number)
            ->assertSee('Maria Santos')
            ->assertSee($staff->name)
            ->assertSee('Iced Tea');
    }

    public function test_checkout_rejects_insufficient_payment(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::create([
            'product_name' => 'Rice Pack',
            'category' => 'Grocery',
            'price' => 90,
            'stock' => 5,
            'supplier' => 'Grocery Supply',
            'barcode' => 'RICE-001',
            'date_added' => now(),
        ]);

        $this->actingAs($staff)
            ->from(route('cashiering.index'))
            ->post(route('cashiering.checkout'), [
                'payment_method' => 'cash',
                'amount_paid' => 50,
                'items' => [
                    [
                        'product_id' => $product->getKey(),
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('cashiering.index', absolute: false))
            ->assertSessionHasErrors('amount_paid');

        $this->assertSame(0, Sale::count());
        $this->assertSame(5, $product->fresh()->stock);
    }
}
