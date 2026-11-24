<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginView(): View
    {
        return view('auth.login');
    }

    public function registerView(): View
    {
        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt([...$credentials, 'role' => 'admin'], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The administrator credentials are incorrect.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $user = User::query()->create([...$data, 'role' => 'admin']);
        Auth::login($user);
        $request->session()->regenerate();

        return to_route('dashboard')->with('success', 'Your administrator account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
