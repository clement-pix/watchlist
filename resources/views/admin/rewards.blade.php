@extends('layout')
@section('title', 'Récompenses — Backoffice WatchList')
@section('content')
<section class="page-header page-header-row"><div><p class="eyebrow"><span></span> Communauté</p><h1>Récompenses membres</h1><p>Définissez les émoticônes à débloquer et les objectifs nécessaires.</p></div><a class="button button-secondary" href="{{ route('admin.index') }}">Retour au backoffice</a></section>
<section class="panel admin-panel reward-admin-create"><div class="panel-heading"><div><span class="settings-icon">＋</span><h2>Nouvelle récompense</h2></div></div>
    <form method="POST" action="{{ route('admin.rewards.store') }}" class="reward-admin-form">@csrf
        <div class="field"><label>Émoticône</label><input name="emoji" maxlength="16" placeholder="🎬" required></div>
        <div class="field"><label>Nom</label><input name="name" placeholder="Première séance" required></div>
        <div class="field"><label>Objectif</label><select name="criterion_type"><option value="reviews">Nombre d’avis</option><option value="watchlist">Films/séries en liste</option><option value="messages">Messages dans le Salon</option></select></div>
        <div class="field"><label>Seuil</label><input type="number" name="threshold" min="0" value="1" required></div>
        <div class="field field-wide"><label>Description</label><input name="description" maxlength="180" placeholder="Débloquée après votre première contribution"></div>
        <input type="hidden" name="enabled" value="1"><button class="button" type="submit">Créer</button>
    </form>
</section>
<section class="reward-admin-list">
@foreach($rewards as $reward)
    <form class="panel reward-admin-row" method="POST" action="{{ route('admin.rewards.update', $reward) }}">@csrf @method('PUT')
        <input class="emoji-input" name="emoji" value="{{ $reward->emoji }}" maxlength="16" aria-label="Émoticône">
        <div class="field"><label>Nom</label><input name="name" value="{{ $reward->name }}" required></div>
        <div class="field"><label>Objectif</label><select name="criterion_type"><option value="reviews" @selected($reward->criterion_type === 'reviews')>Avis</option><option value="watchlist" @selected($reward->criterion_type === 'watchlist')>Liste</option><option value="messages" @selected($reward->criterion_type === 'messages')>Messages</option></select></div>
        <div class="field"><label>Seuil</label><input type="number" name="threshold" value="{{ $reward->threshold }}" min="0" required></div>
        <div class="field field-wide"><label>Description</label><input name="description" value="{{ $reward->description }}"></div>
        <input type="hidden" name="sort_order" value="{{ $reward->sort_order }}"><label class="reward-enabled"><input type="checkbox" name="enabled" value="1" @checked($reward->enabled)> Active</label>
        <div class="table-actions"><button class="button button-small" type="submit">Enregistrer</button><button class="remove-link" type="submit" form="delete-reward-{{ $reward->id }}">Supprimer</button></div>
    </form>
    <form id="delete-reward-{{ $reward->id }}" method="POST" action="{{ route('admin.rewards.destroy', $reward) }}" onsubmit="return confirm('Supprimer cette récompense ?')">@csrf @method('DELETE')</form>
@endforeach
</section>
@endsection
