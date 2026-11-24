<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 11, 24)->setTime(12, 0));
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

test('dashboard renders empty states and working quick action destinations', function () {
    $this->get(route('dashboard'))->assertOk()
        ->assertSee('Needs attention')->assertSee('Recent transactions')->assertSee('Expiring memberships')
        ->assertSee('Quick actions')->assertSee('Membership overview')->assertSee('No transactions yet')
        ->assertSee('No pending payment records to review.')->assertSee('No active memberships expire in the next 30 days.')
        ->assertSee(route('members.create'), false)->assertSee(route('payments.create'), false)
        ->assertSee(route('transactions.create', ['type' => 'renewal']), false)
        ->assertViewHas('totalMembers', 0)->assertViewHas('pendingPaymentCount', 0)->assertViewHas('expiringMemberCount', 0);
});

test('summary cards use real membership transaction and monthly payment totals', function () {
    $member = Member::factory()->create(['status' => 'active', 'expires_at' => today()->addDays(60)]);
    Member::factory()->create(['status' => 'inactive']);
    Member::factory()->create(['status' => 'active', 'expires_at' => today()->subDay()]);
    $pending = MembershipTransaction::factory()->for($member)->create(['status' => 'pending']);
    MembershipTransaction::factory()->for($member)->create(['status' => 'paid']);
    MembershipTransaction::factory()->for($member)->create(['status' => 'approved']);
    MembershipTransaction::factory()->for($member)->create(['status' => 'cancelled']);
    Payment::factory()->for($pending, 'transaction')->create(['amount' => '125.25', 'paid_at' => now()]);
    Payment::factory()->for($pending, 'transaction')->create(['amount' => '50.50', 'paid_at' => now()->startOfMonth()]);
    Payment::factory()->for($pending, 'transaction')->create(['amount' => 900, 'paid_at' => now()->subMonth()]);
    Payment::factory()->for($pending, 'transaction')->create(['amount' => 800, 'status' => 'pending', 'paid_at' => null]);
    $this->get(route('dashboard'))->assertOk()->assertViewHas('totalMembers', 3)->assertViewHas('activeMembers', 1)
        ->assertViewHas('pendingTransactions', 2)->assertViewHas('paidTransactions', 1)->assertViewHas('pendingPaymentCount', 1)
        ->assertViewHas('collectedPayments', fn ($amount) => (float) $amount === 175.75)->assertSee('175.75');
});

test('needs attention shows only paid transactions and pending payments oldest first with review links', function () {
    $member = Member::factory()->create();
    $paidTransactions = collect();
    $pendingPayments = collect();
    for ($index = 0; $index < 6; $index++) {
        $transaction = MembershipTransaction::factory()->for($member)->create(['status' => 'paid', 'type' => $index % 2 === 0 ? 'application' : 'renewal', 'created_at' => now()->addMinutes($index)]);
        $paidTransactions->push($transaction);
        $pendingPayments->push(Payment::factory()->for($transaction, 'transaction')->create(['status' => 'pending', 'paid_at' => null, 'created_at' => now()->addMinutes($index)]));
    }
    MembershipTransaction::factory()->for($member)->create(['status' => 'pending']);
    MembershipTransaction::factory()->for($member)->create(['status' => 'approved']);
    MembershipTransaction::factory()->for($member)->create(['status' => 'cancelled']);
    Payment::factory()->for($paidTransactions->first(), 'transaction')->create(['status' => 'paid']);
    $this->get(route('dashboard'))->assertOk()->assertViewHas('paidTransactions', 6)->assertViewHas('pendingPaymentCount', 6)
        ->assertViewHas('approvalsToReview', fn ($transactions) => $transactions->modelKeys() === $paidTransactions->take(4)->pluck('id')->all())
        ->assertViewHas('paymentsToReview', fn ($payments) => $payments->modelKeys() === $pendingPayments->take(4)->pluck('id')->all())
        ->assertSee(route('transactions.show', $paidTransactions->first()), false)->assertSee(route('payments.show', $pendingPayments->first()), false)
        ->assertSee(route('transactions.index', ['status' => 'paid']), false)->assertSee(route('payments.index', ['status' => 'pending']), false);
});

test('recent transactions show six latest records with member type amount status and date', function () {
    $member = Member::factory()->create(['user_id' => User::factory()->create(['name' => 'Recent Member'])->id]);
    $transactions = collect();
    for ($index = 0; $index < 8; $index++) {
        $transactions->push(MembershipTransaction::factory()->for($member)->create(['status' => 'cancelled', 'type' => 'renewal', 'amount_due' => '1234.50', 'created_at' => now()->subDays($index)]));
    }
    $this->get(route('dashboard'))->assertOk()->assertViewHas('recentTransactions', fn ($recent) => $recent->modelKeys() === $transactions->take(6)->pluck('id')->all())
        ->assertSee('Recent Member')->assertSee('Renewal')->assertSee('1,234.50')->assertSee('Cancelled')->assertSee('Nov 24, 2026')
        ->assertDontSee($transactions->last()->transaction_number);
});

