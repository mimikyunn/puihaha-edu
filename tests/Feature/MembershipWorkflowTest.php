<?php

use App\MembershipService;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

test('registration creates a member and a priced application atomically', function () {
    $category = MembershipCategory::factory()->create(['fee' => 25]);
    $level = MembershipLevel::factory()->create(['fee' => 750, 'duration_months' => 6]);
    $this->post(route('members.store'), ['name' => 'Samantha Member', 'email' => 'sam@example.com', 'membership_category_id' => $category->id, 'membership_level_id' => $level->id])->assertSessionHasNoErrors()->assertRedirect();
    $member = Member::query()->sole();
    $transaction = $member->transactions()->sole();
    expect($member->member_number)->toStartWith('MEM-')
        ->and($member->status)->toBe('pending')
        ->and($member->user->role)->toBe('member')
        ->and($transaction->transaction_number)->toStartWith('TXN-')
        ->and($transaction->amount_due)->toBe('775.00')
        ->and($transaction->duration_months)->toBe(6)
        ->and($transaction->status)->toBe('pending');
});

test('registration rejects invalid details duplicate numbers and inactive options', function () {
    $existing = Member::factory()->create();
    $category = MembershipCategory::factory()->create(['is_active' => false]);
    $level = MembershipLevel::factory()->create(['is_active' => false]);
    $this->post(route('members.store'), ['name' => '', 'email' => 'bad-email', 'member_number' => $existing->member_number, 'membership_category_id' => $category->id, 'membership_level_id' => $level->id])->assertSessionHasErrors(['name', 'email', 'member_number', 'membership_category_id', 'membership_level_id']);
    expect(Member::query()->count())->toBe(1)->and(MembershipTransaction::query()->count())->toBe(0);
});

test('member details can be edited and filtered by name category and level', function () {
    $member = Member::factory()->create();
    $other = Member::factory()->create();
    $this->put(route('members.update', $member), ['name' => 'Searchable Person', 'email' => $member->user->email, 'member_number' => $member->member_number, 'membership_category_id' => $member->membership_category_id, 'membership_level_id' => $member->membership_level_id, 'phone_number' => '09123456789'])->assertSessionHasNoErrors()->assertRedirect(route('members.show', $member));
    $this->get(route('members', ['search' => 'Searchable', 'category' => $member->membership_category_id, 'level' => $member->membership_level_id]))->assertOk()->assertSee('Searchable Person')->assertDontSee($other->user->email);
    expect($member->fresh()->phone_number)->toBe('09123456789');
});

test('an unpaid application cannot be approved and partial payments keep it pending', function () {
    $transaction = MembershipTransaction::factory()->create(['amount_due' => 500]);
    $this->patch(route('transactions.approve', $transaction))->assertSessionHasErrors('status');
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => '200.25', 'payment_method' => 'cash'])->assertSessionHasNoErrors()->assertRedirect();
    expect($transaction->fresh()->status)->toBe('pending')->and($transaction->fresh()->balanceCents())->toBe(29975);
    $this->patch(route('transactions.approve', $transaction))->assertSessionHasErrors('status');
    expect($transaction->member->fresh()->status)->toBe('pending');
});

test('full payment permits approval and activates the snapshotted validity', function () {
    $transaction = MembershipTransaction::factory()->create(['amount_due' => 500, 'duration_months' => 6]);
    $transaction->level->update(['duration_months' => 24, 'fee' => 900]);
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 500, 'payment_method' => 'bank_transfer', 'reference_number' => 'BANK-001'])->assertSessionHasNoErrors()->assertRedirect();
    expect($transaction->fresh()->status)->toBe('paid');
    $this->patch(route('transactions.approve', $transaction))->assertSessionHasNoErrors();
    $transaction->refresh();
    expect($transaction->status)->toBe('approved')->and($transaction->amount_due)->toBe('500.00')
        ->and($transaction->member->fresh()->status)->toBe('active')
        ->and($transaction->ends_at->toDateString())->toBe(today()->addMonthsNoOverflow(6)->subDay()->toDateString());
    $this->patch(route('transactions.approve', $transaction))->assertSessionHasErrors('status');
});

test('payments reject invalid amounts references and transaction ids', function () {
    $transaction = MembershipTransaction::factory()->create(['amount_due' => 500]);
    foreach (['0', '-1', '500.01', '1.001'] as $amount) {
        $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => $amount, 'payment_method' => 'cash'])->assertSessionHasErrors('amount');
    }
    $this->post(route('payments.store'), ['membership_transaction_id' => 999, 'amount' => 1, 'payment_method' => 'cash'])->assertSessionHasErrors('membership_transaction_id');
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 10, 'payment_method' => 'card'])->assertSessionHasErrors('reference_number');
    expect(Payment::query()->count())->toBe(0);
});

