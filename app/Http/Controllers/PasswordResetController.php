<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        try {
            $status = Password::sendResetLink($data);
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withInput($request->only('email'))->withErrors(['email' => 'We could not send the reset email right now. Please try again later or contact your administrator.']);
        }
        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
        }

        return back()->with('status', 'If an account matches this email, a password reset link has been sent.');
    }

    public function reset(Request $request, string $token): View
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

        return view('auth.reset-password', ['token' => $token, 'email' => $data['email']]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);
        $status = Password::reset($data, function (User $user, string $password): void {
            DB::transaction(function () use ($user, $password): void {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
                }
            });
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
        }

        return to_route('login')->with('status', 'Your password has been reset. Sign in with your new password.');
    }
}
