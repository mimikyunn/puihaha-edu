<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;

uses(RefreshDatabase::class);

test('password reset forms render with the original logo and login recovery link', function () {
    $this->get(route('login'))->assertOk()->assertSee(route('password.request'), false)->assertSee('Forgot password?')->assertSee('m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z', false)->assertDontSee('Your community, connected')->assertDontSee('PUIHAHA · Membership management');
    $this->get(route('register'))->assertOk()->assertSee('Manage every member, renewal, and payment in one place.');
    $this->get(route('password.request'))->assertOk()->assertSee('Email reset link');
    $this->get(route('password.reset', ['token' => 'example-token', 'email' => 'admin@example.com']))->assertOk()->assertSee('Choose a new password');
});

test('a reset notification contains a usable single use link and changes the password', function () {
    Notification::fake();
    Event::fake([PasswordReset::class]);
    $user = User::factory()->create(['role' => 'admin']);
    $oldRememberToken = $user->remember_token;
    $this->from(route('password.request'))->post(route('password.email'), ['email' => $user->email])->assertRedirect(route('password.request'))->assertSessionHas('status');
    $notification = Notification::sent($user, ResetPassword::class)->sole();
    $message = $notification->toMail($user);
    expect($message->actionUrl)->toContain('/reset-password/'.$notification->token)->toContain('email='.urlencode($user->email));
    expect(DB::table('password_reset_tokens')->value('token'))->not->toBe($notification->token);
    $this->post(route('password.update'), ['token' => $notification->token, 'email' => $user->email, 'password' => 'a-new-password', 'password_confirmation' => 'a-new-password'])->assertSessionHasNoErrors()->assertRedirect(route('login'));
    expect(Hash::check('a-new-password', $user->fresh()->password))->toBeTrue()->and($user->fresh()->remember_token)->not->toBe($oldRememberToken);
    Event::assertDispatched(PasswordReset::class);
    expect(DB::table('password_reset_tokens')->count())->toBe(0);
    $this->post(route('password.update'), ['token' => $notification->token, 'email' => $user->email, 'password' => 'another-password', 'password_confirmation' => 'another-password'])->assertSessionHasErrors('email');
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'a-new-password'])->assertRedirect(route('dashboard'));
});

test('unknown emails receive the same response without sending a notification', function () {
    Notification::fake();
    $this->post(route('password.email'), ['email' => 'missing@example.com'])->assertSessionHasNoErrors()->assertSessionHas('status', 'If an account matches this email, a password reset link has been sent.');
    Notification::assertNothingSent();
});

test('reset rejects invalid expired mismatched and unconfirmed credentials', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $token = Password::createToken($user);
    $this->post(route('password.email'), ['email' => 'bad-email'])->assertSessionHasErrors('email');
    $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'short'])->assertSessionHasErrors('password');
    $this->post(route('password.update'), ['token' => $token, 'email' => $other->email, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('email');
    $this->post(route('password.update'), ['token' => 'invalid', 'email' => $user->email, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('email');
    $this->travel(61)->minutes();
    $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('email');
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('password reset requests are throttled per account', function () {
    Notification::fake();
    $user = User::factory()->create();
    $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();
    $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasErrors('email');
    Notification::assertSentToTimes($user, ResetPassword::class, 1);
});

test('mail transport failure returns an actionable error without claiming delivery', function () {
    Password::shouldReceive('sendResetLink')->once()->andThrow(new TransportException('SMTP unavailable'));
    $this->from(route('password.request'))->post(route('password.email'), ['email' => 'admin@example.com'])->assertRedirect(route('password.request'))->assertSessionHasErrors('email')->assertSessionMissing('status');
});

test('reset removes existing database sessions for that account', function () {
    config(['session.driver' => 'database']);
    $user = User::factory()->create();
    DB::table('sessions')->insert(['id' => 'old-session', 'user_id' => $user->id, 'payload' => base64_encode(serialize([])), 'last_activity' => now()->timestamp]);
    $token = Password::createToken($user);
    $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertRedirect(route('login'));
    $this->assertDatabaseMissing('sessions', ['id' => 'old-session']);
});
