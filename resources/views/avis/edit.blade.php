@extends('layout')
@section('title', 'Modifier mon avis — WatchList')
@section('content')
<a href="{{ route('media.show',$avis->id_media) }}" class="back-link">← Retour à la fiche</a>
<section class="page-header"><p class="eyebrow"><span></span> Votre contribution</p><h1>Modifier mon avis</h1><p>Ajustez votre note ou votre commentaire.</p></section>
<div class="panel"><form action="{{ route('comment.update',$avis->id_media) }}" method="POST">@csrf @method('PUT')
<div class="field"><label for="note">Note sur 5</label><select id="note" name="note" required>@for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected($avis->note==$i)>{{ $i }} étoile{{ $i>1?'s':'' }}</option>@endfor</select></div>
<div class="field"><label for="comment">Commentaire</label><textarea id="comment" name="comment" required>{{ old('comment',$avis->commentaire) }}</textarea></div>
<button type="submit" class="button">Enregistrer mon avis</button></form></div>
@endsection
