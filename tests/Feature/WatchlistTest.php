<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    public function test_member_can_add_a_tmdb_title_only_once_and_remove_only_their_own_entry(): void
    {
        // FR : ce schéma minimal reproduit les tables traversées par ces routes.
        // EN: This minimal schema mirrors the tables touched by these routes.
        DB::statement('CREATE TABLE categorie (id_categorie INTEGER PRIMARY KEY, type TEXT)');
        DB::statement('CREATE TABLE genre (id_genre INTEGER PRIMARY KEY, type TEXT)');
        DB::statement('CREATE TABLE media (id_media INTEGER PRIMARY KEY, titre TEXT, description TEXT, lien_image TEXT, date_ajout TEXT, id_categorie INTEGER, tmdb_id INTEGER, tmdb_type TEXT, UNIQUE(tmdb_id, tmdb_type))');
        DB::statement('CREATE TABLE posseder (id_media INTEGER, id_genre INTEGER, PRIMARY KEY(id_media, id_genre))');
        DB::statement('CREATE TABLE liste (id_liste INTEGER PRIMARY KEY, id INTEGER UNIQUE, nom TEXT, date_creation TEXT)');
        DB::statement('CREATE TABLE Composer (user_id INTEGER, id_media INTEGER, id_liste INTEGER, PRIMARY KEY(user_id, id_media, id_liste))');

        $this->mock(TmdbService::class)->shouldReceive('details')->with('movie', 42)->times(3)->andReturn([
            'id' => 42, 'title' => 'Film de test', 'overview' => 'Synopsis', 'poster_url' => null, 'genres' => ['Drame'],
        ]);

        $first = new User(['id_role' => 2]);
        $first->id = 101;
        $second = new User(['id_role' => 2]);
        $second->id = 102;
        $addUrl = route('liste-privee.external.store', ['movie', 42]);

        $this->actingAs($first)->post($addUrl)->assertRedirect(route('liste-privee.index'));
        $this->post($addUrl)->assertRedirect(route('liste-privee.index'));
        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseCount('Composer', 1);

        $this->actingAs($second)->post($addUrl)->assertRedirect(route('liste-privee.index'));
        $mediaId = Media::sole()->id_media;
        $this->actingAs($first)->from(route('liste-privee.index'))
            ->delete(route('liste-privee.destroy', $mediaId))->assertRedirect(route('liste-privee.index'));
        $this->assertDatabaseMissing('Composer', ['user_id' => 101, 'id_media' => $mediaId]);
        $this->assertDatabaseHas('Composer', ['user_id' => 102, 'id_media' => $mediaId]);
    }
}
