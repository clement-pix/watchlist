@extends('layout')
@section('title', 'WatchList — Trouvez votre prochaine histoire')
@section('mainClass', 'home-main')
@section('content')
@php
    $catalogueItems = collect($catalogue['items']);
    $editorialItems = $featuredSections->pluck('items')->flatten(1);
    $weeklySection = $featuredSections->get('weekly');
    $weeklyItems = collect($weeklySection['items'] ?? []);
    $weeklyFeature = $weeklyItems->first();
    $secondarySections = $featuredSections->only(['new', 'recommended']);
    $featuredOnline = $editorialItems->first() ?: $catalogueItems->first();
    $featuredLocal = $medias->first();
@endphp
<section class="hero">
    <div class="hero-glow hero-glow-one"></div><div class="hero-glow hero-glow-two"></div>
    <div class="hero-content">
        <p class="eyebrow"><span></span> Films et séries à la demande</p>
        <h1>Trouvez votre<br><em>prochaine histoire.</em></h1>
        <p class="hero-copy">Explorez un catalogue mondial, filtrez selon votre humeur et gardez uniquement les titres qui comptent dans votre liste personnelle.</p>
        <div class="hero-actions">
            <a href="#catalogue" class="button">Explorer le catalogue <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg></a>
            @guest<a href="{{ route('register') }}" class="text-link">Créer ma liste <span>↗</span></a>
            @else<a href="{{ route('liste-privee.index') }}" class="text-link">Voir ma liste <span>↗</span></a>@endguest
        </div>
        <div class="hero-proof" aria-label="Fonctionnalités principales">
            <span><strong>Films + séries</strong>un seul catalogue</span><span><strong>20+</strong>genres disponibles</span><span><strong>1 clic</strong>pour sauvegarder</span>
        </div>
    </div>
    <div class="hero-visual" aria-hidden="true">
        <div class="poster poster-back poster-one"></div><div class="poster poster-back poster-two"></div>
        <div class="poster poster-featured">
            @if($featuredOnline && $featuredOnline['poster_url'])<img src="{{ $featuredOnline['poster_url'] }}" alt="">
            @elseif($featuredLocal)<img src="{{ $featuredLocal->poster_url }}" alt="">
            @else<img src="{{ asset('images/poster-placeholder.svg') }}" alt="">@endif
        </div>
        <span class="floating-rating">★ À découvrir</span>
    </div>
</section>
@if($editorialItems->isNotEmpty())
<section class="editorial-section" aria-labelledby="editorial-title">
    <div class="section-heading editorial-heading">
        <div><p class="eyebrow"><span></span> Choisi par WatchList</p><h2 id="editorial-title">À ne pas manquer</h2></div>
        <p>Une sélection courte pour découvrir rapidement un film ou une série qui mérite votre attention.</p>
    </div>
    @if($weeklyFeature)
        <article class="editorial-spotlight">
            @if($weeklyFeature['poster_url'])<img class="editorial-spotlight-bg" src="{{ $weeklyFeature['poster_url'] }}" alt="" aria-hidden="true">@endif
            <div class="editorial-spotlight-shade"></div>
            <div class="editorial-spotlight-copy">
                <span class="editorial-label"><i></i> Sélection de la semaine</span>
                <div class="editorial-meta"><span>{{ $weeklyFeature['type_label'] }}</span>@if($weeklyFeature['year'])<span>{{ $weeklyFeature['year'] }}</span>@endif</div>
                <h3>{{ $weeklyFeature['title'] }}</h3>
                <p>{{ collect($weeklyFeature['genres'])->take(3)->join(' · ') ?: 'Une découverte choisie par WatchList.' }}</p>
                <a class="button" href="{{ route('catalogue.show', [$weeklyFeature['type'], $weeklyFeature['id']]) }}">Découvrir la sélection <span>→</span></a>
            </div>
            <a class="editorial-spotlight-poster" href="{{ route('catalogue.show', [$weeklyFeature['type'], $weeklyFeature['id']]) }}"><img src="{{ $weeklyFeature['poster_url'] ?: asset('images/poster-placeholder.svg') }}" alt="Affiche de {{ $weeklyFeature['title'] }}"></a>
        </article>
    @endif

    @if($weeklyItems->count() > 1)
        <div class="editorial-more-weekly">
            @foreach($weeklyItems->skip(1) as $media)
                <a href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}"><img src="{{ $media['poster_url'] ?: asset('images/poster-placeholder.svg') }}" alt=""><span><small>Aussi cette semaine</small><strong>{{ $media['title'] }}</strong></span></a>
            @endforeach
        </div>
    @endif

    <div class="editorial-secondary-grid">
        @foreach($secondarySections as $key => $section)
            @if($section['items']->isNotEmpty())
                <section class="editorial-rail" aria-labelledby="editorial-{{ $key }}">
                    <div class="editorial-rail-heading"><div><span>{{ $key === 'new' ? 'Nouveau' : 'Pour vous' }}</span><h3 id="editorial-{{ $key }}">{{ $section['label'] }}</h3></div>@if($section['items']->count() > 1)<small>{{ $section['items']->count() }} titres</small>@endif</div>
                    <div class="editorial-rail-list">
                        @foreach($section['items'] as $media)
                            <article class="editorial-card">
                                <a href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}" class="editorial-poster"><img src="{{ $media['poster_url'] ?: asset('images/poster-placeholder.svg') }}" alt="Affiche de {{ $media['title'] }}" loading="lazy"><span class="media-type">{{ $media['type_label'] }}</span></a>
                                <div class="editorial-card-copy"><span class="editorial-kicker">{{ $section['label'] }}</span><h4><a href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}">{{ $media['title'] }}</a></h4><p>{{ collect($media['genres'])->take(2)->join(' · ') ?: 'À découvrir' }}{{ $media['year'] ? ' · '.$media['year'] : '' }}</p><a class="text-link" href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}">Voir la fiche <span>→</span></a></div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</section>
