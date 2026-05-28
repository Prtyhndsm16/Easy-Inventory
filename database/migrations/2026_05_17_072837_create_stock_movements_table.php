<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name');
            $table->enum('type', ['stock_in', 'stock_out', 'sale', 'manual']);
            $table->integer('quantity');        // positive = in, negative = out
            $table->integer('before_stock');
            $table->integer('after_stock');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('reference')->nullable();  // receipt number, stock-out id, etc.
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
