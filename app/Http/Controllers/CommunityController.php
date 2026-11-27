<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\User;
use App\Services\CommunityProgressService;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function profile(Request $request, User $user, CommunityProgressService $progress)
    {
        abort_if($user->profile_visibility === 'private' && $request->user()->id !== $user->id && ! in_array((int) $request->user()->id_role, [1, 3], true), 403);
        $stats = $progress->stats($user);

        return view('community.profile', [
            'member' => $user->load('equippedReward'),
            'communityStats' => $stats,
            'unlockedRewards' => $progress->unlocked($user, $stats),
        ]);
    }

    public function equip(Request $request, Reward $reward, CommunityProgressService $progress)
    {
        abort_unless($reward->enabled && $progress->unlocked($request->user())->contains('id', $reward->id), 403);
        $request->user()->update(['equipped_reward_id' => $reward->id]);

        return back()->with('success', "L’émoticône {$reward->emoji} est maintenant affichée avec votre pseudo.");
    }

    public function unequip(Request $request)
    {
        $request->user()->update(['equipped_reward_id' => null]);

        return back()->with('success', 'Votre émoticône publique a été retirée.');
    }
}
