<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        $user = $this->pendingUser($request);

        return view('auth.two-factor-challenge', compact('user'));
    }

    public function verify(Request $request, TwoFactorService $twoFactor)
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $this->pendingUser($request);

        if (! $twoFactor->verify($user, $validated['code'])) {
            return back()->withErrors(['code' => 'Code incorrect ou expiré.']);
        }

        $remember = (bool) $request->session()->pull('two_factor_remember', false);
        $request->session()->forget('two_factor_user_id');
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();

        return redirect()->intended(route('dashboard'));
    }

    public function resend(Request $request, TwoFactorService $twoFactor)
    {
        $user = $this->pendingUser($request);

        try {
            $twoFactor->issue($user);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Un nouveau code vient de vous être envoyé.');
    }

    private function pendingUser(Request $request): User
    {
        $id = $request->session()->get('two_factor_user_id');
        abort_unless($id, 403);

        return User::findOrFail($id);
    }
}
