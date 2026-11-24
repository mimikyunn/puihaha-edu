<?php

use App\MembershipService;
use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

uses(DatabaseMigrations::class);

test('application notification is delivered to the registered member with the reference and amount', function () {
    Notification::fake();
    $member = Member::factory()->create();
    $transaction = app(MembershipService::class)->createTransaction($member, 'application');
    Notification::assertSentTo($member->user, MembershipApplicationSubmitted::class, function ($notification, $channels) use ($transaction, $member) {
        $mail = $notification->toMail($member->user);

        return $notification->transaction->id === $transaction->id
            && $notification->afterCommit === true
            && $channels === ['mail']
            && str_contains($mail->subject, 'application received')
            && in_array('Reference number: '.$transaction->transaction_number, $mail->introLines, true)
            && in_array('Total fee: PHP 500.00', $mail->introLines, true);
    });
});

test('queued emails are dispatched only after the outer transaction commits', function () {
    config(['queue.default' => 'database']);
    $member = Member::factory()->create();
    DB::beginTransaction();
    try {
        app(MembershipService::class)->createTransaction($member, 'application');
        expect(DB::table('jobs')->count())->toBe(0);
        DB::commit();
    } catch (Throwable $exception) {
        DB::rollBack();
        throw $exception;
    }
    expect(DB::table('jobs')->count())->toBe(1);
    expect(DB::table('jobs')->value('payload'))->toContain('MembershipApplicationSubmitted');
});

test('rolling back membership creation does not leave an email job', function () {
    config(['queue.default' => 'database']);
    $member = Member::factory()->create();
    DB::beginTransaction();
    app(MembershipService::class)->createTransaction($member, 'application');
    DB::rollBack();
    expect(MembershipTransaction::query()->count())->toBe(0)->and(DB::table('jobs')->count())->toBe(0);
});

test('invalid registration does not send an application email', function () {
    Notification::fake();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->post(route('members.store'), ['name' => 'Ana', 'email' => 'bad-email'])->assertSessionHasErrors(['email', 'membership_category_id', 'membership_level_id']);
    Notification::assertNothingSent();
});
