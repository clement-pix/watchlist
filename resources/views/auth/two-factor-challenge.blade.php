@extends('layout')

@section('title', 'Vérification de sécurité — WatchList')

@section('content')
<div class="auth-page two-factor-page">
    <section class="auth-story">
        <p class="eyebrow"><span></span> Connexion sécurisée</p>
        <h1>Une dernière <span>vérification.</span></h1>
        <p>Votre mot de passe est correct. Confirmez maintenant que c’est bien vous avec le code temporaire envoyé par e-mail.</p>
    </section>
    <section class="auth-card">
        <div class="auth-card-header"><h2>Code à 6 chiffres</h2><p>Le code expire après 10 minutes et après plusieurs essais incorrects.</p></div>
        <form action="{{ route('two-factor.verify') }}" method="POST">
            @csrf
            <div class="field"><label for="code">Code de sécurité</label><input class="code-input" id="code" name="code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="000000" autocomplete="one-time-code" required autofocus>@error('code')<p class="field-error">{{ $message }}</p>@enderror</div>
            <button class="button button-block" type="submit">Confirmer la connexion</button>
        </form>
        <form action="{{ route('two-factor.resend') }}" method="POST" class="resend-form">@csrf<button type="submit" class="text-link">Renvoyer un nouveau code <span>→</span></button></form>
    </section>
</div>
@endsection
