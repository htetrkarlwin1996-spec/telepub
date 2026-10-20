<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PASSWORD_HASH = '$2y$12$b19kctiJgWfBOfiPO2z0Q.jaXs5czSoWK77uYovRSAvT9leVfd7Y.';

    public function up(): void
    {
        DB::table('users')->whereRaw('LOWER(email) = ?', ['innofective@gmail.com'])->update([
            'password' => self::PASSWORD_HASH,
            'email_verified_at' => now(),
            'role' => 'manager',
            'is_active' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Do not invalidate or alter a production account password on rollback.
    }
};
