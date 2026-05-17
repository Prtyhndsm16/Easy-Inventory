<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->string('supplier')->nullable();
            $table->string('reference_no')->nullable();   // e.g. PO number, delivery receipt
            $table->date('date');
            $table->unsignedBigInteger('received_by')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};
