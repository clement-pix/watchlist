<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->string('two_factor_method', 10)->nullable()->after('two_factor_enabled');
            $table->string('two_factor_code')->nullable()->after('two_factor_method');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
            $table->unsignedTinyInteger('two_factor_attempts')->default(0)->after('two_factor_expires_at');
            $table->boolean('email_notifications')->default(true)->after('two_factor_attempts');
            $table->string('profile_visibility', 20)->default('members')->after('email_notifications');
            $table->timestamp('last_login_at')->nullable()->after('profile_visibility');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'phone_verified_at', 'two_factor_enabled', 'two_factor_method',
                'two_factor_code', 'two_factor_expires_at', 'two_factor_attempts',
                'email_notifications', 'profile_visibility', 'last_login_at', 'last_login_ip',
            ]);
        });
    }
};
