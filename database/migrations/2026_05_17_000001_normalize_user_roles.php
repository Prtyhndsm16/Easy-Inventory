<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        foreach (DB::table('users')->select('id', 'role')->get() as $user) {
            $normalizedRole = strtolower(trim((string) $user->role));

            if (! in_array($normalizedRole, ['admin', 'staff'], true)) {
                continue;
            }

            if ($normalizedRole === $user->role) {
                continue;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['role' => $normalizedRole]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
