@extends('layout')

@section('title', 'Créer un compte — WatchList')

@section('content')
<div class="auth-page">
    <section class="auth-story">
        <p class="eyebrow"><span></span> Bienvenue dans WatchList</p>
        <h1>Créez la liste qui vous <span>ressemble.</span></h1>
        <p>Enregistrez les films et séries qui vous donnent envie, puis construisez votre propre bibliothèque de découvertes.</p>
        <div class="auth-points">
            <span><i>✓</i> Inscription gratuite et rapide</span>
            <span><i>✓</i> Sélection personnelle et privée</span>
            <span><i>✓</i> Notes et commentaires communautaires</span>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-header">
            <h2>Créer un compte</h2>
            <p>Quelques informations suffisent pour commencer.</p>
        </div>
        <form action="{{ route('register.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label for="firstname">Prénom</label>
                    <input type="text" id="firstname" name="firstname" value="{{ old('firstname') }}" autocomplete="given-name" required>
                    @error('firstname')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="family-name" required>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field field-full">
                    <label for="pseudo">Nom d’utilisateur</label>
                    <input type="text" id="pseudo" name="pseudo" value="{{ old('pseudo') }}" autocomplete="username" required>
                    @error('pseudo')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field field-full">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field field-full">
                    <label for="email_verif">Confirmer l’adresse e-mail</label>
                    <input type="email" id="email_verif" name="email_verif" value="{{ old('email_verif') }}" autocomplete="email" required>
                    @error('email_verif')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirmation</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                </div>
            </div>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
            <p class="form-note">8 caractères minimum avec majuscule, minuscule, chiffre et symbole.</p>
            <button type="submit" class="button button-block">Créer mon compte</button>
        </form>
        <p class="auth-footer">Déjà membre ? <a href="{{ route('login') }}">Se connecter</a></p>
    </section>
</div>
@endsection
