<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    Notification::fake();
});

test('weather requires an administrator and validates coordinates', function () {
    $this->getJson(route('weather.show', ['lat' => 14.5995, 'lon' => 120.9842]))->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson(route('weather.show', ['lat' => 14.5995, 'lon' => 120.9842]))->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->getJson(route('weather.show', ['lat' => 91, 'lon' => -181]))->assertUnprocessable()->assertJsonValidationErrors(['lat', 'lon']);
    $this->getJson(route('weather.show'))->assertUnprocessable()->assertJsonValidationErrors(['lat', 'lon']);
    Http::assertNothingSent();
});

test('weather is cached and returns only safe provider fields', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    config(['services.openweather.key' => 'server-secret']);
    Http::fake(['api.openweathermap.org/*' => Http::response(['name' => 'Manila', 'main' => ['temp' => 29.5, 'humidity' => 75], 'weather' => [['description' => 'clear sky']], 'appid' => 'server-secret', 'internal' => 'private'])]);
    for ($request = 0; $request < 2; $request++) {
        $this->getJson(route('weather.show', ['lat' => 14.5995, 'lon' => 120.9842]))->assertOk()->assertExactJson(['place' => 'Manila', 'temperature' => 29.5, 'description' => 'clear sky', 'humidity' => 75])->assertDontSee('server-secret')->assertDontSee('private');
    }
    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request->url() !== '' && $request['appid'] === 'server-secret' && $request['units'] === 'metric' && $request['lat'] == 14.6 && $request['lon'] == 120.984);
});

test('weather fails gracefully with no key provider errors or malformed data', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    config(['services.openweather.key' => null]);
    $this->getJson(route('weather.show', ['lat' => 0, 'lon' => 0]))->assertStatus(503)->assertJsonStructure(['message']);
    Http::assertNothingSent();
    config(['services.openweather.key' => 'server-secret']);
    Http::fake(['api.openweathermap.org/*' => Http::sequence()->push(['message' => 'server-secret'], 401)->push([], 200)->pushStatus(429)]);
    foreach ([0, 1, 2] as $coordinate) {
        $this->getJson(route('weather.show', ['lat' => $coordinate, 'lon' => 0]))->assertStatus(503)->assertDontSee('server-secret');
    }
});

test('weather connection failures return a generic unavailable message', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    config(['services.openweather.key' => 'server-secret']);
    Http::fake(fn () => throw new ConnectionException('Connection failed'));
    $this->getJson(route('weather.show', ['lat' => 0, 'lon' => 0]))->assertStatus(503)->assertDontSee('server-secret');
});

test('weather requests have a route rate limit', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    config(['services.openweather.key' => null]);
    for ($request = 0; $request < 30; $request++) {
        $this->getJson(route('weather.show', ['lat' => 0, 'lon' => 0]))->assertStatus(503);
    }
    $this->getJson(route('weather.show', ['lat' => 0, 'lon' => 0]))->assertStatus(429);
});

test('registration and edit save optional member location and keep weather keys off the page', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    config(['services.google_maps.key' => 'browser-key', 'services.openweather.key' => 'server-secret']);
    $category = MembershipCategory::factory()->create();
    $level = MembershipLevel::factory()->create();
    $data = ['name' => 'Ana Cruz', 'email' => 'ana@example.com', 'membership_category_id' => $category->id, 'membership_level_id' => $level->id, 'address' => 'Manila, Philippines', 'latitude' => '14.5995000', 'longitude' => '120.9842000'];
    $this->post(route('members.store'), $data)->assertSessionHasNoErrors();
    $member = Member::query()->sole();
    expect($member->latitude)->toBe('14.5995000')->and($member->longitude)->toBe('120.9842000')->and($member->address)->toBe('Manila, Philippines');
    $this->get(route('members.edit', $member))->assertOk()->assertSee('browser-key')->assertDontSee('server-secret')->assertSee('Use Manila center');
    $this->get(route('members.show', $member))->assertOk()->assertSee('Manila, Philippines')->assertSee('14.5995000')->assertDontSee('server-secret');
    $this->put(route('members.update', $member), [...$data, 'member_number' => $member->member_number, 'latitude' => null, 'longitude' => null, 'address' => 'Updated address'])->assertSessionHasNoErrors();
    expect($member->fresh()->latitude)->toBeNull()->and($member->fresh()->longitude)->toBeNull()->and($member->fresh()->address)->toBe('Updated address');
});

test('invalid or incomplete member coordinates cannot be saved', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $category = MembershipCategory::factory()->create();
    $level = MembershipLevel::factory()->create();
    $data = ['name' => 'Ana Cruz', 'email' => 'ana@example.com', 'membership_category_id' => $category->id, 'membership_level_id' => $level->id];
    $this->post(route('members.store'), [...$data, 'latitude' => 91, 'longitude' => 181])->assertSessionHasErrors(['latitude', 'longitude']);
    $this->post(route('members.store'), [...$data, 'latitude' => 14.5995])->assertSessionHasErrors('longitude');
    $this->post(route('members.store'), [...$data, 'longitude' => 120.9842])->assertSessionHasErrors('latitude');
    expect(Member::query()->count())->toBe(0);
});
