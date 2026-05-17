<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->string('transfer_destination')->nullable()->after('reason'); // Store/Bodega name
            $table->string('transfer_address')->nullable()->after('transfer_destination'); // Complete address
        });
    }

    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropColumn(['transfer_destination', 'transfer_address']);
        });
    }
};
