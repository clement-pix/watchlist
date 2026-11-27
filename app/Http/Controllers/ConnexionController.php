<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ConnexionController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     *
     * @return \Illuminate\View\View
     */
    public function formulaire()
    {
        // Affiche la vue "connexion.blade.php"
        return view('connexion');
    }

    /**
     * Traite les données envoyées par le formulaire de connexion.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function traitement(Request $request, TwoFactorService $twoFactor)
    {
        // Valide les champs envoyés dans le formulaire
        $request->validate([
            'email' => ['required', 'email'],    // L'email est requis et doit être valide
            'password' => ['required', 'string'],
        ]);

        // Tente de connecter l'utilisateur avec les identifiants fournis
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            // FR : le compte ne doit pas rester connecté pendant la vérification du code.
            // EN: The account must not remain signed in while the code is pending.
            if ($user->two_factor_enabled) {
                $remember = $request->boolean('remember');
                Auth::logout();
                $request->session()->regenerate();
                $request->session()->put('two_factor_user_id', $user->id);
                $request->session()->put('two_factor_remember', $remember);

                try {
                    $twoFactor->issue($user);
                } catch (RuntimeException $exception) {
                    $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);
                    return back()->with('error', $exception->getMessage())->onlyInput('email');
                }

                return redirect()->route('two-factor.challenge');
            }

            $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis sont incorrects.',
        ])->onlyInput('email');
    }
}