@endif
<section class="catalogue-section" id="catalogue">
    <div class="section-heading catalogue-heading">
        <div><p class="eyebrow"><span></span> Catalogue en direct</p><h2>Que voulez-vous regarder ?</h2></div>
        <p>La recherche consulte TMDB en temps réel. Seuls vos favoris et vos avis sont conservés dans WatchList.</p>
    </div>
    <form method="GET" action="{{ route('welcome') }}#catalogue" class="catalogue-filters">
        <div class="search-field"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="2"/></svg><input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Rechercher un film ou une série…" aria-label="Rechercher un titre"></div>
        <select name="type" aria-label="Type de contenu"><option value="all" @selected($filters['type']==='all')>Films et séries</option><option value="movie" @selected($filters['type']==='movie')>Films</option><option value="tv" @selected($filters['type']==='tv')>Séries</option></select>
        <select name="genre" aria-label="Genre"><option value="0">Tous les genres</option>@foreach($catalogue['genres'] as $id => $name)<option value="{{ $id }}" @selected($filters['genre']==$id)>{{ $name }}</option>@endforeach</select>
        <button class="button" type="submit">Afficher</button>
        @if($filters['q'] || $filters['type'] !== 'all' || $filters['genre'])<a class="filter-reset" href="{{ route('welcome') }}#catalogue">Réinitialiser</a>@endif
    </form>
    @if($catalogueError)<div class="catalogue-notice catalogue-notice-error">{{ $catalogueError }}</div>@endif
    @if($catalogueEnabled && $catalogueItems->isNotEmpty())
        <div class="results-meta"><strong>{{ $filters['q'] ? 'Résultats pour « '.$filters['q'].' »' : 'Les titres populaires du moment' }}</strong><span>Page {{ $catalogue['page'] }} sur {{ $catalogue['total_pages'] }}</span></div>
        <div class="media-grid">
            @foreach($catalogueItems as $media)
                <article class="media-card">
                    <a href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}" class="media-poster"><img src="{{ $media['poster_url'] ?: asset('images/poster-placeholder.svg') }}" alt="Affiche de {{ $media['title'] }}" loading="lazy"><span class="media-overlay"><span>Voir la fiche</span></span><span class="media-type">{{ $media['type_label'] }}</span></a>
                    <div class="media-card-body"><div><h3><a href="{{ route('catalogue.show', [$media['type'], $media['id']]) }}">{{ $media['title'] }}</a></h3><p>{{ collect($media['genres'])->take(2)->join(' · ') ?: 'À découvrir' }}{{ $media['year'] ? ' · '.$media['year'] : '' }}</p></div><span class="rating" title="Avis publiés sur WatchList">@if($media['site_review_count'])★ {{ number_format($media['site_rating'], 1, ',', '') }}/5 <small>({{ $media['site_review_count'] }})</small>@else<span class="no-rating">Aucun avis</span>@endif</span></div>
                </article>
            @endforeach
        </div>
        @if($catalogue['total_pages'] > 1)<nav class="pagination" aria-label="Pagination du catalogue">@if($catalogue['page'] > 1)<a class="button button-secondary" href="{{ request()->fullUrlWithQuery(['page'=>$catalogue['page']-1]) }}#catalogue">← Précédent</a>@endif @if($catalogue['page'] < $catalogue['total_pages'])<a class="button button-secondary" href="{{ request()->fullUrlWithQuery(['page'=>$catalogue['page']+1]) }}#catalogue">Suivant →</a>@endif</nav>@endif
    @elseif(!$catalogueEnabled)
        <div class="catalogue-notice"><div><strong>Mode démonstration</strong><p>Ajoutez votre jeton TMDB dans <code>TMDB_TOKEN</code> pour activer le catalogue mondial. La sélection locale reste disponible ci-dessous.</p></div></div>
        @include('partials.local-catalogue', ['medias' => $medias])
    @elseif($medias->isNotEmpty())
        @include('partials.local-catalogue', ['medias' => $medias])
    @else
        <div class="empty-state"><h3>Aucun résultat</h3><p>Essayez un autre titre, un autre genre ou réinitialisez les filtres.</p></div>
    @endif
</section>
<section class="feature-strip">
    <div class="feature-intro"><p class="eyebrow"><span></span> Simple par conception</p><h2>Un grand catalogue.<br>Une application légère.</h2></div>
    <div class="feature-list"><article><span>01</span><div><h3>Explorez sans importer</h3><p>Les films, séries, affiches et genres arrivent à la demande depuis TMDB.</p></div></article><article><span>02</span><div><h3>Conservez l’essentiel</h3><p>La base locale contient uniquement les comptes, listes, titres choisis et avis.</p></div></article><article><span>03</span><div><h3>Installez partout</h3><p>Laravel, MariaDB ou MySQL et une seule clé d’API suffisent.</p></div></article></div>
</section>
@endsection
