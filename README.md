# WatchList

[Read this documentation in English](README.en.md)

Application web open source développée par Clément Musset dans le cadre du BTS SIO, option SLAM. WatchList combine un catalogue de films et séries, une liste privée et un espace communautaire administrable.

Le catalogue n'est pas recopié dans la base : les données sont consultées via TMDB et mises en cache. La base conserve les interactions propres à WatchList et les références aux œuvres utilisées dans l'application.

[Installation](#installation-rapide) · [Architecture](#architecture-du-catalogue) · [Base de données](#base-de-données) · [Tests](#tests) · [English](README.en.md)

## Fonctionnalités

- catalogue mondial de films et de séries en français ;
- recherche par titre ;
- filtres Film, Série et genre (horreur, crime, science-fiction, etc.) ;
- fiches détaillées avec synopsis, casting, note, bande-annonce et disponibilité ;
- liste personnelle et avis communautaires ;
- paramètres de compte, confidentialité et modification du mot de passe ;
- double authentification par e-mail ;
- backoffice avec statistiques, gestion des membres, sélections éditoriales TMDB, signalements et historique de modération ;
- rangs communautaires, émoticônes à débloquer et Salon de discussion modéré ;
- interface responsive ;
- compatible MariaDB/MySQL et adapté à un hébergement PHP classique.

## Prérequis

- PHP 8.2 ou supérieur, avec `pdo_mysql` pour MariaDB/MySQL et `pdo_sqlite` pour les tests ;
- Composer 2 ;
- Node.js 20 ou supérieur ;
- MariaDB 10.6+ ou MySQL 8 ;
- un jeton de lecture TMDB gratuit.

## Installation rapide

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Créez une base vide, renseignez les variables `DB_*` dans `.env`, puis importez la structure :

Conservez `DB_TABLE_PREFIX=wl_` : les tables du schéma initial portent ce préfixe. L'import SQL installe la structure historique ; les migrations ajoutent les évolutions et le seeder remplit les référentiels, sans créer de compte.

```bash
mysql -u root -p nom_de_la_base < database/structure_WL.sql
php artisan migrate --force
php artisan db:seed --force
npm run build
```

Sur Windows, copiez manuellement `.env.example` vers `.env` si la commande `cp` n'est pas disponible.

Exécutez ces commandes depuis la racine du projet. Sous PowerShell, ouvrez le client MySQL puis utilisez `SOURCE chemin/vers/database/structure_WL.sql;` après avoir sélectionné la base. Ne réimportez pas le schéma sur une base déjà utilisée.

## Se repérer dans le code

- `routes/web.php` : points d'entrée et accès réservés aux membres et à l'administration.
- `app/Http/Controllers/` : validation des demandes et préparation des pages.
- `app/Services/` : logique réutilisable, notamment les appels à TMDB.
- `app/Models/` et `database/` : données locales et historique des évolutions du schéma.
- `resources/` : vues Blade, styles et interactions dans le navigateur.

`php artisan test` utilise SQLite en mémoire (extension `pdo_sqlite`). Ne publiez jamais `.env`, une sauvegarde, un journal ou des fichiers importés. En production, désactivez `APP_DEBUG` et utilisez HTTPS.

## Activer le catalogue TMDB

1. Créez un compte sur [TMDB](https://www.themoviedb.org/).
2. Demandez une clé dans les paramètres **API** du compte.
3. Copiez le **jeton d'accès en lecture à l'API** dans `.env` :

```ini
TMDB_TOKEN=votre_jeton_de_lecture
TMDB_LANGUAGE=fr-FR
TMDB_REGION=FR
```

Le jeton reste côté serveur et n'est jamais envoyé au navigateur. Après modification :

```bash
php artisan optimize:clear
```

## Double authentification

L’envoi par e-mail utilise la configuration `MAIL_*` de Laravel. En développement, `MAIL_MAILER=log` écrit le code dans `storage/logs/laravel.log` sans envoyer de message réel.

Pour activer l’envoi réel, renseignez dans le fichier `.env` local :

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=utilisateur_smtp
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=watchlist@example.com
```

Utilisez les paramètres de votre fournisseur et une adresse d’expédition autorisée. Le port 587 est un exemple ; pour TLS implicite sur le port 465, utilisez `MAIL_SCHEME=smtps` si le fournisseur le demande. Ne publiez pas vos identifiants. Rechargez la configuration avec `php artisan config:clear`, puis testez avec `php artisan watchlist:test-smtp destinataire@example.com`. Cette commande n’active pas la double authentification.


## Communauté, rangs et récompenses

- Les avis et le Salon utilisent uniquement le **pseudo public**. Une adresse e-mail ne peut pas servir de pseudo.
- Les rangs progressent avec le nombre d’avis : Spectateur curieux, Critique en herbe, Cinéphile confirmé, Expert du grand écran et Légende WatchList.
- Les émoticônes sont débloquées grâce aux avis ou au nombre d’œuvres conservées dans la liste. Le super administrateur peut les créer, les modifier ou les désactiver dans **Administration > Récompenses**.
- Le **Salon** est réservé aux membres connectés. Un clic droit, ou le bouton `•••` sur mobile, permet de répondre à un message, de réagir avec une émoticône débloquée et de supprimer son propre message.
- Un membre peut signaler le message ou l'avis d'un autre membre en indiquant un motif et des précisions. Les doublons en attente sont bloqués.
- Les modérateurs retrouvent les signalements dans **Administration > Modération**, peuvent les traiter ou les classer sans suite, et doivent laisser une conclusion.
- Les suppressions, sanctions et décisions sur les signalements sont conservées dans un historique de modération horodaté.
- Les gestionnaires et super administrateurs peuvent rendre un membre muet pendant 10 minutes, 1 heure, 24 heures ou 7 jours, le bannir définitivement, rétablir son accès et supprimer ses messages.
- De nouvelles réactions sont débloquées après 5, 10, 20 et 30 messages. Les seuils peuvent aussi dépendre des avis et des films ou séries conservés dans la liste.
- Le Salon limite les envois et refuse une liste configurable de termes interdits.
- Les termes bloqués peuvent être complétés dans `config/community.php`. Pour un déploiement public très fréquenté, un service de modération spécialisé peut compléter la file de signalements intégrée.

## Rôles et administration

- rôle `1` : super administrateur, avec gestion des membres ;
- rôle `3` : gestionnaire du catalogue et des avis ;
- rôle `2` : membre standard.

Le backoffice est disponible sur `/admin` pour les rôles autorisés. Aucun compte n’est fourni. Après avoir créé votre compte via l’interface, ouvrez `php artisan tinker` depuis votre propre serveur et exécutez `App\\Models\\User::where('email', 'votre-adresse@example.com')->update(['id_role' => 1]);`. Ne faites cela que sur votre installation.

Une base neuve ne contient que les rôles, catégories et récompenses, en plus du registre des migrations. Les comptes, listes, avis et mises en avant restent vides.

## Développement local

Dans un premier terminal :

```bash
php artisan serve
```

Dans un second terminal :

```bash
npm run dev
```

Le site est disponible sur `http://127.0.0.1:8000`.

## Déploiement

Le projet fonctionne sur un hébergement PHP classique. Le dossier public du domaine doit pointer vers `public/`.

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

Configurez ensuite une tâche planifiée exécutant `php artisan schedule:run` toutes les minutes si vous ajoutez ultérieurement des tâches programmées.

## Architecture du catalogue

```text
Navigateur (Blade, CSS et JavaScript compilés par Vite)
  → Routes et contrôleurs Laravel
      ├── TmdbService → cache Laravel → API TMDB
      └── Modèles Eloquent → MariaDB/MySQL
```

Laravel 12 gère les routes, les sessions et les accès. Blade rend les pages côté serveur ; Vite compile les styles et le JavaScript, avec Tailwind CSS 4 parmi les dépendances front-end.

`TmdbService` normalise les réponses en distinguant films et séries. La recherche est mise en cache 30 minutes, les fiches 6 heures et les genres 24 heures. Le jeton TMDB reste côté serveur. Les notes publiques et avis sont ceux des membres WatchList, pas les notes ou avis de TMDB.

## Base de données

La structure de départ est dans [`database/structure_WL.sql`](database/structure_WL.sql) ; les [migrations](database/migrations) documentent les évolutions. Dans le tableau, les noms logiques sont indiqués sans le préfixe `wl_`.

| Relation | Cardinalité / rôle |
| --- | --- |
| Rôle → compte | **1:N**. Le rôle détermine l'accès aux fonctions membre ou administratives. |
| Liste ↔ média | **N:N** via `Composer`. Le média porte la référence au titre externe lorsque celle-ci est disponible. |
| Compte → avis ← média | **1:N** de chaque côté. L'avis associe un membre à un titre. |
| Message → réponses | **1:N**. Une réponse peut référencer un message du Salon ; les réactions sont stockées séparément. |
| Signalement et historique | Les signalements et les décisions de modération sont stockés dans des tables distinctes. |

## Tests

```bash
php artisan test
```

PHPUnit utilise SQLite en mémoire, une session et un cache temporaires, et un transport mail en mémoire. Les tests couvrent notamment l'accès aux espaces privés, l'expiration et la non-réutilisation des codes de connexion, les doublons dans une liste et le traitement unique d'un signalement. Ils ne remplacent pas une vérification de MariaDB, de TMDB et du SMTP dans votre environnement de déploiement.

## Attribution

Ce produit utilise l'API TMDB mais n'est ni approuvé ni certifié par TMDB. Les informations de disponibilité sont fournies par JustWatch via TMDB.

## Licence

Le code de WatchList est distribué sous licence MIT. Les données, textes et images provenant de TMDB restent soumis aux conditions de TMDB et de leurs ayants droit.
