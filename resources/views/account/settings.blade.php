@extends('layout')

@section('title', 'Paramètres du compte — WatchList')

@section('content')
<section class="page-header settings-header">
    <p class="eyebrow"><span></span> Mon compte</p>
    <h1>Paramètres et sécurité</h1>
    <p>Gérez vos informations, vos préférences et la protection de votre compte depuis un seul espace.</p>
</section>

<div class="settings-layout">
    <nav class="settings-nav" aria-label="Sections des paramètres">
        <a href="#profil">Informations personnelles</a>
        <a href="#preferences">Préférences</a>
        <a href="#recompenses">Récompenses</a>
        <a href="#securite">Mot de passe</a>
        <a href="#double-authentification">Double authentification</a>
        <a href="#activite">Activité du compte</a>
        <a href="#suppression" class="danger-link">Supprimer le compte</a>
    </nav>

    <div class="settings-content">
        <section class="panel settings-panel" id="profil">
            <div class="panel-heading"><div><span class="settings-icon">◎</span><h2>Informations personnelles</h2></div><p>Ces informations servent à identifier votre compte.</p></div>
            <form action="{{ route('account.profile.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="form-grid">
                    <div class="field"><label for="firstname">Prénom</label><input id="firstname" name="firstname" value="{{ old('firstname', $user->firstname) }}" autocomplete="given-name" required>@error('firstname')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="name">Nom</label><input id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="family-name" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="pseudo">Pseudo public</label><input id="pseudo" name="pseudo" value="{{ old('pseudo', $user->display_name) }}" autocomplete="nickname" required><small class="field-help">Visible dans vos avis et dans le Salon. Une adresse e-mail n’est pas acceptée.</small>@error('pseudo')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="email">Adresse e-mail</label><input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field field-full"><label for="phone">Téléphone <span class="optional">facultatif</span></label><input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+33 6 00 00 00 00" autocomplete="tel">@error('phone')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div>
                <div class="form-actions"><button class="button" type="submit">Enregistrer mes informations</button></div>
            </form>
        </section>

        <section class="panel settings-panel" id="preferences">
            <div class="panel-heading"><div><span class="settings-icon">◈</span><h2>Préférences et confidentialité</h2></div><p>Choisissez ce que les autres membres peuvent voir.</p></div>
            <form action="{{ route('account.preferences.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="field"><label for="profile_visibility">Visibilité du profil</label><select id="profile_visibility" name="profile_visibility"><option value="members" @selected($user->profile_visibility === 'members')>Visible par les membres connectés</option><option value="private" @selected($user->profile_visibility === 'private')>Profil privé</option></select></div>
                <label class="preference-row"><span><strong>Notifications par e-mail</strong><small>Recevoir les informations importantes liées au compte et à votre liste.</small></span><input type="checkbox" name="email_notifications" value="1" @checked($user->email_notifications)></label>
                <div class="form-actions"><button class="button" type="submit">Enregistrer mes préférences</button></div>
            </form>
        </section>

        <section class="panel settings-panel" id="recompenses">
            <div class="panel-heading"><div><span class="settings-icon">🏆</span><h2>Rang et récompenses</h2></div><p>Publiez des avis et enrichissez votre liste pour débloquer des émoticônes.</p></div>
            <div class="rank-summary"><div><small>Votre rang actuel</small><strong>{{ $communityStats['rank']['name'] }}</strong></div><div><small>Activité</small><strong>{{ $communityStats['reviews'] }} avis · {{ $communityStats['watchlist'] }} en liste · {{ $communityStats['messages'] }} messages</strong></div></div>
            @if($communityStats['next'])<p class="rank-next">Encore {{ $communityStats['next']['minimum'] - $communityStats['reviews'] }} avis pour atteindre <strong>{{ $communityStats['next']['name'] }}</strong>.</p>@else<p class="rank-next">Vous avez atteint le rang maximal. Une vraie légende du grand écran !</p>@endif
            <div class="reward-grid">
                @forelse($unlockedRewards as $reward)
                    <article class="reward-card {{ $user->equipped_reward_id === $reward->id ? 'is-equipped' : '' }}"><span>{{ $reward->emoji }}</span><div><strong>{{ $reward->name }}</strong><small>{{ $reward->description }}</small></div><form method="POST" action="{{ route('community.rewards.equip', $reward) }}">@csrf<button class="text-link" type="submit">{{ $user->equipped_reward_id === $reward->id ? 'Équipée' : 'Afficher' }}</button></form></article>
                @empty<p class="muted-copy">Ajoutez un film ou publiez un avis pour débloquer votre première récompense.</p>@endforelse
            </div>
            @if($user->equipped_reward_id)<form class="reward-reset" method="POST" action="{{ route('community.rewards.unequip') }}">@csrf @method('DELETE')<button type="submit" class="text-link">Ne plus afficher d’émoticône</button></form>@endif
        </section>

        <section class="panel settings-panel" id="securite">
            <div class="panel-heading"><div><span class="settings-icon">◆</span><h2>Modifier le mot de passe</h2></div><p>Utilisez au moins 10 caractères, avec majuscule, chiffre et symbole.</p></div>
            <form action="{{ route('account.password.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="form-grid">
                    <div class="field field-full"><label for="current_password">Mot de passe actuel</label><input type="password" id="current_password" name="current_password" autocomplete="current-password" required>@error('current_password')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="password">Nouveau mot de passe</label><input type="password" id="password" name="password" autocomplete="new-password" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="password_confirmation">Confirmer</label><input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required></div>
                </div>
                <div class="form-actions"><button class="button" type="submit">Changer mon mot de passe</button></div>
            </form>
        </section>

        <section class="panel settings-panel" id="double-authentification">
            <div class="panel-heading"><div><span class="settings-icon">✦</span><h2>Double authentification</h2></div><p>Un code temporaire sera demandé après votre mot de passe.</p></div>
            <div class="security-status {{ $user->two_factor_enabled ? 'is-enabled' : '' }}">
                <span></span><div><strong>{{ $user->two_factor_enabled ? 'Protection activée' : 'Protection non activée' }}</strong><small>@if($user->two_factor_enabled) Code envoyé par e-mail à chaque connexion.@else Activez-la pour mieux protéger votre compte.@endif</small></div>
            </div>

            @if(!$user->two_factor_enabled)
                <form action="{{ route('account.two-factor.start') }}" method="POST">
                    @csrf
                    <div class="two-factor-options">
                        <input type="hidden" name="method" value="email"><p>Le code sera envoyé à {{ $user->email }}.</p>
                    </div>
                    <div class="form-grid compact-grid">
                        <div class="field"><label for="two_factor_password">Mot de passe actuel</label><input type="password" id="two_factor_password" name="current_password" autocomplete="current-password" required></div>
                    </div>
                    @error('method')<p class="field-error">{{ $message }}</p>@enderror
                    <div class="form-actions"><button class="button" type="submit">Recevoir mon code d’activation</button></div>
                </form>

                @if(session('two_factor_setup') || $user->two_factor_code)
                    <form class="verification-box" action="{{ route('account.two-factor.confirm') }}" method="POST">
                        @csrf
                        <div><strong>Confirmer l’activation</strong><p>Saisissez le code à 6 chiffres reçu. Il reste valable 10 minutes.</p></div>
                        <div class="verification-action"><input name="code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="000000" aria-label="Code de confirmation" required><button class="button button-small" type="submit">Valider</button></div>
                        @error('code')<p class="field-error">{{ $message }}</p>@enderror
                    </form>
                @endif
            @else
                <form action="{{ route('account.two-factor.disable') }}" method="POST" class="inline-security-form">
                    @csrf @method('DELETE')
                    <div class="field"><label for="disable_password">Mot de passe actuel</label><input type="password" id="disable_password" name="current_password" autocomplete="current-password" required></div>
                    <button class="button button-secondary" type="submit">Désactiver la double authentification</button>
                </form>
            @endif
        </section>

        <section class="panel settings-panel" id="activite">
            <div class="panel-heading"><div><span class="settings-icon">◷</span><h2>Activité du compte</h2></div><p>Quelques repères utiles pour détecter une activité inhabituelle.</p></div>
            <dl class="account-facts">
                <div><dt>Compte créé</dt><dd>{{ $user->created_at?->translatedFormat('d F Y') ?: 'Non renseigné' }}</dd></div>
                <div><dt>Dernière connexion</dt><dd>{{ $user->last_login_at?->diffForHumans() ?: 'Première session enregistrée' }}</dd></div>
                <div><dt>Dernière adresse IP</dt><dd>{{ $user->last_login_ip ?: 'Non enregistrée' }}</dd></div>
                <div><dt>Rôle</dt><dd>{{ $user->role_label }}</dd></div>
            </dl>
        </section>

        <section class="panel settings-panel danger-panel" id="suppression">
            <div class="panel-heading"><div><span class="settings-icon">!</span><h2>Supprimer mon compte</h2></div><p>Cette action supprime définitivement votre compte, votre liste et vos avis.</p></div>
            <form action="{{ route('account.destroy') }}" method="POST" onsubmit="return confirm('Supprimer définitivement votre compte et toutes vos données ?')">
                @csrf @method('DELETE')
                <div class="form-grid compact-grid">
                    <div class="field"><label for="delete_password">Mot de passe actuel</label><input type="password" id="delete_password" name="current_password" required></div>
                    <div class="field"><label for="confirmation">Écrivez SUPPRIMER</label><input id="confirmation" name="confirmation" placeholder="SUPPRIMER" required></div>
                </div>
                <div class="form-actions"><button class="button button-danger" type="submit">Supprimer définitivement mon compte</button></div>
            </form>
        </section>
    </div>
</div>
@endsection
