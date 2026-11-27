<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedInteger('tmdb_id')->nullable()->after('id_categorie');
            $table->string('tmdb_type', 10)->nullable()->after('tmdb_id');
            $table->unique(['tmdb_id', 'tmdb_type'], 'media_tmdb_unique');
        });

        $demoTitles = ['Horizon rouge', 'Le Royaume minuscule', 'Les Éclats du temps', 'Minuit sur Belle-Île'];
        $demoIds = DB::table('media')->whereIn('titre', $demoTitles)->pluck('id_media');

        DB::table('posseder')->whereIn('id_media', $demoIds)->delete();
        DB::table('Composer')->whereIn('id_media', $demoIds)->delete();
        DB::table('avis')->whereIn('id_media', $demoIds)->delete();
        DB::table('media')->whereIn('id_media', $demoIds)->delete();
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropUnique('media_tmdb_unique');
            $table->dropColumn(['tmdb_id', 'tmdb_type']);
        });
    }
};
