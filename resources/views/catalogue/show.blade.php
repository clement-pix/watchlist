@extends('layout')

@section('title', $media['title'].' — WatchList')

@section('content')
<a href="{{ route('welcome') }}#catalogue" class="back-link">← Retour au catalogue</a>

<section class="detail-hero catalogue-detail" @if($media['backdrop_url']) style="--backdrop:url('{{ $media['backdrop_url'] }}')" @endif>
    <div class="detail-poster">
        <img src="{{ $media['poster_url'] ?: asset('images/poster-placeholder.svg') }}" alt="Affiche de {{ $media['title'] }}">
    </div>
    <div class="detail-copy">
        <p class="eyebrow"><span></span> {{ $media['type_label'] }}{{ $media['year'] ? ' · '.$media['year'] : '' }}</p>
        <h1>{{ $media['title'] }}</h1>
        @if($media['tagline'])<p class="detail-tagline">{{ $media['tagline'] }}</p>@endif
        <div class="chips">
            @if($localMedia && $localMedia->avis->isNotEmpty())
                <span class="chip chip-rating" title="Note donnée par les membres de WatchList">★ {{ number_format($localMedia->avis->avg('note'), 1, ',', '') }}/5 · {{ $localMedia->avis->count() }} avis WatchList</span>
            @else
                <span class="chip">Aucun avis WatchList</span>
            @endif
            @if($media['runtime'])<span class="chip">{{ $media['runtime'] }} min</span>@endif
            @foreach($media['genres'] as $genre)<span class="chip">{{ $genre }}</span>@endforeach
        </div>
        <p class="detail-description">{{ $media['overview'] ?: 'Aucun synopsis disponible en français pour ce titre.' }}</p>
        <div class="detail-actions">
            @auth
                <form action="{{ route('liste-privee.external.store', [$media['type'],$media['id']]) }}" method="POST">
                    @csrf
                    <button type="submit" class="button">＋ Ajouter à ma liste</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="button">Se connecter pour l’ajouter</a>
            @endauth
            @if($media['trailer'])
                <a class="button button-secondary" href="https://www.youtube.com/watch?v={{ $media['trailer']['key'] }}" target="_blank" rel="noopener">▶ Voir la bande-annonce</a>
            @endif
        </div>
    </div>
</section>

<section class="providers-panel" aria-labelledby="providers-title">
    <div class="providers-heading">
        <div>
            <p class="eyebrow"><span></span> Disponibilité légale en France</p>
            <h2 id="providers-title">Où regarder ?</h2>
            <p>Les offres peuvent évoluer. Informations de disponibilité fournies par JustWatch via TMDB.</p>
        </div>
    </div>

    @if(count($media['providers']['groups']))
        <div class="provider-groups">
            @foreach($media['providers']['groups'] as $group)
                <div class="provider-group">
                    <h3>{{ $group['label'] }}</h3>
                    <div class="provider-list">
                        @foreach($group['items'] as $provider)
                            @if($provider['url'])<a href="{{ $provider['url'] }}" target="_blank" rel="noopener" class="provider-button" aria-label="Rechercher {{ $media['title'] }} sur {{ $provider['name'] }}">@else<span class="provider-button" title="Lien officiel indisponible">@endif
                                @if($provider['logo_url'])<img src="{{ $provider['logo_url'] }}" alt="" loading="lazy">@endif
                                <span>{{ $provider['name'] }}</span>
                            @if($provider['url'])</a>@else</span>@endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="provider-empty">
            <span aria-hidden="true">⌁</span>
            <div><strong>Aucune plateforme référencée en France</strong><p>TMDB et JustWatch ne signalent pas encore d’offre légale pour ce titre.</p></div>
        </div>
    @endif
</section>

@if(count($media['cast']))
<section class="cast-section">
    <div class="section-heading"><div><p class="eyebrow"><span></span> Distribution</p><h2>À l’écran</h2></div></div>
    <div class="cast-grid">
        @foreach($media['cast'] as $person)
            <article class="cast-card">
                <div>@if($person['photo_url'])<img src="{{ $person['photo_url'] }}" alt="Photo de {{ $person['name'] }}" loading="lazy">@else<span>{{ mb_substr($person['name'],0,1) }}</span>@endif</div>
                <h3>{{ $person['name'] }}</h3><p>{{ $person['character'] ?: 'Rôle non précisé' }}</p>
            </article>
        @endforeach
    </div>
</section>
@endif

<section class="reviews" id="reviews">
    <div class="section-heading">
        <div><p class="eyebrow"><span></span> La communauté WatchList</p><h2>Les avis du site</h2></div>
        <p>{{ $localMedia?->avis->count() ?? 0 }} {{ ($localMedia?->avis->count() ?? 0) > 1 ? 'avis publiés' : 'avis publié' }}. Aucune note TMDB n’est utilisée ici.</p>
    </div>

    @if($localMedia && $localMedia->avis->isNotEmpty())
        <div class="review-grid">
            @foreach($localMedia->avis as $avis)
                <article class="review">
                    <div class="review-stars" aria-label="{{ $avis->note }} étoiles sur 5">{{ str_repeat('★', $avis->note) }}<span>{{ str_repeat('★', 5 - $avis->note) }}</span></div>
                    <p class="review-meta">Par @include('partials.member-identity', ['member' => $avis->membre])</p>
                    <p>{{ $avis->commentaire }}</p>
                    @if(auth()->check() && auth()->id() === $avis->id)<a href="{{ route('comment.edit', $localMedia) }}" class="text-link">Modifier mon avis →</a>@elseif(auth()->check())<button type="button" class="review-report-button" data-report-url="{{ route('reports.reviews.store', [$avis->id_media, $avis->id]) }}" data-report-label="Avis de {{ $avis->membre?->display_name ?? 'Compte supprimé' }}">⚑ Signaler</button>@endif
                </article>
            @endforeach
        </div>
    @else
        <div class="empty-state"><h3>Soyez le premier à donner votre avis</h3><p>Votre note sera publiée sur WatchList et aidera les autres membres.</p></div>
    @endif

    @auth
        @if($localMedia)
            <div class="panel">
                @if(!$alreadyCommented)
                    <h2>Partager mon avis</h2>
                    <form action="{{ route('comment.store', $localMedia) }}" method="POST">
                        @csrf
                        <div class="form-grid">
                            <div class="field"><label for="note">Note sur 5</label><select name="note" id="note" required><option value="">Choisir</option>@for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} étoile{{ $i>1?'s':'' }}</option>@endfor</select></div>
                            <div class="field field-full"><label for="comment">Commentaire</label><textarea name="comment" id="comment" placeholder="Qu’avez-vous pensé de ce titre ?" required>{{ old('comment') }}</textarea></div>
                        </div>
                        <button type="submit" class="button">Publier mon avis</button>
                    </form>
                @else
                    <p>Vous avez déjà partagé votre avis sur ce titre.</p>
                @endif
            </div>
        @else
            <form action="{{ route('catalogue.review.prepare', [$media['type'],$media['id']]) }}" method="POST" class="review-start">
                @csrf
                <button type="submit" class="button">★ Donner mon avis</button>
                <p>WatchList préparera uniquement cette fiche dans la base locale.</p>
            </form>
        @endif
    @else
        <a href="{{ route('login') }}" class="button review-login">Se connecter pour donner son avis</a>
    @endauth
</section>
@endsection
