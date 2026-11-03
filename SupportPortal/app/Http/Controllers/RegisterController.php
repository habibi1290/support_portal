<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'name.required' => 'Bitte gib deinen Namen ein.',
            'email.required' => 'Bitte gib deine E-Mail-Adresse ein.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'email.unique' => 'Diese E-Mail-Adresse ist bereits registriert.',
            'password.required' => 'Bitte gib ein Passwort ein.',
            'password.' . Password::class =>
                'Dein Passwort muss mindestens 12 Zeichen lang sein und mindestens ' .
                'einen Großbuchstaben, einen Kleinbuchstaben, eine Ziffer und ' .
                'ein Sonderzeichen enthalten.',
            'password.confirmed' => 'Die Passwörter stimmen nicht überein.',
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('register')->with(
            'success',
            'Dein Konto wurde erstellt. Du kannst dich jetzt anmelden.'
        );
    }
}
