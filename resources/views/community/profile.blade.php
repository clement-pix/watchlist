@extends('layout')
@section('title', $member->display_name.' — Communauté WatchList')
@section('content')
<section class="page-header member-profile-header">
    <p class="eyebrow"><span></span> Profil membre</p>
    <div class="member-profile-title"><span>{{ $member->equippedReward?->emoji ?: '🎬' }}</span><div><h1>{{ $member->display_name }}</h1><p>{{ $communityStats['rank']['name'] }} · membre depuis {{ $member->created_at?->translatedFormat('F Y') }}</p></div></div>
</section>
<section class="community-stats community-stats-four">
    <article><strong>{{ $communityStats['reviews'] }}</strong><span>avis publiés</span></article>
    <article><strong>{{ $communityStats['watchlist'] }}</strong><span>films et séries en liste</span></article>
    <article><strong>{{ $unlockedRewards->count() }}</strong><span>récompenses débloquées</span></article>
    <article><strong>{{ $communityStats['messages'] }}</strong><span>messages dans le Salon</span></article>
</section>
<section class="panel reward-panel"><div class="panel-heading"><div><span class="settings-icon">✦</span><h2>Collection d’émoticônes</h2></div><p>Des distinctions obtenues grâce à l’activité sur WatchList.</p></div><div class="reward-grid">@forelse($unlockedRewards as $reward)<article class="reward-card"><span>{{ $reward->emoji }}</span><div><strong>{{ $reward->name }}</strong><small>{{ $reward->description }}</small></div></article>@empty<p class="muted-copy">La première récompense arrive dès la première contribution.</p>@endforelse</div></section>
@endsection
