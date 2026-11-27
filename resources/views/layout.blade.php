<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WatchList, votre espace pour découvrir, noter et conserver vos films et séries préférés.">
    <meta name="theme-color" content="#080b14">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WatchList — Films & séries')</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>
<body>
    <header class="site-header">
        <div class="nav-shell">
            <a href="{{ route('welcome') }}" class="brand" aria-label="WatchList, retour à l'accueil">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 40 40" role="img"><path d="M16 11.5v17l14-8.5-14-8.5Z" fill="currentColor"/><path d="M20 3.5a16.5 16.5 0 1 0 0 33 16.5 16.5 0 0 0 0-33Z" fill="none" stroke="currentColor" stroke-width="2.5"/></svg>
                </span>
                <span>Watch<span>List</span></span>
            </a>

            <button class="menu-toggle" type="button" aria-controls="main-navigation" aria-expanded="false" data-menu-toggle>
                <span class="sr-only">Ouvrir le menu</span>
                <span></span><span></span><span></span>
            </button>

            <nav id="main-navigation" class="main-nav" aria-label="Navigation principale" data-menu>
                <a href="{{ route('welcome') }}" class="nav-link {{ request()->routeIs('welcome') ? 'is-active' : '' }}">Découvrir</a>
                @auth
                    <a href="{{ route('liste-privee.index') }}" class="nav-link {{ request()->routeIs('liste-privee.*') ? 'is-active' : '' }}">Ma liste</a>
                    @if(in_array((int) auth()->user()->id_role, [1, 3], true))<a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.*') || request()->routeIs('users.*') || request()->routeIs('media.index') || request()->routeIs('media.create') ? 'is-active' : '' }}">Administration</a>@endif
                    <a href="{{ route('account.settings') }}" class="nav-link {{ request()->routeIs('account.*') ? 'is-active' : '' }}">Mon compte</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link nav-link-muted">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login*') ? 'is-active' : '' }}">Connexion</a>
                    <a href="{{ route('register') }}" class="button button-small">Créer un compte</a>
                @endauth
            </nav>
        </div>
    </header>

    @if(session('success') || session('error'))
        <div class="flash-wrap" role="status">
            @if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="flash flash-error">{{ session('error') }}</div>@endif
        </div>
    @endif

    <main class="@yield('mainClass', 'site-main')">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-shell">
            <div>
                <a href="{{ route('welcome') }}" class="brand brand-footer">
                    <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 40 40"><path d="M16 11.5v17l14-8.5-14-8.5Z" fill="currentColor"/><path d="M20 3.5a16.5 16.5 0 1 0 0 33 16.5 16.5 0 0 0 0-33Z" fill="none" stroke="currentColor" stroke-width="2.5"/></svg></span>
                    <span>Watch<span>List</span></span>
                </a>
                <p>Le bon film, au bon moment.</p>
            </div>
            <div class="footer-meta">
                <span>Projet Laravel réalisé par Clément Musset</span>
                <span>Données et images : <a href="https://www.themoviedb.org" target="_blank" rel="noopener">TMDB</a></span>
                <small>Ce produit utilise l’API TMDB mais n’est ni approuvé ni certifié par TMDB.</small>
                <span>© <span data-current-year>{{ date('Y') }}</span> WatchList</span>
            </div>
        </div>
    </footer>
    @auth @include('partials.chat-widget') @include('partials.report-dialog') @endauth
    @stack('scripts')
</body>
</html>
