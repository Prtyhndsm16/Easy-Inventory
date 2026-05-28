<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_sales_summary_and_top_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $coffee = Product::create([
            'product_name' => 'Iced Coffee',
            'category' => 'Drinks',
            'price' => 125,
            'stock' => 30,
            'supplier' => 'Cafe Supply',
            'barcode' => 'COFFEE-001',
            'date_added' => now(),
        ]);
        $bread = Product::create([
            'product_name' => 'Banana Bread',
            'category' => 'Bakery',
            'price' => 85,
            'stock' => 20,
            'supplier' => 'Bakery Supply',
            'barcode' => 'BREAD-001',
            'date_added' => now(),
        ]);

        $todaySale = Sale::create([
            'sold_at' => now(),
            'subtotal' => 1250,
            'total_amount' => 1250,
            'payment_method' => 'cash',
            'created_by' => $admin->id,
        ]);
        $todaySale->items()->create([
            'product_id' => $coffee->getKey(),
            'product_name' => $coffee->product_name,
            'barcode' => $coffee->barcode,
            'quantity' => 10,
            'unit_price' => 125,
            'line_total' => 1250,
        ]);

        $yesterdaySale = Sale::create([
            'sold_at' => now()->subDay(),
            'subtotal' => 700,
            'total_amount' => 700,
            'payment_method' => 'cash',
            'created_by' => $admin->id,
        ]);
        $yesterdaySale->items()->create([
            'product_id' => $bread->getKey(),
            'product_name' => $bread->product_name,
            'barcode' => $bread->barcode,
            'quantity' => 5,
            'unit_price' => 140,
            'line_total' => 700,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Business Dashboard')
            ->assertSee('PHP 1,250.00')
            ->assertSee('PHP 700.00')
            ->assertSee('Top 5 Selling Products')
            ->assertSee('Iced Coffee')
            ->assertSee('10 sold');
    }
}
