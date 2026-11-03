<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Bitte gib deine E-Mail-Adresse ein.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'password.required' => 'Bitte gib dein Passwort ein.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'E-Mail-Adresse oder Passwort ist falsch.',
                ])
                ->withInput($request->only(['email', 'remember']));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('tickets.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sign-in');
    }
}
