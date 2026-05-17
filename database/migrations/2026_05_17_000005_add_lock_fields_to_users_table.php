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
        if (! Schema::hasColumn('users', 'locked_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('locked_at')->nullable()->after('role')->index();
            });
        }

        if (! Schema::hasColumn('users', 'locked_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'locked_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('locked_by');
            });
        }

        if (Schema::hasColumn('users', 'locked_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('locked_at');
            });
        }
    }
};
