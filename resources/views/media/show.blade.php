@extends('layout')

@section('title', $media->titre . ' — WatchList')

@section('content')
<a href="{{ route('welcome') }}#catalogue" class="back-link">← Retour à la sélection</a>

<section class="detail-hero">
    <div class="detail-poster"><img src="{{ $media->poster_url }}" alt="Affiche de {{ $media->titre }}"></div>
    <div class="detail-copy">
        <p class="eyebrow"><span></span> Fiche média</p>
        <h1>{{ $media->titre }}</h1>
        <div class="chips">
            @if($media->categorie)<span class="chip">{{ ucfirst($media->categorie->type) }}</span>@endif
            @foreach($media->genres as $genre)<span class="chip">{{ ucfirst($genre->type) }}</span>@endforeach
            <span class="chip">★ {{ $media->avis->count() ? number_format($media->avis->avg('note'), 1, ',', '') . '/5' : 'Pas encore noté' }}</span>
        </div>
        <p class="detail-description">{{ $media->description }}</p>
        @auth
            <form action="{{ route('liste-privee.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_media" value="{{ $media->id_media }}">
                <button type="submit" class="button">＋ Ajouter à ma liste</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="button">Se connecter pour l’ajouter</a>
        @endauth
    </div>
</section>

<section class="reviews" id="reviews">
    <div class="section-heading">
        <div><p class="eyebrow"><span></span> La communauté</p><h2>Avis des spectateurs</h2></div>
        <p>{{ $media->avis->count() }} {{ $media->avis->count() > 1 ? 'avis publiés' : 'avis publié' }} pour ce titre.</p>
    </div>

    @if($media->avis->isEmpty())
        <div class="empty-state"><h3>Soyez le premier à donner votre avis</h3><p>Votre recommandation aidera les prochains spectateurs.</p></div>
    @else
        <div class="review-grid">
            @foreach($media->avis as $avis)
                <article class="review">
                    <div class="review-stars" aria-label="{{ $avis->note }} étoiles sur 5">{{ str_repeat('★', $avis->note) }}<span style="color:#4b5363">{{ str_repeat('★', 5 - $avis->note) }}</span></div>
                    <p class="review-meta">Par @include('partials.member-identity', ['member' => $avis->membre])</p>
                    <p>{{ $avis->commentaire }}</p>
                    @if(auth()->check() && auth()->id() === $avis->id)
                        <a href="{{ route('comment.edit', $media) }}" class="text-link">Modifier mon avis →</a>
                    @elseif(auth()->check())
                        <button type="button" class="review-report-button" data-report-url="{{ route('reports.reviews.store', [$avis->id_media, $avis->id]) }}" data-report-label="Avis de {{ $avis->membre?->display_name ?? 'Compte supprimé' }}">⚑ Signaler</button>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    @auth
        <div class="panel">
            @if(!$alreadyCommented)
                <h2>Partager mon avis</h2>
                <form action="{{ route('comment.store', $media) }}" method="POST">
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
    @endauth
</section>
@endsection
