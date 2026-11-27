@extends('layout')
@section('title', 'Gérer le catalogue — WatchList')
@section('content')
<a href="{{ route('dashboard') }}" class="back-link">← Retour à mon espace</a>
<section class="page-header page-header-row"><div><p class="eyebrow"><span></span> Administration</p><h1>Le catalogue</h1><p>{{ $medias->count() }} titres actuellement disponibles.</p></div><a href="{{ route('media.create') }}" class="button">＋ Ajouter un média</a></section>

@if($medias->isEmpty())
    <div class="empty-state"><h3>Le catalogue est vide</h3><p>Ajoutez votre premier film ou votre première série.</p><a href="{{ route('media.create') }}" class="button">Ajouter un média</a></div>
@else
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Titre</th><th>Catégorie</th><th>Genres</th><th>Ajout</th><th>Action</th></tr></thead><tbody>
    @foreach($medias as $media)<tr><td><strong>{{ $media->titre }}</strong></td><td>{{ ucfirst($media->categorie->type ?? '—') }}</td><td>{{ $media->genres->pluck('type')->join(', ') ?: '—' }}</td><td>{{ $media->date_ajout ? \Carbon\Carbon::parse($media->date_ajout)->format('d/m/Y') : '—' }}</td><td><div class="table-actions"><a href="{{ route('media.show',$media) }}" class="button button-secondary">Voir</a><form action="{{ route('media.destroy',$media->id_media) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce média ?')">@csrf @method('DELETE')<button type="submit" class="button button-danger">Supprimer</button></form></div></td></tr>@endforeach
    </tbody></table></div>
@endif
@endsection
