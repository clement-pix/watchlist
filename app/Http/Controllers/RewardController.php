<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index()
    {
        return view('admin.rewards', ['rewards' => Reward::orderBy('sort_order')->orderBy('threshold')->get()]);
    }

    public function store(Request $request)
    {
        Reward::create($this->validated($request));
        return back()->with('success', 'La récompense a été créée.');
    }

    public function update(Request $request, Reward $reward)
    {
        $reward->update($this->validated($request));
        return back()->with('success', 'La récompense a été mise à jour.');
    }

    public function destroy(Reward $reward)
    {
        User::where('equipped_reward_id', $reward->id)->update(['equipped_reward_id' => null]);
        DB::table('chat_message_reactions')->where('reward_id', $reward->id)->delete();
        Reward::whereKey($reward->id)->delete();
        return back()->with('success', 'La récompense a été supprimée.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'emoji' => ['required', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:180'],
            'criterion_type' => ['required', Rule::in(['reviews', 'watchlist', 'messages'])],
            'threshold' => ['required', 'integer', 'min:0', 'max:10000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);
        $data['enabled'] = $request->boolean('enabled');
        $data['sort_order'] ??= 0;
        return $data;
    }
}
