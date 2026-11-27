<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatMessageReaction;
use App\Models\Reward;
use App\Models\User;
use App\Services\CommunityProgressService;
use App\Services\ModerationLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    public function messages(Request $request, CommunityProgressService $progress)
    {
        $viewer = $request->user();
        $canModerate = in_array((int) $viewer->id_role, [1, 3], true);
        $messages = ChatMessage::with(['user.equippedReward', 'replyTo.user', 'reactions.reward'])
            ->latest()->take(50)->get()->reverse()->values();
        $unlocked = $progress->unlocked($viewer)->map(fn (Reward $reward) => [
            'id' => $reward->id, 'emoji' => $reward->emoji, 'name' => $reward->name,
        ])->values();

        return response()->json([
            'unlocked_reactions' => $unlocked,
            'messages' => $messages->map(function (ChatMessage $message) use ($viewer, $canModerate, $progress) {
                $author = $message->user;
                return [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'author' => $author?->display_name ?? 'Compte supprimé',
                    'emoji' => $author?->equippedReward?->emoji,
                    'rank' => $author ? $progress->rankFor($author) : null,
                    'time' => $message->created_at?->diffForHumans(),
                    'mine' => $message->user_id === $viewer->id,
                    'can_delete' => $message->user_id === $viewer->id || $canModerate,
                    'can_moderate' => $canModerate,
                    'can_sanction' => $canModerate && $author && (int) $author->id_role !== 1 && $author->id !== $viewer->id,
                    'is_suspended' => (bool) ($author?->chat_banned_permanently || $author?->chat_banned_until?->isFuture()),
                    'delete_url' => route('chat.destroy', $message),
                    'report_url' => route('reports.messages.store', $message),
                    'mute_url' => $author ? route('admin.chat.suspend', $author) : null,
                    'ban_url' => $author ? route('admin.chat.ban', $author) : null,
                    'unban_url' => $author ? route('admin.chat.unsuspend', $author) : null,
                    'reply' => $message->replyTo ? [
                        'author' => $message->replyTo->trashed() ? 'Message supprimé' : ($message->replyTo->user?->display_name ?? 'Compte supprimé'),
                        'message' => $message->replyTo->trashed() ? 'Ce message n’est plus disponible.' : Str::limit($message->replyTo->message, 90),
                    ] : null,
                    'reactions' => $message->reactions->groupBy('reward_id')->map(function ($items) use ($viewer) {
                        $first = $items->first();
                        return [
                            'reward_id' => $first->reward_id,
                            'emoji' => $first->reward?->emoji,
                            'name' => $first->reward?->name,
                            'count' => $items->count(),
                            'mine' => $items->contains('user_id', $viewer->id),
                        ];
                    })->values(),
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->chat_banned_permanently) {
            return response()->json(['message' => 'Vous êtes banni du Salon. Contactez un administrateur si vous pensez qu’il s’agit d’une erreur.'], 403);
        }
        if ($user->chat_banned_until?->isFuture()) {
            return response()->json(['message' => 'Vous êtes muet jusqu’au '.$user->chat_banned_until->format('d/m/Y H:i').'.'], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:500'],
            'reply_to_id' => ['nullable', 'integer', Rule::exists('chat_messages', 'id')->whereNull('deleted_at')],
        ]);
        $normalised = Str::lower(Str::ascii($validated['message']));
        foreach (config('community.blocked_terms', []) as $term) {
            $blocked = preg_quote(Str::lower(Str::ascii($term)), '/');
            if (preg_match('/(?<![a-z0-9])'.$blocked.'(?![a-z0-9])/u', $normalised)) {
                return response()->json(['message' => 'Ce message contient un terme non autorisé. Reformulez-le pour participer au salon.'], 422);
            }
        }

        ChatMessage::create([
            'user_id' => $user->id,
            'reply_to_id' => $validated['reply_to_id'] ?? null,
            'message' => trim($validated['message']),
        ]);
        return response()->json(['message' => 'Message publié.'], 201);
    }

    public function destroy(Request $request, ChatMessage $message, ModerationLogService $logs)
    {
        $isModerator = in_array((int) $request->user()->id_role, [1, 3], true);
        abort_unless($isModerator || $message->user_id === $request->user()->id, 403);
        $message->update([
            'moderated_by' => $isModerator && $message->user_id !== $request->user()->id ? $request->user()->id : null,
            'moderation_reason' => $isModerator ? 'Message retiré par la modération' : 'Message supprimé par son auteur',
        ]);
        $message->delete();
        if ($isModerator && $message->user_id !== $request->user()->id) {
            $logs->record($request->user(), 'message_deleted', $message->user, 'chat_message', $message->id, 'Message retiré par la modération', ['snapshot' => $message->message]);
        }
        return response()->json(['message' => 'Message supprimé.']);
    }

    public function react(Request $request, ChatMessage $message, Reward $reward, CommunityProgressService $progress)
    {
        abort_unless($reward->enabled && $progress->unlocked($request->user())->contains('id', $reward->id), 403);
        $existing = ChatMessageReaction::where([
            'chat_message_id' => $message->id, 'user_id' => $request->user()->id, 'reward_id' => $reward->id,
        ])->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['message' => 'Réaction retirée.']);
        }
        ChatMessageReaction::create([
            'chat_message_id' => $message->id, 'user_id' => $request->user()->id, 'reward_id' => $reward->id,
        ]);
        return response()->json(['message' => 'Réaction ajoutée.'], 201);
    }

    public function suspend(Request $request, User $user, ModerationLogService $logs)
    {
        $this->ensureCanSanction($request, $user);
        $validated = $request->validate([
            'duration' => ['required', 'integer', 'in:10,60,1440,10080'],
            'reason' => ['nullable', 'string', 'max:180'],
        ]);
        $user->update([
            'chat_banned_until' => now()->addMinutes((int) $validated['duration']),
            'chat_banned_permanently' => false,
            'chat_ban_reason' => $validated['reason'] ?: 'Sanction appliquée depuis le Salon',
        ]);
        $logs->record($request->user(), 'user_muted', $user, 'user', $user->id, $validated['reason'] ?: 'Sanction appliquée depuis le Salon', ['duration_minutes' => (int) $validated['duration']]);
        return $this->moderationResponse($request, $user->display_name.' est muet temporairement.');
    }

    public function ban(Request $request, User $user, ModerationLogService $logs)
    {
        $this->ensureCanSanction($request, $user);
        $user->update([
            'chat_banned_until' => null,
            'chat_banned_permanently' => true,
            'chat_ban_reason' => 'Bannissement permanent du Salon',
        ]);
        $logs->record($request->user(), 'user_banned', $user, 'user', $user->id, 'Bannissement permanent du Salon');
        return $this->moderationResponse($request, $user->display_name.' est banni du Salon.');
    }

    public function unsuspend(Request $request, User $user, ModerationLogService $logs)
    {
        $user->update(['chat_banned_until' => null, 'chat_banned_permanently' => false, 'chat_ban_reason' => null]);
        $logs->record($request->user(), 'user_unbanned', $user, 'user', $user->id, 'Accès au Salon rétabli');
        return $this->moderationResponse($request, 'L’accès au Salon a été rétabli.');
    }

    private function ensureCanSanction(Request $request, User $user): void
    {
        abort_if((int) $user->id_role === 1 || $user->id === $request->user()->id, 403);
    }

    private function moderationResponse(Request $request, string $message)
    {
        return $request->expectsJson() ? response()->json(['message' => $message]) : back()->with('success', $message);
    }
}
