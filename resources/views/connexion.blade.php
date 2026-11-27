@extends('layout')

@section('title', 'Connexion — WatchList')

@section('content')
<div class="auth-page">
    <section class="auth-story">
        <p class="eyebrow"><span></span> Heureux de vous revoir</p>
        <h1>Votre prochaine séance <span>vous attend.</span></h1>
        <p>Retrouvez votre sélection personnelle, ajoutez de nouveaux titres et partagez vos dernières découvertes.</p>
        <div class="auth-points">
            <span><i>✓</i> Votre liste accessible à tout moment</span>
            <span><i>✓</i> Vos notes et avis réunis au même endroit</span>
            <span><i>✓</i> Une expérience simple sur tous vos écrans</span>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-header">
            <h2>Se connecter</h2>
            <p>Entrez vos identifiants pour continuer.</p>
        </div>
        <form action="{{ route('login.attempt') }}" method="post">
            @csrf
            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" placeholder="vous@exemple.fr" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <label class="checkbox"><input type="checkbox" name="remember" value="1"> Rester connecté sur cet appareil</label>
            <button type="submit" class="button button-block">Accéder à mon espace</button>
        </form>
        <p class="auth-footer">Pas encore de compte ? <a href="{{ route('register') }}">Créer mon compte</a></p>
    </section>
</div>
@endsection
