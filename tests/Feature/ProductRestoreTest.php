<?php

namespace Tests\Feature;

use App\Models\DeletedProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_move_product_to_deleted_products_without_removing_it_from_database(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $imagePath = 'product-images/restorable.jpg';

        Storage::disk('public')->makeDirectory('product-images');
        Storage::disk('public')->put($imagePath, 'image contents');

        $product = Product::create([
            'product_name' => 'Restorable Mouse',
            'category' => 'Accessories',
            'price' => 499.99,
            'stock' => 15,
            'supplier' => 'ABC Trading',
            'barcode' => 'RESTORE-001',
            'date_added' => now()->toDateString(),
            'image_path' => $imagePath,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index', absolute: false));

        $this->assertSoftDeleted('products', [
            'product_id' => $product->getKey(),
        ]);
        $this->assertDatabaseHas('deleted_products', [
            'original_product_id' => $product->getKey(),
            'product_name' => 'Restorable Mouse',
            'barcode' => 'RESTORE-001',
        ]);
        $this->assertNotNull(Product::withTrashed()->find($product->getKey()));
        $this->assertTrue(Storage::disk('public')->exists($imagePath));
    }

    public function test_admin_can_view_and_restore_deleted_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'product_name' => 'Restore From Table',
            'category' => 'Accessories',
            'price' => 499.99,
            'stock' => 15,
            'supplier' => 'ABC Trading',
            'barcode' => 'RESTORE-002',
            'date_added' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product));
        $deletedProduct = DeletedProduct::where('original_product_id', $product->getKey())->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertDontSee('Restore From Table');

        $this->actingAs($admin)
            ->get(route('admin.products.deleted'))
            ->assertOk()
            ->assertSee('Restore From Table')
            ->assertSee('Restore');

        $this->actingAs($admin)
            ->patch(route('admin.products.restore', $deletedProduct))
            ->assertRedirect(route('admin.products.deleted', absolute: false));

        $this->assertDatabaseHas('products', [
            'product_id' => $product->getKey(),
            'deleted_at' => null,
        ]);
        $this->assertDatabaseMissing('deleted_products', [
            'original_product_id' => $product->getKey(),
        ]);
    }
}
