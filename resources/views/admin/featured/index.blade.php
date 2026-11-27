@extends('layout')
@section('title', 'Mises en avant — WatchList')
@section('content')
<a href="{{ route('admin.index') }}" class="back-link">← Retour au backoffice</a>

<section class="page-header page-header-row">
    <div>
        <p class="eyebrow"><span></span> Sélection éditoriale</p>
        <h1>Mises en avant</h1>
        <p>Composez l’accueil sans enregistrer tout le catalogue. Les informations et les affiches restent synchronisées avec TMDB.</p>
    </div>
</section>

<div class="featured-admin-layout">
    <section class="panel featured-create-panel">
        <div class="panel-heading"><div><span class="settings-icon">＋</span><h2>Ajouter un contenu</h2></div></div>
        <form method="POST" action="{{ route('admin.featured.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field"><label for="media_type">Type</label><select id="media_type" name="media_type" required><option value="movie" @selected(old('media_type') === 'movie')>Film</option><option value="tv" @selected(old('media_type') === 'tv')>Série</option></select></div>
                <div class="field"><label for="tmdb_id">Identifiant TMDB</label><input id="tmdb_id" name="tmdb_id" type="number" min="1" value="{{ old('tmdb_id') }}" placeholder="Ex. 550" required>@error('tmdb_id')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field field-full"><label for="placement">Rubrique</label><select id="placement" name="placement" required>@foreach($placements as $key => $label)<option value="{{ $key }}" @selected(old('placement') === $key)>{{ $label }}</option>@endforeach</select>@error('placement')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field field-full"><label for="position">Ordre d’affichage</label><input id="position" name="position" type="number" min="0" max="999" value="{{ old('position', 10) }}"><p class="form-note">Les nombres les plus petits apparaissent en premier.</p></div>
            </div>
            <button class="button" type="submit">Ajouter à l’accueil</button>
        </form>
        <aside class="featured-help"><strong>Où trouver l’identifiant ?</strong><p>Sur une adresse TMDB comme <code>themoviedb.org/movie/550</code>, l’identifiant est <code>550</code>.</p></aside>
    </section>

    <section class="featured-admin-list" aria-label="Contenus mis en avant">
        @forelse($selections->groupBy('placement') as $placement => $items)
            <div class="panel featured-admin-group">
                <div class="panel-heading"><div><span class="settings-icon">★</span><h2>{{ $placements[$placement] ?? $placement }}</h2></div><span class="chip">{{ $items->count() }}</span></div>
                @foreach($items as $selection)
                    <article class="featured-admin-item">
                        <img src="{{ $selection->poster_url ?: asset('images/poster-placeholder.svg') }}" alt="" loading="lazy">
                        <div class="featured-admin-copy">
                            <strong>{{ $selection->title ?: 'Contenu TMDB indisponible' }}</strong>
                            <small>{{ $selection->media_type === 'movie' ? 'Film' : 'Série' }} · TMDB {{ $selection->tmdb_id }}</small>
                            <form method="POST" action="{{ route('admin.featured.update', $selection) }}" class="featured-inline-form">
                                @csrf @method('PUT')
                                <select name="placement" aria-label="Rubrique">@foreach($placements as $key => $label)<option value="{{ $key }}" @selected($selection->placement === $key)>{{ $label }}</option>@endforeach</select>
                                <input type="number" name="position" min="0" max="999" value="{{ $selection->position }}" aria-label="Ordre">
                                <label class="compact-check"><input type="checkbox" name="active" value="1" @checked($selection->active)> Visible</label>
                                <button class="button button-secondary" type="submit">Enregistrer</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('admin.featured.destroy', $selection) }}" onsubmit="return confirm('Retirer ce contenu de l’accueil ?')">@csrf @method('DELETE')<button class="remove-link" type="submit">Retirer</button></form>
                    </article>
                @endforeach
            </div>
        @empty
            <div class="empty-state"><h3>Aucune mise en avant</h3><p>Ajoutez un contenu TMDB pour composer la page d’accueil.</p></div>
        @endforelse
    </section>
</div>
@endsection
