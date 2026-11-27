<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\ChatMessage;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function message(Request $request, ChatMessage $message)
    {
        abort_if($message->user_id === $request->user()->id, 403, 'Vous ne pouvez pas signaler votre propre message.');

        return $this->create($request, 'chat_message', (string) $message->id, (string) $message->user_id, $message->message);
    }

    public function review(Request $request, int $media, int $user)
    {
        abort_if($user === $request->user()->id, 403, 'Vous ne pouvez pas signaler votre propre avis.');
        $review = Avis::where('id_media', $media)->where('id', $user)->firstOrFail();

        return $this->create($request, 'review', $media.':'.$user, (string) $user, $review->commentaire);
    }

    private function create(Request $request, string $type, string $targetId, string $authorId, string $snapshot)
    {
        $validated = $request->validate([
            'reason' => ['required', Rule::in(['insult', 'harassment', 'spam', 'spoiler', 'illegal', 'other'])],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);
        $duplicate = Report::where('reporter_id', $request->user()->id)
            ->where('target_type', $type)->where('target_id', $targetId)->where('status', 'pending')->exists();
        if ($duplicate) {
            return back()->with('error', 'Vous avez déjà signalé ce contenu. Il est en attente de traitement.');
        }
        Report::create([
            'reporter_id' => $request->user()->id,
            'target_type' => $type,
            'target_id' => $targetId,
            'target_author_id' => $authorId,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'target_snapshot' => $snapshot,
        ]);

        return back()->with('success', 'Merci. Votre signalement a été transmis à l’équipe de modération.');
    }
}
