@extends('layout')

@section('title', 'Backoffice — WatchList')

@section('content')
<section class="page-header page-header-row admin-header">
    <div><p class="eyebrow"><span></span> Backoffice WatchList</p><h1>Piloter le site</h1><p>Vue d’ensemble des membres, des sélections éditoriales et des contributions.</p></div>
    <div class="header-actions"><a class="button button-secondary" href="{{ route('admin.featured.index') }}">Mises en avant</a><a class="button button-secondary" href="{{ route('admin.moderation.index') }}">Modération @if($stats['pendingReports'])<span class="button-count">{{ $stats['pendingReports'] }}</span>@endif</a>@if((int) auth()->user()->id_role === 1)<a class="button button-secondary" href="{{ route('admin.rewards.index') }}">Récompenses</a><a class="button" href="{{ route('users.index') }}">Gérer les membres</a>@endif</div>
</section>

<section class="admin-stats" aria-label="Statistiques du site">
    <article><span>Membres</span><strong>{{ $stats['users'] }}</strong><small>comptes enregistrés</small></article>
    <article><span>Fiches locales</span><strong>{{ $stats['media'] }}</strong><small>films et séries suivis</small></article>
    <article><span>Avis publiés</span><strong>{{ $stats['reviews'] }}</strong><small>contributions membres</small></article>
    <article><span>Note moyenne</span><strong>{{ number_format($stats['average'], 1, ',', '') }}/5</strong><small>sur les avis WatchList</small></article>
</section>

<div class="admin-columns">
    <section class="panel admin-panel">
        <div class="panel-heading"><div><span class="settings-icon">◎</span><h2>Derniers membres</h2></div>@if((int) auth()->user()->id_role === 1)<a href="{{ route('users.index') }}" class="text-link">Tous les membres <span>→</span></a>@endif</div>
        <div class="admin-list">
            @forelse($recentUsers as $member)
                <article><span class="member-avatar">{{ mb_strtoupper(mb_substr($member->firstname ?: $member->pseudo, 0, 1)) }}</span><div><strong>{{ $member->firstname }} {{ $member->name }}</strong><small>{{ $member->email }}</small></div><span class="chip">{{ $member->role_label }}</span></article>
            @empty<p class="muted-copy">Aucun membre enregistré.</p>@endforelse
        </div>
    </section>

    <section class="panel admin-panel">
        <div class="panel-heading"><div><span class="settings-icon">★</span><h2>Avis récents</h2></div></div>
        <div class="admin-list review-admin-list">
            @forelse($recentReviews as $review)
                <article><div><strong>{{ $review->media?->titre ?: 'Média supprimé' }}</strong><small>{{ $review->membre?->display_name ?: 'Compte supprimé' }} · {{ $review->note }}/5</small><p>{{ \Illuminate\Support\Str::limit($review->commentaire, 110) }}</p></div><form action="{{ route('admin.reviews.destroy', [$review->id_media, $review->id]) }}" method="POST" onsubmit="return confirm('Supprimer cet avis ?')">@csrf @method('DELETE')<button class="remove-link" type="submit">Supprimer</button></form></article>
            @empty<p class="muted-copy">Aucun avis publié.</p>@endforelse
        </div>
    </section>
</div>

<section class="panel admin-panel chat-moderation-panel">
    <div class="panel-heading"><div><span class="settings-icon">✦</span><h2>Modération du Salon</h2></div><p>Les derniers messages publiés par les membres.</p></div>
    <div class="admin-list chat-admin-list">
        @forelse($recentMessages as $message)
            <article><div><strong>{{ $message->user?->display_name ?: 'Compte supprimé' }}</strong><small>{{ $message->created_at?->diffForHumans() }}</small><p>{{ $message->message }}</p></div><div class="chat-admin-actions"><button class="remove-link" type="button" data-admin-delete-chat="{{ route('chat.destroy', $message) }}">Supprimer</button>@if($message->user && (int)$message->user->id_role !== 1)<form method="POST" action="{{ route('admin.chat.suspend', $message->user) }}">@csrf<input type="hidden" name="duration" value="60"><input type="hidden" name="reason" value="Modération du Salon"><button class="remove-link" type="submit">Suspendre 1 h</button></form>@endif</div></article>
        @empty<p class="muted-copy">Aucun message dans le Salon pour le moment.</p>@endforelse
    </div>
</section>
@endsection
