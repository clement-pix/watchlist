# WatchList

[Documentation française](README.md)

WatchList is an open-source Laravel 12 application developed by Clément Musset as a BTS SIO coursework project. It combines a movie and TV catalogue, private watchlists, community reviews and a moderation back office. It queries TMDB on demand rather than importing the entire catalogue.

[Setup](#local-setup) · [Architecture](#architecture) · [Data model](#data-model) · [Tests](#tests) · [Français](README.md)

## Local setup

You need PHP 8.2+, Composer 2, Node.js 20+, and a MySQL or MariaDB server. Run these commands from the project root:

```sh
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

On PowerShell use `Copy-Item .env.example .env` if needed. Create an empty database and fill in the `DB_*` settings in `.env`. Import the bundled structure into that empty database only:

```sh
mysql -u root -p your_database < database/structure_WL.sql
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

On PowerShell, use the MySQL client and run `SOURCE path/to/database/structure_WL.sql;` after selecting the database. Open `http://127.0.0.1:8000`. For live frontend updates, run `npm run dev` in a separate terminal.

## Catalogue and accounts

Set `TMDB_TOKEN` to your TMDB API read access token in `.env`, then run `php artisan optimize:clear`. The token stays on the server. The catalogue supports title search, movie or TV filters, genres and watchlist additions.

Email verification codes use Laravel's `MAIL_*` settings. The local log mailer records messages rather than sending them. The admin area is at `/admin`. No account is provided. On your own installation, sign up, open `php artisan tinker`, and run `App\\Models\\User::where('email', 'your-address@example.com')->update(['id_role' => 1]);` to grant that account the administrator role.

A fresh database contains only roles, categories and rewards, plus migration bookkeeping. Accounts, lists, reviews and editorial selections start empty.

For real email delivery, set `MAIL_MAILER=smtp` in your local `.env` and fill in `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` and an authorized `MAIL_FROM_ADDRESS` using your provider's settings. Use `MAIL_SCHEME=smtp` for port 587 or `smtps` if your provider specifies implicit TLS on port 465. Never commit credentials. Run `php artisan config:clear`, then `php artisan watchlist:test-smtp recipient@example.com` to test delivery without changing account settings.

## Architecture

Laravel handles routes, sessions, access control and persistence. Blade renders the pages server-side; Vite builds the CSS and JavaScript. `TmdbService` normalizes movies and TV shows and caches catalogue pages for 30 minutes, details for 6 hours and genres for 24 hours. Public reviews and scores are WatchList member contributions, not TMDB ratings.

## Data model

Import [the initial SQL schema](database/structure_WL.sql), then run the [Laravel migrations](database/migrations). Keep `DB_TABLE_PREFIX=wl_` to match the imported tables. Seeding creates reference data, not accounts.

| Relationship | Design |
| --- | --- |
| Roles → accounts | One-to-many; roles restrict access to administrative features. |
| Watchlists ↔ titles | Many-to-many through the `Composer` table. |
| Accounts → reviews ← titles | A review belongs to one account and one title. |
| Chat messages → replies | A reply may refer to a parent message. Reactions are stored separately. |
| Reports and moderation | Reports and moderation decisions are stored separately. |

## Tests

Run `php artisan test` with the PHP `pdo_sqlite` extension installed. The suite uses an in-memory SQLite database and mail transport. It checks private access, two-factor code expiry and reuse, duplicate watchlist entries, and report resolution. Validate MariaDB, TMDB and SMTP separately in your own deployment.

## Where to start reading

- `routes/web.php` defines URLs and access boundaries.
- `app/Http/Controllers/` validates input and prepares responses.
- `app/Services/` holds shared logic, including TMDB requests.
- `app/Models/` describes the local data; `database/` contains the schema and migrations.
- `resources/` contains Blade views, CSS and browser scripts.

Run `php artisan test` with the `pdo_sqlite` extension; it uses an in-memory database. For deployment, point the web root at `public/`, set `APP_DEBUG=false`, and use HTTPS. Never commit environment files, credentials, logs, uploads or database backups.

## Licence and attribution

The code is MIT-licensed. This product uses the TMDB API but is not endorsed or certified by TMDB. Availability data is supplied by JustWatch through TMDB. Third-party data and images are not covered by the project's code licence.
