<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('administrators can sign up and sign out with registration remaining available', function () {
    $this->get(route('register'))->assertOk();
    $this->post(route('register.store'), ['name' => 'Administrator 1', 'email' => 'admin@example.com', 'password' => 'strong-password', 'password_confirmation' => 'strong-password'])->assertRedirect(route('dashboard'));
    $admin = User::query()->sole();
    expect($admin->role)->toBe('admin')->and(Hash::check('strong-password', $admin->password))->toBeTrue();
    $this->assertAuthenticatedAs($admin);
    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
    $this->get(route('login'))->assertOk()->assertSee('Sign up!')->assertSee(route('register'), false);
    $this->get(route('register'))->assertOk()->assertSee('Sign up for an administrator account');
    $this->post(route('register.store'), ['name' => 'Administrator 2', 'email' => 'second-admin@example.com', 'password' => 'another-password', 'password_confirmation' => 'another-password'])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    $secondAdministrator = User::query()->where('email', 'second-admin@example.com')->sole();
    expect(User::query()->count())->toBe(2)->and($secondAdministrator->role)->toBe('admin')->and(Hash::check('another-password', $secondAdministrator->password))->toBeTrue();
    $this->assertAuthenticatedAs($secondAdministrator);
    $this->get(route('dashboard'))->assertOk();
});

test('signup rejects duplicate emails and mismatched passwords', function () {
    $existingAdministrator = User::factory()->create(['role' => 'admin']);
    $this->post(route('register.store'), ['name' => 'Duplicate', 'email' => $existingAdministrator->email, 'password' => 'strong-password', 'password_confirmation' => 'different-password'])->assertSessionHasErrors(['email', 'password']);
    expect(User::query()->count())->toBe(1);
    $this->assertGuest();
});

test('administrator login validates credentials and rejects members', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = User::factory()->create();
    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->post(route('login.store'), ['email' => $member->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($admin);
});

test('guests and member accounts cannot access administration', function () {
    foreach (['dashboard', 'members', 'member-categories', 'member-levels', 'transactions.index', 'payments.index'] as $routeName) {
        $this->get(route($routeName))->assertRedirect(route('login'));
    }
    $this->actingAs(User::factory()->create());
    foreach (['dashboard', 'members', 'member-categories', 'member-levels', 'transactions.index', 'payments.index'] as $routeName) {
        $this->get(route($routeName))->assertForbidden();
    }
    $this->post(route('members.store'), [])->assertForbidden();
    $this->post(route('member-levels.store'), [])->assertForbidden();
    $this->post(route('payments.store'), [])->assertForbidden();
});

test('setup validates names email and confirmed passwords', function () {
    $this->post(route('register.store'), ['email' => 'invalid', 'password' => 'short'])->assertSessionHasErrors(['name', 'email', 'password']);
    expect(User::query()->count())->toBe(0);
});

test('repeated failed login attempts are throttled', function () {
    User::factory()->create(['role' => 'admin']);
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('login.store'), ['email' => 'missing@example.com', 'password' => 'incorrect'])->assertSessionHasErrors('email');
    }
    $this->post(route('login.store'), ['email' => 'missing@example.com', 'password' => 'incorrect'])->assertStatus(429);
});
