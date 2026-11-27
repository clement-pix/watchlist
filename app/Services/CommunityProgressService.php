<?php

namespace App\Services;

use App\Models\Avis;
use App\Models\Composer;
use App\Models\Reward;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Support\Collection;

class CommunityProgressService
{
    private const RANKS = [
        ['minimum' => 0, 'name' => 'Spectateur curieux'],
        ['minimum' => 5, 'name' => 'Critique en herbe'],
        ['minimum' => 10, 'name' => 'Cinéphile confirmé'],
        ['minimum' => 25, 'name' => 'Expert du grand écran'],
        ['minimum' => 50, 'name' => 'Légende WatchList'],
    ];

    public function stats(User $user): array
    {
        $reviews = Avis::where('id', $user->id)->count();
        $watchlist = Composer::where('user_id', $user->id)->distinct()->count('id_media');
        $messages = ChatMessage::where('user_id', $user->id)->count();
        $rank = collect(self::RANKS)->last(fn ($item) => $reviews >= $item['minimum']);
        $next = collect(self::RANKS)->first(fn ($item) => $reviews < $item['minimum']);

        return compact('reviews', 'watchlist', 'messages', 'rank', 'next');
    }

    public function unlocked(User $user, ?array $stats = null): Collection
    {
        $stats ??= $this->stats($user);

        return Reward::where('enabled', true)->orderBy('sort_order')->orderBy('threshold')->get()
            ->filter(fn (Reward $reward) => match ($reward->criterion_type) {
                'reviews' => $stats['reviews'] >= $reward->threshold,
                'messages' => $stats['messages'] >= $reward->threshold,
                default => $stats['watchlist'] >= $reward->threshold,
            })
            ->values();
    }

    public function rankFor(User $user): string
    {
        return $this->stats($user)['rank']['name'];
    }
}
