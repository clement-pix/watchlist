<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('emoji', 16);
            $table->string('description', 180)->nullable();
            $table->enum('criterion_type', ['reviews', 'watchlist']);
            $table->unsignedInteger('threshold')->default(0);
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('equipped_reward_id')->nullable()->after('profile_visibility');
            $table->timestamp('chat_banned_until')->nullable()->after('equipped_reward_id');
            $table->string('chat_ban_reason', 180)->nullable()->after('chat_banned_until');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('message');
            $table->unsignedBigInteger('moderated_by')->nullable()->index();
            $table->string('moderation_reason', 180)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['equipped_reward_id', 'chat_banned_until', 'chat_ban_reason']);
        });
        Schema::dropIfExists('rewards');
    }
};
