<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tmdb_id');
            $table->string('media_type', 10);
            $table->string('placement', 30)->index();
            $table->string('title');
            $table->text('poster_url')->nullable();
            $table->json('genres')->nullable();
            $table->string('release_year', 4)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['tmdb_id', 'media_type', 'placement'], 'featured_content_unique');
        });

        $removedMediaIds = DB::table('media')
            ->whereIn('titre', ['Au-delà de l’orbite', 'Dernière séance'])
            ->pluck('id_media');
        DB::table('posseder')->whereIn('id_media', $removedMediaIds)->delete();
        DB::table('Composer')->whereIn('id_media', $removedMediaIds)->delete();
        DB::table('avis')->whereIn('id_media', $removedMediaIds)->delete();
        DB::table('media')->whereIn('id_media', $removedMediaIds)->delete();

        // FR : aucune sélection éditoriale par défaut.
        // EN: Start without any editorial selections.
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_contents');
    }
};
