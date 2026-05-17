<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_outs')) {
            Schema::create('stock_outs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('product_name'); // snapshot in case product gets deleted
                $table->unsignedInteger('quantity');
                $table->enum('reason', ['Sold', 'Damaged', 'Expired', 'Transferred']);
                $table->date('date');
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('product_id')
                    ->references('product_id')
                    ->on('products')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
    }
};
