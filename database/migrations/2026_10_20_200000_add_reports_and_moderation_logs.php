<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_id')->index();
            $table->string('target_type', 30)->index();
            $table->string('target_id', 100)->index();
            $table->string('target_author_id', 30)->nullable()->index();
            $table->string('reason', 30);
            $table->text('details')->nullable();
            $table->text('target_snapshot');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedBigInteger('handled_by')->nullable()->index();
            $table->timestamp('handled_at')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();
        });

        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('moderator_id')->index();
            $table->unsignedBigInteger('target_user_id')->nullable()->index();
            $table->string('action', 50)->index();
            $table->string('target_type', 30)->nullable();
            $table->string('target_id', 100)->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
        Schema::dropIfExists('reports');
    }
};
