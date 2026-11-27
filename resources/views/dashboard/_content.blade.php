<section class="page-header">
    <p class="eyebrow"><span></span> Votre espace</p>
    <h1>Bonjour {{ auth()->user()->firstname }}.</h1>
    <p>Retrouvez votre sélection et gérez les contenus auxquels votre rôle vous donne accès.</p>
</section>

<div class="dashboard-grid">
    <a href="{{ route('account.settings') }}" class="action-card">
        <span class="action-card-icon">◎</span>
        <div><h2>Mon compte</h2><p>Modifiez vos informations, préférences et réglages de sécurité.</p></div>
        <strong>Paramètres →</strong>
    </a>

    <a href="{{ route('liste-privee.index') }}" class="action-card">
        <span class="action-card-icon">♥</span>
        <div><h2>Ma liste personnelle</h2><p>Retrouvez les films et séries que vous souhaitez voir.</p></div>
        <strong>Ouvrir →</strong>
    </a>

    @if(in_array((int) auth()->user()->id_role, [1, 3], true))
        <a href="{{ route('admin.index') }}" class="action-card">
            <span class="action-card-icon">▤</span>
            <div><h2>Backoffice</h2><p>Consultez les statistiques et administrez le site.</p></div>
            <strong>Piloter →</strong>
        </a>
        <a href="{{ route('admin.featured.index') }}" class="action-card">
            <span class="action-card-icon">★</span>
            <div><h2>Mises en avant</h2><p>Choisissez la sélection de la semaine, les nouveautés et les recommandations.</p></div>
            <strong>Composer →</strong>
        </a>
    @endif

    @if((int) auth()->user()->id_role === 1)
        <a href="{{ route('users.index') }}" class="action-card">
            <span class="action-card-icon">◎</span>
            <div><h2>Gérer les membres</h2><p>Modifiez les informations et les rôles des utilisateurs.</p></div>
            <strong>Administrer →</strong>
        </a>
    @endif
</div>
