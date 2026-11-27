<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorService
{
    public function issue(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(10),
            'two_factor_attempts' => 0,
        ])->save();

        Mail::raw(
            "Votre code de sécurité WatchList est : {$code}\n\nIl expire dans 10 minutes. Si vous n’êtes pas à l’origine de cette demande, changez votre mot de passe.",
            fn ($message) => $message->to($user->email, $user->firstname)->subject('Votre code de sécurité WatchList')
        );
    }

    public function verify(User $user, string $code): bool
    {
        if (! $user->two_factor_code || ! $user->two_factor_expires_at || $user->two_factor_expires_at->isPast()) {
            return false;
        }

        if ($user->two_factor_attempts >= 5 || ! Hash::check($code, $user->two_factor_code)) {
            $user->increment('two_factor_attempts');
            return false;
        }

        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_attempts' => 0,
        ])->save();

        return true;
    }

}
