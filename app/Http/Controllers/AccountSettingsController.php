<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoFactorService;
use App\Services\CommunityProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AccountSettingsController extends Controller
{
    public function index(CommunityProgressService $progress)
    {
        $user = Auth::user();
        $communityStats = $progress->stats($user);
        return view('account.settings', [
            'user' => $user,
            'communityStats' => $communityStats,
            'unlockedRewards' => $progress->unlocked($user, $communityStats),
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'min:2', 'max:50'],
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'pseudo' => ['required', 'string', 'min:2', 'max:50', 'not_regex:/@/', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:120', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s().-]{8,25}$/'],
        ]);

        $emailChanged = $user->email !== $validated['email'];
        $phoneChanged = (string) $user->phone !== (string) ($validated['phone'] ?? '');

        $user->fill($validated);
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        if ($phoneChanged) {
            $user->phone_verified_at = null;
        }
        if ($emailChanged) {
            $user->two_factor_enabled = false;
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
        }
        $user->save();

        return back()->with('success', 'Vos informations personnelles ont été mises à jour.');
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'profile_visibility' => ['required', Rule::in(['private', 'members'])],
        ]);

        $request->user()->update([
            'profile_visibility' => $validated['profile_visibility'],
            'email_notifications' => $request->boolean('email_notifications'),
        ]);

        return back()->with('success', 'Vos préférences ont été enregistrées.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('success', 'Votre mot de passe a été modifié.');
    }

    public function startTwoFactor(Request $request, TwoFactorService $twoFactor)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'method' => ['required', Rule::in(['email'])],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->forceFill([
            'two_factor_method' => 'email',
            'two_factor_enabled' => false,
        ])->save();

        try {
            $twoFactor->issue($user);
        } catch (RuntimeException $exception) {
            $user->forceFill(['two_factor_code' => null, 'two_factor_expires_at' => null])->save();
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('two_factor_setup', true)->with('success', 'Un code de confirmation vient de vous être envoyé.');
    }

    public function confirmTwoFactor(Request $request, TwoFactorService $twoFactor)
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        /** @var User $user */
        $user = $request->user();

        if (! $twoFactor->verify($user, $validated['code'])) {
            return back()->with('two_factor_setup', true)->withErrors(['code' => 'Code incorrect ou expiré.']);
        }

        $user->forceFill([
            'two_factor_enabled' => true,
            'email_verified_at' => now(),
        ])->save();

        return back()->with('success', 'La double authentification est maintenant activée.');
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $request->user()->forceFill([
            'two_factor_enabled' => false,
            'two_factor_method' => null,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_attempts' => 0,
        ])->save();

        return back()->with('success', 'La double authentification a été désactivée.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:SUPPRIMER'],
        ]);

        $user = $request->user();
        DB::transaction(function () use ($user) {
            // Les signalements créés par le membre sont supprimés avec ses données.
            // Les éléments de modération déjà traités restent anonymisés pour préserver l'audit.
            DB::table('reports')->where('reporter_id', $user->id)->delete();
            DB::table('reports')->where('target_author_id', $user->id)->update(['target_author_id' => null]);
            DB::table('moderation_logs')->where('target_user_id', $user->id)->update(['target_user_id' => null]);
            DB::table('avis')->where('id', $user->id)->delete();
            DB::table('Composer')->where('user_id', $user->id)->delete();
            DB::table('liste')->where('id', $user->id)->delete();
            $messageIds = DB::table('chat_messages')->where('user_id', $user->id)->pluck('id');
            DB::table('chat_message_reactions')->where('user_id', $user->id)->orWhereIn('chat_message_id', $messageIds)->delete();
            DB::table('chat_messages')->where('user_id', $user->id)->delete();
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')->with('success', 'Votre compte et vos données ont été supprimés.');
    }
}
