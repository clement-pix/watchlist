@extends('layout')
@section('title', 'Modération — WatchList')
@section('content')
<section class="page-header page-header-row admin-header"><div><p class="eyebrow"><span></span> Confiance et sécurité</p><h1>Centre de modération</h1><p>Traitez les signalements et consultez la trace de chaque action sensible.</p></div><a class="button button-secondary" href="{{ route('admin.index') }}">Retour au backoffice</a></section>

<nav class="moderation-tabs" aria-label="Filtrer les signalements">
    @foreach(['pending'=>'À traiter','actioned'=>'Traités','dismissed'=>'Classés sans suite','all'=>'Tous'] as $value=>$label)
        <a class="{{ $currentStatus === $value ? 'is-active' : '' }}" href="{{ route('admin.moderation.index', ['status'=>$value]) }}">{{ $label }} @if($value === 'pending')<span>{{ $pendingCount }}</span>@endif</a>
    @endforeach
</nav>

<div class="moderation-layout">
    <section class="moderation-reports">
        @forelse($reports as $report)
            <article class="panel report-card">
                <header><div><span class="report-type">{{ $report->target_label }}</span><h2>{{ $report->reason_label }}</h2></div><span class="report-status is-{{ $report->status }}">{{ match($report->status){'actioned'=>'Traité','dismissed'=>'Classé',default=>'À traiter'} }}</span></header>
                <blockquote>{{ $report->target_snapshot }}</blockquote>
                <dl><div><dt>Signalé par</dt><dd>{{ $report->reporter?->display_name ?? 'Compte supprimé' }}</dd></div><div><dt>Date</dt><dd>{{ $report->created_at?->translatedFormat('d F Y à H:i') }}</dd></div></dl>
                @if($report->details)<p class="report-details"><strong>Précisions :</strong> {{ $report->details }}</p>@endif
                @if($report->status === 'pending')
                    <form method="POST" action="{{ route('admin.moderation.handle', $report) }}">@csrf @method('PUT')<div class="field"><label for="resolution-{{ $report->id }}">Décision et justification</label><textarea id="resolution-{{ $report->id }}" name="resolution" maxlength="1000" required placeholder="Expliquez brièvement votre décision…"></textarea></div><div class="report-actions"><button class="button button-secondary" name="status" value="dismissed" type="submit">Classer sans suite</button><button class="button" name="status" value="actioned" type="submit">Marquer comme traité</button></div></form>
                @else
                    <div class="report-resolution"><strong>{{ $report->handler?->display_name ?? 'Modérateur supprimé' }}</strong> · {{ $report->handled_at?->diffForHumans() }}<p>{{ $report->resolution }}</p></div>
                @endif
            </article>
        @empty
            <div class="empty-state"><h2>Aucun signalement dans cette catégorie</h2><p>La file de modération est à jour.</p></div>
        @endforelse
        {{ $reports->links() }}
    </section>

    <aside class="panel moderation-history"><div class="panel-heading"><div><span class="settings-icon">◷</span><h2>Historique</h2></div></div><div class="moderation-timeline">
        @forelse($logs as $log)
            <article><span></span><div><strong>{{ match($log->action){'message_deleted'=>'Message supprimé','review_deleted'=>'Avis supprimé','user_muted'=>'Membre rendu muet','user_banned'=>'Membre banni','user_unbanned'=>'Accès rétabli','report_actioned'=>'Signalement traité','report_dismissed'=>'Signalement classé',default=>$log->action} }}</strong><small>par {{ $log->moderator?->display_name ?? 'Compte supprimé' }} · {{ $log->created_at?->diffForHumans() }}</small>@if($log->targetUser)<p>Membre : {{ $log->targetUser->display_name }}</p>@endif @if($log->reason)<p>{{ $log->reason }}</p>@endif</div></article>
        @empty<p class="muted-copy">Aucune action enregistrée pour le moment.</p>@endforelse
    </div></aside>
</div>
@endsection
