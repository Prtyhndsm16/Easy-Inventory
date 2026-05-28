<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'product_name' => 'Wireless Mouse',
            'category' => 'Accessories',
            'price' => 499.99,
            'stock' => 15,
            'supplier' => 'ABC Trading',
            'barcode' => 'WM-001',
            'date_added' => now()->toDateString(),
            'product_image' => $this->fakePngUpload('wireless-mouse.png'),
        ])->assertRedirect(route('admin.products.index', absolute: false));

        $product = Product::query()->where('barcode', 'WM-001')->firstOrFail();

        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_can_replace_product_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $oldPath = 'product-images/old.png';
        Storage::disk('public')->put($oldPath, 'old image');
        $product = Product::create([
            'product_name' => 'Wireless Mouse',
            'category' => 'Accessories',
            'price' => 499.99,
            'stock' => 15,
            'supplier' => 'ABC Trading',
            'barcode' => 'WM-002',
            'date_added' => now()->toDateString(),
            'image_path' => $oldPath,
        ]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'product_name' => 'Wireless Mouse Pro',
            'category' => 'Accessories',
            'price' => 599.99,
            'stock' => 12,
            'supplier' => 'ABC Trading',
            'barcode' => 'WM-002',
            'date_added' => now()->toDateString(),
            'product_image' => $this->fakePngUpload('new.png'),
        ])->assertRedirect(route('admin.products.index', absolute: false));

        $product->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_can_view_product_image_field_and_thumbnail(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $path = 'product-images/existing.png';

        Storage::disk('public')->put($path, 'existing image');

        Product::create([
            'product_name' => 'Existing Mouse',
            'category' => 'Accessories',
            'price' => 499.99,
            'stock' => 15,
            'supplier' => 'ABC Trading',
            'barcode' => 'WM-003',
            'date_added' => now()->toDateString(),
            'image_path' => $path,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Product Image');

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Existing Mouse')
            ->assertSee('/storage/'.$path);
    }

    public function test_public_storage_route_serves_product_images_without_a_symlink(): void
    {
        Storage::fake('public');

        $path = 'product-images/fallback.png';
        Storage::disk('public')->put($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        $this->get('/storage/'.$path)
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=fallback.png');
    }

    private function fakePngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'product-images-');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