test('overpayment is checked against the remaining balance and fully paid transactions reject more payments', function () {
    $transaction = MembershipTransaction::factory()->create(['amount_due' => 500]);
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 300, 'payment_method' => 'cash'])->assertSessionHasNoErrors();
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 201, 'payment_method' => 'cash'])->assertSessionHasErrors('amount');
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 200, 'payment_method' => 'cash'])->assertSessionHasNoErrors();
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 1, 'payment_method' => 'cash'])->assertSessionHasErrors('membership_transaction_id');
    expect($transaction->payments()->sum('amount'))->toEqual(500);
});

test('renewal extends from the existing expiry without losing paid days', function () {
    $member = Member::factory()->create(['status' => 'active', 'joined_at' => today()->subYear(), 'expires_at' => today()->addMonths(2)]);
    MembershipTransaction::factory()->for($member)->create(['status' => 'approved', 'approved_at' => now(), 'starts_at' => today()->subYear(), 'ends_at' => $member->expires_at]);
    $oldExpiry = $member->expires_at->copy();
    $this->post(route('transactions.store'), ['member_id' => $member->id, 'type' => 'renewal'])->assertSessionHasNoErrors()->assertRedirect();
    $renewal = $member->transactions()->where('type', 'renewal')->sole();
    $this->post(route('payments.store'), ['membership_transaction_id' => $renewal->id, 'amount' => $renewal->amount_due, 'payment_method' => 'cash'])->assertSessionHasNoErrors();
    $this->patch(route('transactions.approve', $renewal))->assertSessionHasNoErrors();
    expect($renewal->fresh()->starts_at->toDateString())->toBe($oldExpiry->copy()->addDay()->toDateString())
        ->and($member->fresh()->expires_at->toDateString())->toBe($oldExpiry->copy()->addDay()->addMonthsNoOverflow(12)->subDay()->toDateString());
});

test('duplicate applications premature renewals and simultaneous open transactions are rejected', function () {
    $member = Member::factory()->create();
    $this->post(route('transactions.store'), ['member_id' => $member->id, 'type' => 'renewal'])->assertSessionHasErrors('type');
    $this->post(route('transactions.store'), ['member_id' => $member->id, 'type' => 'application'])->assertSessionHasNoErrors();
    $this->post(route('transactions.store'), ['member_id' => $member->id, 'type' => 'application'])->assertSessionHasErrors('member_id');
    expect($member->transactions()->count())->toBe(1);
    $this->get(route('members.show', $member))->assertOk()->assertSee('View open transaction')->assertDontSee('New application / renewal');
    $this->get(route('transactions.create'))->assertOk()->assertViewHas('members', fn ($members) => ! $members->contains('id', $member->id));
});

test('deactivation preserves history and blocks payments transactions and approval', function () {
    $transaction = MembershipTransaction::factory()->create();
    $member = $transaction->member;
    $this->delete(route('members.destroy', $member))->assertSessionHasNoErrors();
    expect($member->fresh()->status)->toBe('inactive')->and($member->transactions()->count())->toBe(1);
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 100, 'payment_method' => 'cash'])->assertSessionHasErrors('membership_transaction_id');
    $this->post(route('transactions.store'), ['member_id' => $member->id, 'type' => 'application'])->assertSessionHasErrors('member_id');
    $this->patch(route('transactions.approve', $transaction))->assertSessionHasErrors('status');
    $this->patch(route('members.reactivate', $member))->assertSessionHasNoErrors();
    expect($member->fresh()->status)->toBe('pending');
});

test('only unpaid open transactions may be cancelled', function () {
    $transaction = MembershipTransaction::factory()->create();
    $this->patch(route('transactions.cancel', $transaction))->assertSessionHasNoErrors();
    expect($transaction->fresh()->status)->toBe('cancelled');
    $this->post(route('payments.store'), ['membership_transaction_id' => $transaction->id, 'amount' => 1, 'payment_method' => 'cash'])->assertSessionHasErrors('membership_transaction_id');
    $paid = MembershipTransaction::factory()->create();
    Payment::factory()->for($paid, 'transaction')->create(['amount' => 1]);
    $this->patch(route('transactions.cancel', $paid))->assertSessionHasErrors('status');
    expect($paid->fresh()->status)->toBe('pending');
});

