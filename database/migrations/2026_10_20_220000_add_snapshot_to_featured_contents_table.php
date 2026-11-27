<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('featured_contents', 'title')) {
            Schema::table('featured_contents', function (Blueprint $table) {
                $table->string('title')->nullable()->after('placement');
                $table->text('poster_url')->nullable()->after('title');
                $table->json('genres')->nullable()->after('poster_url');
                $table->string('release_year', 4)->nullable()->after('genres');
            });
        }

    }

    public function down(): void
    {
        if (Schema::hasColumn('featured_contents', 'title')) {
            Schema::table('featured_contents', function (Blueprint $table) {
                $table->dropColumn(['title', 'poster_url', 'genres', 'release_year']);
            });
        }
    }
};
