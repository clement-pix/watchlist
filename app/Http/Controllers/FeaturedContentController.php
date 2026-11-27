<?php

namespace App\Http\Controllers;

use App\Models\FeaturedContent;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class FeaturedContentController extends Controller
{
    public function index()
    {
        $selections = FeaturedContent::query()
            ->orderBy('placement')
            ->orderBy('position')
            ->get();

        return view('admin.featured.index', [
            'selections' => $selections,
            'placements' => FeaturedContent::PLACEMENTS,
        ]);
    }

    public function store(Request $request, TmdbService $tmdb)
    {
        $validated = $request->validate([
            'tmdb_id' => ['required', 'integer', 'min:1'],
            'media_type' => ['required', Rule::in(['movie', 'tv'])],
            'placement' => ['required', Rule::in(array_keys(FeaturedContent::PLACEMENTS))],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        try {
            $media = $tmdb->details($validated['media_type'], (int) $validated['tmdb_id']);
        } catch (Throwable) {
            return back()->withInput()->withErrors(['tmdb_id' => 'Ce contenu est introuvable sur TMDB. Vérifiez son identifiant et son type.']);
        }

        $exists = FeaturedContent::query()
            ->where('tmdb_id', $validated['tmdb_id'])
            ->where('media_type', $validated['media_type'])
            ->where('placement', $validated['placement'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['tmdb_id' => 'Ce contenu est déjà présent dans cette rubrique.']);
        }

        FeaturedContent::create([
            ...$validated,
            'title' => $media['title'],
            'poster_url' => $media['poster_url'],
            'genres' => $media['genres'],
            'release_year' => $media['year'],
            'position' => $validated['position'] ?? 0,
            'active' => true,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', $media['title'].' a été ajouté à la mise en avant.');
    }

    public function update(Request $request, FeaturedContent $featuredContent)
    {
        $validated = $request->validate([
            'placement' => ['required', Rule::in(array_keys(FeaturedContent::PLACEMENTS))],
            'position' => ['required', 'integer', 'min:0', 'max:999'],
            'active' => ['nullable', 'boolean'],
        ]);

        $duplicate = FeaturedContent::query()
            ->whereKeyNot($featuredContent->id)
            ->where('tmdb_id', $featuredContent->tmdb_id)
            ->where('media_type', $featuredContent->media_type)
            ->where('placement', $validated['placement'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['placement' => 'Ce contenu est déjà présent dans cette rubrique.']);
        }

        $featuredContent->update([
            ...$validated,
            'active' => $request->boolean('active'),
        ]);

        return back()->with('success', 'La mise en avant a été mise à jour.');
    }

    public function destroy(FeaturedContent $featuredContent)
    {
        $featuredContent->delete();

        return back()->with('success', 'Le contenu a été retiré des mises en avant.');
    }
}