test('open transactions prevent category and level changes', function () {
    $transaction = MembershipTransaction::factory()->create();
    $member = $transaction->member;
    $newLevel = MembershipLevel::factory()->create();
    $this->put(route('members.update', $member), ['name' => $member->user->name, 'email' => $member->user->email, 'member_number' => $member->member_number, 'membership_category_id' => $member->membership_category_id, 'membership_level_id' => $newLevel->id])->assertSessionHasErrors('membership_level_id');
    expect($member->fresh()->membership_level_id)->toBe($member->membership_level_id);
});

test('expiry updates elapsed records and zero fee transactions can be approved', function () {
    $member = Member::factory()->create(['status' => 'active', 'expires_at' => today()->subDay()]);
    $transaction = MembershipTransaction::factory()->for($member)->create(['status' => 'approved', 'ends_at' => today()->subDay()]);
    $this->artisan('membership:expire')->assertSuccessful();
    expect($member->fresh()->status)->toBe('expired')->and($transaction->fresh()->status)->toBe('expired');
    $freeMember = Member::factory()->create(['membership_level_id' => MembershipLevel::factory()->create(['fee' => 0])->id]);
    $free = app(MembershipService::class)->createTransaction($freeMember, 'application');
    expect($free->status)->toBe('paid')->and($free->amount_due)->toBe('0.00');
    $this->patch(route('transactions.approve', $free))->assertSessionHasNoErrors();
    expect($freeMember->fresh()->status)->toBe('active');
});

test('administrators can manage categories and levels with validation', function () {
    $this->post(route('member-categories.store'), ['name' => 'Special', 'description' => 'Special category', 'fee' => 20, 'is_active' => 1])->assertSessionHasNoErrors();
    $category = MembershipCategory::query()->sole();
    $this->post(route('member-categories.store'), ['name' => 'Special', 'fee' => -1, 'is_active' => 1])->assertSessionHasErrors(['name', 'fee']);
    $this->put(route('member-categories.update', $category), ['name' => 'Updated', 'fee' => 25, 'is_active' => 1])->assertSessionHasNoErrors();
    $this->delete(route('member-categories.destroy', $category))->assertSessionHasNoErrors();
    expect($category->fresh()->is_active)->toBeFalse();
    $this->post(route('member-levels.store'), ['name' => 'Diamond', 'fee' => 1000, 'duration_months' => 12, 'rank' => 5, 'benefits' => 'Access', 'is_active' => 1])->assertSessionHasNoErrors();
    $level = MembershipLevel::query()->sole();
    $this->put(route('member-levels.update', $level), ['name' => 'Diamond', 'fee' => 1200, 'duration_months' => 6, 'rank' => 5, 'is_active' => 1])->assertSessionHasNoErrors();
    $this->post(route('member-levels.store'), ['name' => 'Invalid', 'fee' => -1, 'duration_months' => 0, 'rank' => 0, 'is_active' => 1])->assertSessionHasErrors(['fee', 'duration_months']);
    $this->delete(route('member-levels.destroy', $level))->assertSessionHasNoErrors();
    expect($level->fresh()->is_active)->toBeFalse();
});

test('default catalog seeding is repeatable and does not overwrite custom fees', function () {
    $this->seed(DatabaseSeeder::class);
    MembershipLevel::query()->where('name', 'Gold')->update(['fee' => 999]);
    $this->seed(DatabaseSeeder::class);
    expect(MembershipCategory::query()->count())->toBe(4)->and(MembershipLevel::query()->count())->toBe(4)
        ->and(MembershipLevel::query()->where('name', 'Gold')->value('fee'))->toBe('999.00');
});

test('all forms lists profiles histories and printable receipts render', function () {
    $transaction = MembershipTransaction::factory()->create();
    $payment = Payment::factory()->for($transaction, 'transaction')->create(['amount' => 100]);
    $member = $transaction->member;
    $pages = [route('dashboard'), route('members'), route('members.create'), route('members.show', $member), route('members.edit', $member), route('members.record', $member), route('member-categories'), route('member-categories.create'), route('member-categories.edit', $member->category), route('member-levels'), route('member-levels.create'), route('member-levels.edit', $member->level), route('transactions.index'), route('transactions.create'), route('transactions.show', $transaction), route('payments.index'), route('payments.create'), route('payments.show', $payment)];
    foreach ($pages as $page) {
        $this->get($page)->assertOk();
    }
    $this->get(route('members.show', $member))->assertSee($payment->payment_number)->assertSee($transaction->transaction_number);
    $this->get(route('payments.show', $payment))->assertSee($member->user->name)->assertSee($payment->payment_number)->assertSee('Print receipt');
});
