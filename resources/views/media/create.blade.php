@extends('layout')
@section('title', 'Ajouter un média — WatchList')
@section('content')
<a href="{{ route('dashboard') }}" class="back-link">← Retour à mon espace</a>
<section class="page-header"><p class="eyebrow"><span></span> Gestion du catalogue</p><h1>Ajouter un média</h1><p>Complétez la fiche avec des informations utiles et une affiche nette au format vertical.</p></section>

<div class="panel">
    <form action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div class="field field-full"><label for="titre">Titre</label><input type="text" id="titre" name="titre" value="{{ old('titre') }}" maxlength="50" required>@error('titre')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="id_categorie">Catégorie</label><select id="id_categorie" name="id_categorie" required><option value="">Sélectionner</option>@foreach($categories as $categorie)<option value="{{ $categorie->id_categorie }}" @selected(old('id_categorie') == $categorie->id_categorie)>{{ ucfirst($categorie->type) }}</option>@endforeach</select></div>
            <div class="field"><label for="id_genre">Genre</label><select id="id_genre" name="id_genre" required><option value="">Sélectionner</option>@foreach($genres as $genre)<option value="{{ $genre->id_genre }}" @selected(old('id_genre') == $genre->id_genre)>{{ ucfirst($genre->type) }}</option>@endforeach</select></div>
            <div class="field field-full"><label for="description">Synopsis</label><textarea id="description" name="description" maxlength="1500" required>{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field field-full"><label for="image">Affiche</label><input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" required><p class="form-note">JPG, PNG ou WebP. Une affiche verticale est recommandée.</p>@error('image')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
        <button type="submit" class="button">Ajouter au catalogue</button>
    </form>
</div>
@endsection