test('expiring memberships include today and day thirty but exclude elapsed inactive and distant records', function () {
    $todayMember = Member::factory()->create(['status' => 'active', 'expires_at' => today()]);
    $tomorrowMember = Member::factory()->create(['status' => 'active', 'expires_at' => today()->addDay()]);
    $boundaryMember = Member::factory()->create(['status' => 'active', 'expires_at' => today()->addDays(30)]);
    Member::factory()->create(['status' => 'active', 'expires_at' => today()->subDay()]);
    Member::factory()->create(['status' => 'active', 'expires_at' => today()->addDays(31)]);
    Member::factory()->create(['status' => 'inactive', 'expires_at' => today()->addDay()]);
    Member::factory()->create(['status' => 'pending', 'expires_at' => today()->addDay()]);
    Member::factory()->create(['status' => 'active', 'expires_at' => null]);
    $this->get(route('dashboard'))->assertOk()->assertViewHas('expiringMemberCount', 3)
        ->assertViewHas('expiringMembers', fn ($members) => $members->modelKeys() === [$todayMember->id, $tomorrowMember->id, $boundaryMember->id])
        ->assertSee($todayMember->category->name)->assertSee('Expires today')->assertSee('1 day')->assertSee('30 days')
        ->assertSee(route('members.show', $todayMember), false);
});

test('expiry preview is limited to the five nearest members while retaining the full count', function () {
    $members = collect();
    for ($day = 1; $day <= 7; $day++) {
        $members->push(Member::factory()->create(['status' => 'active', 'expires_at' => today()->addDays($day)]));
    }
    $this->get(route('dashboard'))->assertOk()->assertViewHas('expiringMemberCount', 7)
        ->assertViewHas('expiringMembers', fn ($preview) => $preview->modelKeys() === $members->take(5)->pluck('id')->all())
        ->assertSee('Showing the nearest 5 of 7');
});

test('membership overview includes empty categories and counts members across statuses with filtered links', function () {
    $category = MembershipCategory::factory()->create(['name' => 'Student']);
    $emptyCategory = MembershipCategory::factory()->create(['name' => 'Corporate']);
    $level = MembershipLevel::factory()->create(['name' => 'Gold']);
    $emptyLevel = MembershipLevel::factory()->create(['name' => 'Platinum']);
    foreach (['active', 'inactive', 'pending'] as $status) {
        Member::factory()->create(['status' => $status, 'membership_category_id' => $category->id, 'membership_level_id' => $level->id]);
    }
    $this->get(route('dashboard'))->assertOk()
        ->assertViewHas('categoryOverview', fn ($categories) => $categories->firstWhere('id', $category->id)->members_count === 3 && $categories->firstWhere('id', $emptyCategory->id)->members_count === 0)
        ->assertViewHas('levelOverview', fn ($levels) => $levels->firstWhere('id', $level->id)->members_count === 3 && $levels->firstWhere('id', $emptyLevel->id)->members_count === 0)
        ->assertSee(route('members', ['category' => $category->id]), false)->assertSee(route('members', ['level' => $level->id]), false);
});

test('pending payment review filter combines with member search and excludes paid records', function () {
    $member = Member::factory()->create(['user_id' => User::factory()->create(['name' => 'Review Target'])->id]);
    $transaction = MembershipTransaction::factory()->for($member)->create();
    $pending = Payment::factory()->for($transaction, 'transaction')->create(['status' => 'pending', 'paid_at' => null]);
    $paid = Payment::factory()->for($transaction, 'transaction')->create(['status' => 'paid']);
    Payment::factory()->create(['status' => 'pending']);
    $this->get(route('payments.index', ['status' => 'pending', 'search' => 'Review Target']))->assertOk()->assertSee($pending->payment_number)->assertDontSee($paid->payment_number)
        ->assertViewHas('payments', fn ($payments) => $payments->modelKeys() === [$pending->id]);
    $this->getJson(route('payments.index', ['status' => 'invalid']))->assertUnprocessable()->assertJsonValidationErrors('status');
});

test('renew membership quick action selects renewal and only offers eligible members', function () {
    $eligible = Member::factory()->create(['status' => 'active']);
    MembershipTransaction::factory()->for($eligible)->create(['status' => 'approved', 'type' => 'application']);
    Member::factory()->create();
    $openMember = Member::factory()->create(['status' => 'active']);
    MembershipTransaction::factory()->for($openMember)->create(['status' => 'approved']);
    MembershipTransaction::factory()->for($openMember)->create(['status' => 'pending', 'type' => 'renewal']);
    $inactiveMember = Member::factory()->create(['status' => 'inactive']);
    MembershipTransaction::factory()->for($inactiveMember)->create(['status' => 'approved']);
    $this->get(route('transactions.create', ['type' => 'renewal']))->assertOk()->assertViewHas('defaultType', 'renewal')
        ->assertViewHas('members', fn ($members) => $members->modelKeys() === [$eligible->id]);
    $this->getJson(route('transactions.create', ['type' => 'invalid']))->assertUnprocessable()->assertJsonValidationErrors('type');
});
