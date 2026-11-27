<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TmdbService
{
    private const API_URL = 'https://api.themoviedb.org/3';
    private const IMAGE_URL = 'https://image.tmdb.org/t/p';

    public function isConfigured(): bool
    {
        return filled(config('services.tmdb.token'));
    }

    public function browse(array $filters = []): array
    {
        if (! $this->isConfigured()) {
            return ['items' => [], 'genres' => [], 'page' => 1, 'total_pages' => 1];
        }

        $type = in_array($filters['type'] ?? 'all', ['all', 'movie', 'tv'], true)
            ? ($filters['type'] ?? 'all')
            : 'all';
        $page = max(1, min((int) ($filters['page'] ?? 1), 500));
        $query = trim((string) ($filters['q'] ?? ''));
        $genre = (int) ($filters['genre'] ?? 0);

        $cacheKey = 'tmdb.catalogue.'.sha1(json_encode([$type, $page, $query, $genre]));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($type, $page, $query, $genre) {
            $genres = $this->genres();

            if ($query !== '') {
                $response = $this->get('/search/multi', [
                    'query' => $query,
                    'page' => $page,
                    'include_adult' => 'false',
                ]);
                $items = collect($response['results'] ?? [])
                    ->filter(fn (array $item) => in_array($item['media_type'] ?? null, ['movie', 'tv'], true))
                    ->when($type !== 'all', fn ($items) => $items->where('media_type', $type))
                    ->when($genre > 0, fn ($items) => $items->filter(fn ($item) => in_array($genre, $item['genre_ids'] ?? [], true)))
                    ->map(fn (array $item) => $this->normalize($item, $item['media_type'], $genres))
                    ->values();

                return [
                    'items' => $items->all(),
                    'genres' => $genres,
                    'page' => (int) ($response['page'] ?? $page),
                    'total_pages' => min((int) ($response['total_pages'] ?? 1), 500),
                ];
            }

            $types = $type === 'all' ? ['movie', 'tv'] : [$type];
            $responses = [];
            foreach ($types as $mediaType) {
                $params = [
                    'page' => $page,
                    'include_adult' => 'false',
                    'sort_by' => 'popularity.desc',
                    'vote_count.gte' => 20,
                ];
                if ($genre > 0) {
                    $params['with_genres'] = $genre;
                }
                $responses[$mediaType] = $this->get('/discover/'.$mediaType, $params);
            }

            $items = collect($responses)
                ->flatMap(fn (array $response, string $mediaType) => collect($response['results'] ?? [])
                    ->map(fn (array $item) => $this->normalize($item, $mediaType, $genres)))
                ->sortByDesc('popularity')
                ->take(24)
                ->values();

            return [
                'items' => $items->all(),
                'genres' => $genres,
                'page' => $page,
                'total_pages' => min(max(array_map(fn ($response) => (int) ($response['total_pages'] ?? 1), $responses)), 500),
            ];
        });
    }

    public function details(string $type, int $id): array
    {
        $this->guardType($type);

        if (! $this->isConfigured()) {
            throw new RuntimeException('TMDB n’est pas encore configuré.');
        }

        return Cache::remember("tmdb.details.v4.$type.$id", now()->addHours(6), function () use ($type, $id) {
            $item = $this->get("/$type/$id", [
                'append_to_response' => 'credits,videos,watch/providers',
                'include_video_language' => 'fr,en,null',
            ]);
            $genres = collect($item['genres'] ?? [])->pluck('name', 'id')->all();
            $normalized = $this->normalize($item, $type, $genres);
            $normalized['runtime'] = $type === 'movie'
                ? ($item['runtime'] ?? null)
                : Arr::first($item['episode_run_time'] ?? []);
            $normalized['tagline'] = $item['tagline'] ?? null;
            $normalized['status'] = $item['status'] ?? null;
            $normalized['cast'] = collect($item['credits']['cast'] ?? [])->take(8)->map(fn ($person) => [
                'name' => $person['name'],
                'character' => $person['character'] ?? null,
                'photo_url' => $this->image($person['profile_path'] ?? null, 'w185'),
            ])->all();
            $normalized['trailer'] = collect($item['videos']['results'] ?? [])->first(fn ($video) =>
                ($video['site'] ?? '') === 'YouTube' && in_array($video['type'] ?? '', ['Trailer', 'Teaser'], true)
            );
            $normalized['providers'] = $this->normalizeProviders(
                $item['watch/providers']['results']['FR'] ?? null,
                $normalized['title']
            );

            return $normalized;
        });
    }

    public function genres(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        return Cache::remember('tmdb.genres.fr', now()->addDay(), function () {
            $movie = $this->get('/genre/movie/list');
            $tv = $this->get('/genre/tv/list');

            return collect(array_merge($movie['genres'] ?? [], $tv['genres'] ?? []))
                ->unique('id')
                ->sortBy('name')
                ->pluck('name', 'id')
                ->all();
        });
    }

    private function normalize(array $item, string $type, array $genres): array
    {
        $date = $item[$type === 'movie' ? 'release_date' : 'first_air_date'] ?? null;
        $genreIds = $item['genre_ids'] ?? collect($item['genres'] ?? [])->pluck('id')->all();

        return [
            'id' => (int) $item['id'],
            'type' => $type,
            'type_label' => $type === 'movie' ? 'Film' : 'Série',
            'title' => $item[$type === 'movie' ? 'title' : 'name'] ?? 'Sans titre',
            'original_title' => $item[$type === 'movie' ? 'original_title' : 'original_name'] ?? null,
            'overview' => $item['overview'] ?? '',
            'poster_url' => $this->image($item['poster_path'] ?? null, 'w500'),
            'backdrop_url' => $this->image($item['backdrop_path'] ?? null, 'original'),
            'release_date' => $date,
            'year' => $date ? substr($date, 0, 4) : null,
            'rating' => round((float) ($item['vote_average'] ?? 0), 1),
            'vote_count' => (int) ($item['vote_count'] ?? 0),
            'popularity' => (float) ($item['popularity'] ?? 0),
            'genre_ids' => $genreIds,
            'genres' => collect($genreIds)->map(fn ($id) => $genres[$id] ?? null)->filter()->values()->all(),
        ];
    }

    private function image(?string $path, string $size): ?string
    {
        return $path ? self::IMAGE_URL.'/'.$size.$path : null;
    }

    private function normalizeProviders(?array $providers, string $title): array
    {
        if (! $providers) {
            return ['groups' => []];
        }

        $labels = [
            'flatrate' => 'Avec un abonnement',
            'free' => 'Gratuit',
            'ads' => 'Gratuit avec publicité',
            'rent' => 'Location',
            'buy' => 'Achat',
        ];

        $groups = collect($labels)->mapWithKeys(function (string $label, string $key) use ($providers, $title) {
            $items = collect($providers[$key] ?? [])
                ->unique('provider_id')
                ->map(fn (array $provider) => [
                    'id' => (int) $provider['provider_id'],
                    'name' => $provider['provider_name'],
                    'logo_url' => $this->image($provider['logo_path'] ?? null, 'w92'),
                    'url' => $this->providerUrl($provider['provider_name'], $title),
                ])
                ->values()
                ->all();

            return $items ? [$key => ['label' => $label, 'items' => $items]] : [];
        })->all();

        return ['groups' => $groups];
    }

    private function providerUrl(string $providerName, string $title): ?string
    {
        $query = rawurlencode($title);
        $name = mb_strtolower($providerName);

        return match (true) {
            str_contains($name, 'netflix') => "https://www.netflix.com/search?q=$query",
            str_contains($name, 'amazon prime'),
            str_contains($name, 'prime video'),
            str_contains($name, 'amazon video') => "https://www.primevideo.com/search/ref=atv_nb_sr?phrase=$query",
            str_contains($name, 'canal') => "https://www.canalplus.com/recherche?q=$query",
            str_contains($name, 'apple tv') => "https://tv.apple.com/fr/search?term=$query",
            str_contains($name, 'youtube') => "https://www.youtube.com/results?search_query=$query",
            str_contains($name, 'google play') => "https://play.google.com/store/search?q=$query&c=movies",
            str_contains($name, 'rakuten') => "https://www.rakuten.tv/fr/search?q=$query",
            str_contains($name, 'crunchyroll') => "https://www.crunchyroll.com/fr/search?q=$query",
            str_contains($name, 'disney') => 'https://www.disneyplus.com/fr-fr/search',
            $name === 'max' || str_contains($name, 'hbo max') => 'https://www.max.com/fr/fr',
            str_contains($name, 'paramount') => 'https://www.paramountplus.com/fr/',
            str_contains($name, 'arte vod') => 'https://boutique.arte.tv/',
            str_contains($name, 'arte') => 'https://www.arte.tv/fr/search/',
            str_contains($name, 'orange') => 'https://video-a-la-demande.orange.fr/',
            str_contains($name, 'pathé'), str_contains($name, 'pathe') => 'https://home.pathe.fr/',
            str_contains($name, 'sooner') => 'https://www.sooner.be/fr',
            str_contains($name, 'premiere max'), str_contains($name, 'première max') => 'https://www.premieremax.com/',
            str_contains($name, 'videofutur'), str_contains($name, 'viva') => 'https://www.videofutur.fr/',
            str_contains($name, 'universciné'), str_contains($name, 'universcine') => 'https://www.universcine.com/',
            str_contains($name, 'filmo') => 'https://www.filmo.tv/',
            str_contains($name, 'lacinetek') => 'https://www.lacinetek.com/fr',
            str_contains($name, 'mubi') => 'https://mubi.com/fr',
            str_contains($name, 'shadowz') => 'https://www.shadowz.fr/',
            str_contains($name, 'plex') => 'https://watch.plex.tv/fr',
            str_contains($name, 'pluto') => 'https://pluto.tv/fr/',
            str_contains($name, 'molotov') => 'https://www.molotov.tv/',
            str_contains($name, 'france tv') => 'https://www.france.tv/',
            str_contains($name, 'tf1') => 'https://www.tf1.fr/',
            str_contains($name, 'm6') => 'https://www.m6.fr/',
            str_contains($name, 'animation digital network'), $name === 'adn' => 'https://animationdigitalnetwork.com/',
            str_contains($name, 'ocs') => 'https://www.canalplus.com/chaines/ocs',
            default => null,
        };
    }

    private function get(string $path, array $query = []): array
    {
        $response = $this->client()->get(self::API_URL.$path, array_merge([
            'language' => config('services.tmdb.language', 'fr-FR'),
            'region' => config('services.tmdb.region', 'FR'),
        ], $query));

        if (! $response->successful()) {
            throw new RuntimeException('Le catalogue TMDB est momentanément indisponible.');
        }

        return $response->json();
    }

    private function client(): PendingRequest
    {
        $client = Http::acceptJson()
            ->withToken(config('services.tmdb.token'))
            ->timeout(8)
            ->retry(2, 250, throw: false);

        $caBundle = config('services.tmdb.ca_bundle');
        if (filled($caBundle)) {
            $caBundle = preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/', $caBundle)
                ? $caBundle
                : base_path($caBundle);

            if (is_file($caBundle)) {
                $client->withOptions(['verify' => $caBundle]);
            }
        }

        return $client;
    }

    private function guardType(string $type): void
    {
        if (! in_array($type, ['movie', 'tv'], true)) {
            abort(404);
        }
    }
}
