<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_attempt_is_recorded(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'email' => $user->email,
            'event' => 'auth.login',
            'status' => 'failed',
        ]);
    }

    public function test_successful_login_is_recorded(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'email' => $user->email,
            'event' => 'auth.login',
            'status' => 'success',
        ]);
    }

    public function test_admin_product_creation_is_recorded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'product_name' => 'USB Cable',
            'category' => 'Accessories',
            'price' => 199.99,
            'stock' => 25,
            'supplier' => 'Main Supplier',
            'barcode' => 'USB-001',
            'date_added' => now()->toDateString(),
        ]);

        $product = Product::query()->where('barcode', 'USB-001')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'product.created',
            'status' => 'success',
            'auditable_type' => Product::class,
            'auditable_id' => $product->getKey(),
        ]);
    }
}
