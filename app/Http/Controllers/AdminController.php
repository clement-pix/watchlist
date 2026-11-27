<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Media;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\Report;
use App\Services\ModerationLogService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'stats' => [
                'users' => User::count(),
                'media' => Media::count(),
                'reviews' => Avis::count(),
                'average' => round((float) Avis::avg('note'), 1),
                'pendingReports' => Report::where('status', 'pending')->count(),
            ],
            'recentUsers' => User::latest()->take(6)->get(),
            'recentReviews' => Avis::with(['membre', 'media'])->take(8)->get(),
            'recentMessages' => ChatMessage::with('user')->latest()->take(8)->get(),
        ]);
    }

    public function destroyReview(Request $request, int $media, int $user, ModerationLogService $logs)
    {
        $review = Avis::query()->where('id_media', $media)->where('id', $user)->first();
        Avis::query()->where('id_media', $media)->where('id', $user)->delete();
        if ($review) {
            $logs->record($request->user(), 'review_deleted', User::find($user), 'review', $media.':'.$user, 'Avis retiré par la modération', ['snapshot' => $review->commentaire]);
        }

        return back()->with('success', 'L’avis a été supprimé.');
    }
}
