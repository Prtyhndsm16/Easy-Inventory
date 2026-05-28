<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->unique()->after('reference_number');
            }

            if (! Schema::hasColumn('sales', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('receipt_number');
            }

            if (! Schema::hasColumn('sales', 'amount_paid')) {
                $table->decimal('amount_paid', 12, 2)->default(0)->after('total_amount');
            }

            if (! Schema::hasColumn('sales', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->default(0)->after('amount_paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'customer_name')) {
                $table->dropColumn('customer_name');
            }

            if (Schema::hasColumn('sales', 'change_amount')) {
                $table->dropColumn('change_amount');
            }

            if (Schema::hasColumn('sales', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }

            if (Schema::hasColumn('sales', 'receipt_number')) {
                $table->dropUnique(['receipt_number']);
                $table->dropColumn('receipt_number');
            }
        });
    }
};
