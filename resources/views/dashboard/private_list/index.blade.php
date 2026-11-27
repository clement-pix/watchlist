@extends('layout')
@section('title', 'Ma liste — WatchList')
@section('content')
@php($catalogueItems = collect($catalogue['items']))

<a href="{{ route('dashboard') }}" class="back-link">← Retour à mon espace</a>
<section class="page-header list-page-header">
    <p class="eyebrow"><span></span> Sélection personnelle</p>
    <div class="list-page-heading">
        <div><h1>Ma liste</h1><p>Conservez vos films et séries, puis complétez votre sélection grâce au catalogue mondial TMDB.</p></div>
        <span class="list-count"><strong>{{ $liste->count() }}</strong> {{ Str::plural('titre', $liste->count()) }}</span>
    </div>
</section>

<section class="saved-list" aria-labelledby="saved-list-title">
    <div class="section-heading section-heading-compact">
        <div><p class="eyebrow"><span></span> Déjà enregistrés</p><h2 id="saved-list-title">À voir plus tard</h2></div>
    </div>
    @if($liste->isEmpty())
        <div class="empty-state"><div class="empty-icon">♥</div><h3>Votre liste est encore vide</h3><p>Recherchez un titre dans le catalogue ci-dessous et ajoutez-le en un clic.</p><a href="#ajouter" class="button">Explorer le catalogue</a></div>
    @else
        <div class="media-grid saved-media-grid">
            @foreach($liste as $item)
                @if($item->media)
                    <article class="media-card saved-media-card">
                        <a href="{{ $item->media->tmdb_id && $item->media->tmdb_type ? route('catalogue.show', [$item->media->tmdb_type, $item->media->tmdb_id]) : route('media.show', $item->media) }}" class="media-poster"><img src="{{ $item->media->poster_url }}" alt="Affiche de {{ $item->media->titre }}"><span class="media-overlay"><span>Voir la fiche</span></span></a>
                        <div class="media-card-body"><div><h3><a href="{{ $item->media->tmdb_id && $item->media->tmdb_type ? route('catalogue.show', [$item->media->tmdb_type, $item->media->tmdb_id]) : route('media.show', $item->media) }}">{{ $item->media->titre }}</a></h3><p>{{ $item->media->genres->take(2)->pluck('type')->join(' · ') ?: 'Dans votre liste' }}</p></div></div>
                        <form method="POST" action="{{ route('liste-privee.destroy', $item->id_media) }}" class="saved-card-action">@csrf @method('DELETE')<button type="submit" class="remove-link">Retirer de ma liste</button></form>
                    </article>
                @endif
            @endforeach
        </div>
    @endif
</section>

<section class="list-catalogue" id="ajouter" aria-labelledby="add-list-title">
    <div class="section-heading catalogue-heading">
        <div><p class="eyebrow"><span></span> Catalogue TMDB</p><h2 id="add-list-title">Ajouter à ma liste</h2></div>
        <p>Recherchez parmi les films et séries du catalogue mondial. Les résultats sont chargés à la demande, sans remplir inutilement la base de données.</p>
    </div>

    <form method="GET" action="{{ route('liste-privee.index') }}#ajouter" class="catalogue-filters">
        <div class="search-field"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="2"/></svg><input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Rechercher un film ou une série…" aria-label="Rechercher un titre"></div>
        <select name="type" aria-label="Type de contenu"><option value="all" @selected($filters['type'] === 'all')>Films et séries</option><option value="movie" @selected($filters['type'] === 'movie')>Films</option><option value="tv" @selected($filters['type'] === 'tv')>Séries</option></select>
        <select name="genre" aria-label="Genre"><option value="0">Tous les genres</option>@foreach($catalogue['genres'] as $id => $name)<option value="{{ $id }}" @selected($filters['genre'] == $id)>{{ $name }}</option>@endforeach</select>
        <button class="button" type="submit">Afficher</button>
        @if($filters['q'] || $filters['type'] !== 'all' || $filters['genre'])<a class="filter-reset" href="{{ route('liste-privee.index') }}#ajouter">Réinitialiser</a>@endif
    </form>

    @if($catalogueError)<div class="catalogue-notice catalogue-notice-error">{{ $catalogueError }}</div>@endif

    @if($catalogueEnabled && $catalogueItems->isNotEmpty())
        <div class="results-meta"><strong>{{ $filters['q'] ? 'Résultats pour « '.$filters['q'].' »' : 'Les titres populaires du moment' }}</strong><span>Page {{ $catalogue['page'] }} sur {{ $catalogue['total_pages'] }}</span></div>
        <div class="media-grid list-catalogue-grid">
            @foreach($catalogueItems as $media)
                <article class="media-card catalogue-add-card">
                    <a href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}" class="media-poster"><img src="{{ $media['poster_url'] ?: asset('images/poster-placeholder.svg') }}" alt="Affiche de {{ $media['title'] }}" loading="lazy"><span class="media-overlay"><span>Voir la fiche</span></span><span class="media-type">{{ $media['type_label'] }}</span></a>
                    <div class="media-card-body"><div><h3><a href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}">{{ $media['title'] }}</a></h3><p>{{ collect($media['genres'])->take(2)->join(' · ') ?: 'À découvrir' }}{{ $media['year'] ? ' · '.$media['year'] : '' }}</p></div></div>
                    @if($media['in_list'])
                        <span class="list-status" aria-label="Ce titre est déjà dans votre liste">✓ Déjà dans ma liste</span>
                    @else
                        <form method="POST" action="{{ route('liste-privee.external.store', [$media['type'], $media['id']]) }}" class="catalogue-add-action">@csrf<button type="submit" class="button button-small button-block">＋ Ajouter à ma liste</button></form>
                    @endif
                </article>
            @endforeach
        </div>
        @if($catalogue['total_pages'] > 1)
            <nav class="pagination" aria-label="Pagination du catalogue">
                @if($catalogue['page'] > 1)<a class="button button-secondary" href="{{ request()->fullUrlWithQuery(['page' => $catalogue['page'] - 1]) }}#ajouter">← Précédent</a>@endif
                @if($catalogue['page'] < $catalogue['total_pages'])<a class="button button-secondary" href="{{ request()->fullUrlWithQuery(['page' => $catalogue['page'] + 1]) }}#ajouter">Suivant →</a>@endif
            </nav>
        @endif
    @elseif(!$catalogueEnabled)
        <div class="catalogue-notice"><div><strong>Catalogue TMDB non configuré</strong><p>Ajoutez le jeton <code>TMDB_TOKEN</code> pour rendre tous les films et séries disponibles.</p></div></div>
    @elseif(!$catalogueError)
        <div class="empty-state"><h3>Aucun résultat</h3><p>Essayez un autre titre, un autre genre ou réinitialisez les filtres.</p></div>
    @endif
</section>
@endsection
