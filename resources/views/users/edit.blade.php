@extends('layout')
@section('title', 'Modifier un membre — WatchList')
@section('content')
<a href="{{ route('users.index') }}" class="back-link">← Retour aux membres</a>
<section class="page-header"><p class="eyebrow"><span></span> Administration</p><h1>Modifier {{ $user->pseudo }}</h1><p>Mettez à jour ses informations ou son niveau d’accès.</p></section>
<div class="panel"><form action="{{ route('users.update',$user) }}" method="POST">@csrf @method('PUT')<div class="form-grid">
<div class="field"><label for="firstname">Prénom</label><input id="firstname" name="firstname" value="{{ old('firstname',$user->firstname) }}" required></div>
<div class="field"><label for="name">Nom</label><input id="name" name="name" value="{{ old('name',$user->name) }}" required></div>
<div class="field"><label for="pseudo">Pseudo</label><input id="pseudo" name="pseudo" value="{{ old('pseudo',$user->pseudo) }}" required></div>
<div class="field"><label for="email">E-mail</label><input type="email" id="email" name="email" value="{{ old('email',$user->email) }}" required></div>
<div class="field"><label for="phone">Téléphone</label><input type="tel" id="phone" name="phone" value="{{ old('phone',$user->phone) }}" placeholder="+33 6 00 00 00 00"></div>
<div class="field field-full"><label for="role">Rôle</label><select id="role" name="role" required><option value="2" @selected($user->id_role==2)>Membre</option><option value="3" @selected($user->id_role==3)>Gestionnaire</option><option value="1" @selected($user->id_role==1)>Super administrateur</option></select></div>
</div>@if($errors->any())<p class="field-error">{{ $errors->first() }}</p>@endif<button type="submit" class="button">Enregistrer les modifications</button></form></div>
@endsection
