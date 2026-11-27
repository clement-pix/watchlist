<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // FR : conserve la protection et invalide les anciens codes SMS.
        // EN: Keep protection enabled and invalidate previously issued SMS codes.
        DB::table('users')->where('two_factor_method', 'sms')->update([
            'two_factor_method' => 'email',
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_attempts' => 0,
        ]);
    }

    public function down(): void
    {
        // FR : ne rétablit pas un canal retiré ni des codes expirés.
        // EN: Do not restore a removed channel or old codes.
    }
};
