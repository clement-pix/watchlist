<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->string('criterion_type', 20)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('chat_banned_permanently')->default(false)->after('chat_banned_until');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('reply_to_id')->nullable()->index()->after('user_id');
        });

        Schema::create('chat_message_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_message_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('reward_id')->index();
            $table->timestamps();
            $table->unique(['chat_message_id', 'user_id', 'reward_id'], 'chat_reaction_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_reactions');
        Schema::table('chat_messages', fn (Blueprint $table) => $table->dropColumn('reply_to_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('chat_banned_permanently'));
    }
};
