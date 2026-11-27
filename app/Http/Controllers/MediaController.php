<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Media;
use App\Models\Categorie;
use App\Models\Genre;
use App\Models\FeaturedContent;
use Illuminate\Support\Facades\Storage;
use App\Services\TmdbService;
use Illuminate\Support\Str;
use Throwable;

class MediaController extends Controller
{
    /**
     * Affiche la page d'accueil publique avec les médias.
     *
     * @return \Illuminate\View\View
     */
    public function welcome(Request $request, TmdbService $tmdb)
    {
        $medias = Media::with(['categorie', 'genres', 'avis'])->latest('date_ajout')->get();
        $catalogue = ['items' => [], 'genres' => [], 'page' => 1, 'total_pages' => 1];
        $catalogueError = null;

        if ($tmdb->isConfigured()) {
            try {
                $catalogue = $tmdb->browse($request->only(['q', 'type', 'genre', 'page']));
                $localRatings = $medias->mapWithKeys(function (Media $media) {
                    $type = Str::lower((string) $media->categorie?->type) === 'film' ? 'movie' : 'tv';
                    $key = $type.'|'.Str::lower($media->titre);

                    return [$key => [
                        'rating' => $media->avis->isNotEmpty() ? round((float) $media->avis->avg('note'), 1) : null,
                        'count' => $media->avis->count(),
                    ]];
                });

                $catalogue['items'] = collect($catalogue['items'])->map(function (array $item) use ($localRatings) {
                    $key = $item['type'].'|'.Str::lower(Str::limit($item['title'], 50, ''));
                    $community = $localRatings->get($key, ['rating' => null, 'count' => 0]);
                    $item['site_rating'] = $community['rating'];
                    $item['site_review_count'] = $community['count'];

                    return $item;
                })->all();
            } catch (Throwable $exception) {
                report($exception);
                $catalogueError = 'Le catalogue en ligne est momentanément indisponible. La sélection locale reste accessible.';
            }
        }

        $featuredSections = collect(FeaturedContent::PLACEMENTS)->mapWithKeys(function (string $label, string $placement) {
            $items = FeaturedContent::query()
                ->where('placement', $placement)
                ->where('active', true)
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->map(fn (FeaturedContent $selection) => [
                    'id' => $selection->tmdb_id,
                    'type' => $selection->media_type,
                    'type_label' => $selection->media_type === 'movie' ? 'Film' : 'Série',
                    'title' => $selection->title,
                    'poster_url' => $selection->poster_url,
                    'genres' => $selection->genres ?? [],
                    'year' => $selection->release_year,
                    'selection_id' => $selection->id,
                ]);

            return [$placement => ['label' => $label, 'items' => $items]];
        });

        return view('welcome', [
            'medias' => $medias,
            'catalogue' => $catalogue,
            'catalogueEnabled' => $tmdb->isConfigured(),
            'catalogueError' => $catalogueError,
            'featuredSections' => $featuredSections,
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
                'type' => $request->query('type', 'all'),
                'genre' => (int) $request->query('genre', 0),
            ],
        ]);
    }

    public function catalogueShow(string $type, int $id, TmdbService $tmdb)
    {
        try {
            $media = $tmdb->details($type, $id);
        } catch (Throwable $exception) {
            report($exception);
            abort(503, 'Le catalogue est momentanément indisponible.');
        }

        $category = $type === 'movie' ? 'film' : 'série';
        $localMedia = Media::with(['avis.membre', 'categorie', 'genres'])
            ->where('titre', Str::limit($media['title'], 50, ''))
            ->whereHas('categorie', fn ($query) => $query->where('type', $category))
            ->first();
        $alreadyCommented = $localMedia && auth()->check()
            ? $localMedia->avis->contains('id', auth()->id())
            : false;

        return view('catalogue.show', compact('media', 'localMedia', 'alreadyCommented'));
    }

    /**
     * Affiche la liste complète des médias (vue admin).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $medias = Media::with(['categorie', 'genres'])->orderBy('titre')->get();
        return view('media.index', compact('medias')); // Vue de gestion
    }

    /**
     * Affiche le formulaire de création de média.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Categorie::all(); // Toutes les catégories
        $genres = Genre::all();         // Tous les genres (films, séries, etc.)

        return view('media.create', compact('categories', 'genres'));
    }

    /**
     * Enregistre un nouveau média avec image, catégorie et genre.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validation des champs du formulaire
        $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'required|string',
            'image'         => 'required|image',
            'id_categorie'  => 'required|exists:categorie,id_categorie',
            'id_genre'      => 'required|exists:genre,id_genre',
        ]);

        // Sauvegarde de l’image dans le dossier "storage/app/public/media"
        $path = $request->file('image')->store('media', 'public');

        // Création du média dans la base de données
        $media = Media::create([
            'titre'        => $request->titre,
            'description'  => $request->description,
            'lien_image'   => 'storage/' . $path, // Stocké avec préfixe pour asset()
            'id_categorie' => $request->id_categorie,
            'date_ajout'   => now(), // Date d’ajout automatique
        ]);

        // Liaison du genre via la table pivot "posseder"
        $media->genres()->attach($request->id_genre);

        return redirect()->route('media.index')->with('success', 'Média ajouté avec succès.');
    }

    /**
     * Supprime un média (et son image) de la base.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $media = Media::findOrFail($id);

        // Suppression du fichier image s’il existe dans le disque "public"
        if ($media->lien_image && Storage::disk('public')->exists(str_replace('storage/', '', $media->lien_image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $media->lien_image));
        }

        // Détache tous les genres associés
        $media->genres()->detach();

        // Supprime le média de la base
        $media->delete();

        return redirect()->back()->with('success', 'Média supprimé avec succès.');
    }

    /**
     * Affiche la page d’un média avec ses détails et avis.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Charge le média avec ses avis associés
        $media = Media::with(['avis.membre', 'categorie', 'genres'])->findOrFail($id);

        // Vérifie si l’utilisateur a déjà laissé un avis
        $alreadyCommented = false;
        if (auth()->check()) {
            $alreadyCommented = $media->avis->where('id', auth()->id())->isNotEmpty();
        }

        return view('media.show', compact('media', 'alreadyCommented'));
    }
}
