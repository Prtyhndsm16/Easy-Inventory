<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('deleted_products')) {
            Schema::create('deleted_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_product_id')->unique();
                $table->string('product_name');
                $table->string('category')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->unsignedInteger('stock')->default(0);
                $table->string('supplier')->nullable();
                $table->string('barcode')->nullable()->index();
                $table->date('date_added')->nullable();
                $table->string('image_path')->nullable();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('deleted_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'deleted_at')) {
            return;
        }

        foreach (DB::table('products')->whereNotNull('deleted_at')->get() as $product) {
            DB::table('deleted_products')->updateOrInsert(
                ['original_product_id' => $product->product_id],
                [
                    'product_name' => $product->product_name,
                    'category' => $product->category,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'supplier' => $product->supplier,
                    'barcode' => $product->barcode,
                    'date_added' => $product->date_added,
                    'image_path' => $product->image_path ?? null,
                    'deleted_by' => null,
                    'deleted_at' => $product->deleted_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_products');
    }
};
