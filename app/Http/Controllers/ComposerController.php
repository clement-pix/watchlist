<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Composer;
use App\Models\Liste;
use Illuminate\Support\Facades\Auth;
use App\Models\Media;
use App\Models\Categorie;
use App\Models\Genre;
use App\Services\TmdbService;
use Illuminate\Support\Str;
use Throwable;

class ComposerController extends Controller
{
    /**
     * Affiche la liste privée des médias de l'utilisateur connecté.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request, TmdbService $tmdb)
    {
        $userId = Auth::id();

        // Récupère tous les médias de la table pivot WL_Composer liés à l'utilisateur
        // et charge la relation "media" pour chaque élément (eager loading)
        $liste = Composer::where('user_id', $userId)
            ->with(['media.categorie', 'media.genres'])
            ->get();

        $catalogue = ['items' => [], 'genres' => [], 'page' => 1, 'total_pages' => 1];
        $catalogueError = null;

        if ($tmdb->isConfigured()) {
            try {
                $catalogue = $tmdb->browse($request->only(['q', 'type', 'genre', 'page']));
                $listedKeys = $liste->mapWithKeys(function (Composer $item) {
                    if (! $item->media?->tmdb_id || ! $item->media?->tmdb_type) {
                        return [];
                    }

                    return [$item->media->tmdb_type.'|'.$item->media->tmdb_id => true];
                });
                $legacyKeys = $liste->mapWithKeys(function (Composer $item) {
                    if (! $item->media) {
                        return [];
                    }

                    $type = Str::lower((string) $item->media->categorie?->type) === 'film' ? 'movie' : 'tv';

                    return [$type.'|'.Str::lower($item->media->titre) => true];
                });

                $catalogue['items'] = collect($catalogue['items'])->map(function (array $media) use ($listedKeys, $legacyKeys) {
                    $media['in_list'] = $listedKeys->has($media['type'].'|'.$media['id'])
                        || $legacyKeys->has($media['type'].'|'.Str::lower(Str::limit($media['title'], 50, '')));

                    return $media;
                })->all();
            } catch (Throwable $exception) {
                report($exception);
                $catalogueError = 'Le catalogue TMDB est momentanément indisponible. Réessayez dans quelques instants.';
            }
        }

        return view('dashboard.private_list.index', [
            'liste' => $liste,
            'catalogue' => $catalogue,
            'catalogueEnabled' => $tmdb->isConfigured(),
            'catalogueError' => $catalogueError,
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
                'type' => $request->query('type', 'all'),
                'genre' => (int) $request->query('genre', 0),
            ],
        ]);
    }

    /**
     * Ajoute un média à la liste privée de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Vérifie que le média envoyé existe bien dans la table "media"
        $request->validate([
            'id_media' => 'required|exists:media,id_media',
        ]);

        $userId = Auth::id(); // Récupère l'utilisateur actuel

        // Cherche une liste existante pour cet utilisateur ou en crée une
        $liste = Liste::firstOrCreate(
            ['id' => $userId], // correspond à la colonne "id" dans la table WL_liste
            [
                'nom' => 'Liste privée',
                'date_creation' => now(),
            ]
        );

        // Ajoute une ligne dans WL_Composer (sans dupliquer si déjà présent)
        Composer::firstOrCreate([
            'user_id' => $userId,
            'id_media' => $request->id_media,
            'id_liste' => $liste->id_liste,
        ]);

        return redirect()->back()->with('success', 'Média ajouté à votre liste personnelle.');
    }

    public function storeExternal(string $type, int $id, TmdbService $tmdb)
    {
        try {
            $external = $tmdb->details($type, $id);
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->back()->with('error', 'Impossible de récupérer ce titre pour le moment.');
        }

        $media = $this->syncExternalMedia($type, $external);

        $this->addToUserList($media->id_media);

        return redirect()->route('liste-privee.index')->with('success', $media->titre.' a été ajouté à votre liste.');
    }

    public function prepareExternalReview(string $type, int $id, TmdbService $tmdb)
    {
        try {
            $external = $tmdb->details($type, $id);
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->back()->with('error', 'Impossible de préparer la fiche d’avis pour le moment.');
        }

        $media = $this->syncExternalMedia($type, $external);

        return redirect(route('media.show', $media->id_media).'#reviews')
            ->with('success', 'Vous pouvez maintenant publier votre avis sur '.$media->titre.'.');
    }

    /**
     * Supprime un média de la liste privée de l'utilisateur connecté.
     *
     * @param  int  $id_media
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id_media)
    {
        $userId = Auth::id(); // ID de l'utilisateur connecté

        // Supprime la ligne dans la table pivot Composer où l'utilisateur et le média correspondent
        Composer::where('user_id', $userId)
            ->where('id_media', $id_media)
            ->delete();

        return redirect()->back()->with('success', 'Média retiré de votre liste privée.');
    }

    private function addToUserList(int $mediaId): void
    {
        $userId = Auth::id();
        $liste = Liste::firstOrCreate(
            ['id' => $userId],
            ['nom' => 'Liste privée', 'date_creation' => now()]
        );

        Composer::firstOrCreate([
            'user_id' => $userId,
            'id_media' => $mediaId,
            'id_liste' => $liste->id_liste,
        ]);
    }

    private function syncExternalMedia(string $type, array $external): Media
    {
        $category = Categorie::firstOrCreate(['type' => $type === 'movie' ? 'film' : 'série']);
        $title = Str::limit($external['title'], 50, '');
        $media = Media::where('tmdb_id', $external['id'])->where('tmdb_type', $type)->first()
            ?? Media::whereNull('tmdb_id')->where('titre', $title)->where('id_categorie', $category->id_categorie)->first()
            ?? new Media();

        $media->fill([
            'titre' => $title,
            'description' => Str::limit($external['overview'] ?: 'Aucun synopsis disponible.', 1500, ''),
            'lien_image' => $external['poster_url'] ?: 'images/poster-placeholder.svg',
            'date_ajout' => $media->date_ajout ?: now(),
            'id_categorie' => $category->id_categorie,
            'tmdb_id' => $external['id'],
            'tmdb_type' => $type,
        ]);
        $media->save();

        $genreIds = collect($external['genres'])
            ->map(fn (string $name) => Genre::firstOrCreate(['type' => Str::lower($name)])->id_genre)
            ->all();
        $media->genres()->syncWithoutDetaching($genreIds);

        return $media;
    }
}
