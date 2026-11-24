@extends('layout.layout')
@section('title', 'Dashboard')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('members') }}" class="rounded-xl bg-white p-5 transition hover:shadow-sm">
        <p class="text-xs text-[#858496]">Total members</p><p class="mt-2 text-[27px] font-semibold leading-none">{{ number_format($totalMembers) }}</p><p class="mt-4 text-[10px] text-[#858496]">Across all categories</p>
    </a>
    <a href="{{ route('members', ['status' => 'active']) }}" class="rounded-xl bg-white p-5 transition hover:shadow-sm">
        <p class="text-xs text-[#858496]">Active members</p><p class="mt-2 text-[27px] font-semibold leading-none">{{ number_format($activeMembers) }}</p><p class="mt-4 text-[10px] text-[#858496]">{{ $totalMembers ? number_format($activeMembers / $totalMembers * 100, 1) : '0' }}% of total members</p>
    </a>
    <a href="{{ route('transactions.index') }}" class="rounded-xl bg-white p-5 transition hover:shadow-sm">
        <p class="text-xs text-[#858496]">Pending transactions</p><p class="mt-2 text-[27px] font-semibold leading-none">{{ number_format($pendingTransactions) }}</p><p class="mt-4 text-[10px] text-[#858496]">{{ $paidTransactions }} paid · {{ $pendingTransactions - $paidTransactions }} awaiting payment</p>
    </a>
    <a href="{{ route('payments.index') }}" class="rounded-xl bg-[#270CC5] p-5 text-white transition hover:bg-[#1800A6]">
        <p class="text-xs text-indigo-200">Collected payments</p><p class="mt-2 text-[27px] font-semibold leading-none">₱{{ number_format($collectedPayments, 2) }}</p><p class="mt-4 text-[10px] text-indigo-200">This month · {{ now()->format('M Y') }}</p>
    </a>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
    <section aria-labelledby="attention-heading" class="min-w-0 rounded-xl bg-white p-5 sm:p-6">
        <div class="mb-5 flex items-center justify-between gap-3">
            <div><h2 id="attention-heading" class="text-sm font-semibold">Needs attention</h2><p class="mt-1 text-[11px] text-[#858496]">Your next reviews, oldest first.</p></div>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">{{ number_format($paidTransactions + $pendingPaymentCount) }} to review</span>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="min-w-0 rounded-lg border border-slate-100 p-4">
                <div class="flex items-center justify-between gap-2"><h3 class="text-xs font-medium">Awaiting approval</h3><span class="text-lg font-semibold text-[#270CC5]">{{ number_format($paidTransactions) }}</span></div>
                <p class="mt-1 text-[10px] leading-5 text-[#858496]">Paid applications and renewals</p>
                <ul class="mt-3 divide-y divide-slate-100">
                    @forelse($approvalsToReview as $transaction)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0"><p class="truncate text-xs font-medium">{{ $transaction->member->user->name }}</p><p class="mt-1 text-[10px] text-[#858496]">{{ ucfirst($transaction->type) }} · ₱{{ number_format($transaction->amount_due, 2) }}</p></div>
                            <a href="{{ route('transactions.show', $transaction) }}" aria-label="Review transaction for {{ $transaction->member->user->name }}" class="shrink-0 text-[11px] font-medium text-[#270CC5] hover:underline">Review</a>
                        </li>
                    @empty
                        <li class="py-5 text-[11px] leading-5 text-[#858496]">You’re all caught up. No memberships are waiting for approval.</li>
                    @endforelse
                </ul>
                @if($paidTransactions)<a href="{{ route('transactions.index', ['status' => 'paid']) }}" class="mt-2 inline-block text-[11px] font-medium text-[#270CC5] hover:underline">View all awaiting approval →</a>@endif
            </div>
            <div class="min-w-0 rounded-lg border border-slate-100 p-4">
                <div class="flex items-center justify-between gap-2"><h3 class="text-xs font-medium">Payments awaiting verification</h3><span class="text-lg font-semibold text-[#270CC5]">{{ number_format($pendingPaymentCount) }}</span></div>
                <p class="mt-1 text-[10px] leading-5 text-[#858496]">Payment records with pending status</p>
                <ul class="mt-3 divide-y divide-slate-100">
                    @forelse($paymentsToReview as $payment)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0"><p class="truncate text-xs font-medium">{{ $payment->transaction->member->user->name }}</p><p class="mt-1 text-[10px] text-[#858496]">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }} · ₱{{ number_format($payment->amount, 2) }}</p></div>
                            <a href="{{ route('payments.show', $payment) }}" aria-label="Review payment for {{ $payment->transaction->member->user->name }}" class="shrink-0 text-[11px] font-medium text-[#270CC5] hover:underline">Review</a>
                        </li>
                    @empty
                        <li class="py-5 text-[11px] leading-5 text-[#858496]">No pending payment records to review.</li>
                    @endforelse
                </ul>
                @if($pendingPaymentCount)<a href="{{ route('payments.index', ['status' => 'pending']) }}" class="mt-2 inline-block text-[11px] font-medium text-[#270CC5] hover:underline">View all pending payments →</a>@endif
            </div>
        </div>
    </section>

    <section aria-labelledby="actions-heading" class="rounded-xl bg-white p-5 sm:p-6">
        <h2 id="actions-heading" class="text-sm font-semibold">Quick actions</h2><p class="mt-1 text-[11px] text-[#858496]">Keep your membership tasks moving.</p>
        <div class="mt-5 space-y-3">
            @foreach ([['members.create', [], 'members', 'Add member', 'Register someone new to your community.'], ['payments.create', [], 'payments', 'Record payment', 'Record a payment against a transaction.'], ['transactions.create', ['type' => 'renewal'], 'transactions', 'Renew membership', 'Extend an existing member’s validity.']] as [$routeName, $parameters, $icon, $label, $description])
                <a href="{{ route($routeName, $parameters) }}" class="group flex items-center gap-3 rounded-lg border border-slate-100 p-4 transition hover:border-[#270CC5]/20 hover:bg-[#270CC5]/5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#270CC5]/5 text-[#270CC5]"><x-icon :name="$icon" class="size-5" /></span>
                    <div class="min-w-0"><p class="text-xs font-medium group-hover:text-[#270CC5]">{{ $label }}</p><p class="mt-1 text-[10px] leading-5 text-[#858496]">{{ $description }}</p></div>
                    <span aria-hidden="true" class="ml-auto text-[#858496] group-hover:text-[#270CC5]">↗</span>
                </a>
            @endforeach
        </div>
    </section>
</div>

<section aria-labelledby="recent-heading" class="mt-6 min-w-0 rounded-xl bg-white p-5 sm:p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><div><h2 id="recent-heading" class="text-sm font-semibold">Recent transactions</h2><p class="mt-1 text-[11px] text-[#858496]">The six most recent applications and renewals.</p></div><a href="{{ route('transactions.index') }}" class="text-[11px] font-medium text-[#270CC5] hover:underline">View all transactions →</a></div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="border-b border-slate-100 text-[10px] uppercase tracking-wider text-[#858496]"><tr>@foreach(['Member', 'Type', 'Amount due', 'Status', 'Date', ''] as $heading)<th scope="col" class="whitespace-nowrap px-3 py-3 font-medium">{{ $heading }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentTransactions as $transaction)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-3 py-4"><a href="{{ route('members.show', $transaction->member) }}" class="font-medium hover:text-[#270CC5]">{{ $transaction->member->user->name }}</a><p class="mt-1 text-[10px] text-[#858496]">{{ $transaction->transaction_number }}</p></td>
                        <td class="px-3 py-4">{{ ucfirst($transaction->type) }}</td><td class="whitespace-nowrap px-3 py-4 font-medium">₱{{ number_format($transaction->amount_due, 2) }}</td><td class="px-3 py-4"><x-status :status="$transaction->status" /></td><td class="whitespace-nowrap px-3 py-4 text-[#858496]">{{ $transaction->created_at->format('M j, Y') }}</td>
                        <td class="px-3 py-4 text-right"><a href="{{ route('transactions.show', $transaction) }}" aria-label="View transaction {{ $transaction->transaction_number }}" class="font-medium text-[#270CC5] hover:underline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center"><p class="text-xs font-medium">No transactions yet</p><p class="mt-2 text-[11px] text-[#858496]">Register your first member to start tracking applications and payments.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="mt-6 grid items-start gap-6 xl:grid-cols-2">
    <section aria-labelledby="expiring-heading" class="min-w-0 rounded-xl bg-white p-5 sm:p-6">
        <div class="mb-4 flex items-start justify-between gap-3"><div><h2 id="expiring-heading" class="text-sm font-semibold">Expiring memberships</h2><p class="mt-1 text-[11px] leading-5 text-[#858496]">Next 30 days · Showing the nearest {{ min(5, $expiringMemberCount) }} of {{ number_format($expiringMemberCount) }}</p></div><span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-medium text-rose-600">{{ number_format($expiringMemberCount) }} expiring</span></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-xs">
            <thead class="border-b border-slate-100 text-[10px] uppercase tracking-wider text-[#858496]"><tr>@foreach(['Member / category', 'Expiry date', 'Remaining'] as $heading)<th scope="col" class="whitespace-nowrap px-2 py-3 font-medium">{{ $heading }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($expiringMembers as $member)
                    @php($daysRemaining = (int) today()->diffInDays($member->expires_at))
                    <tr><td class="px-2 py-4"><a href="{{ route('members.show', $member) }}" class="font-medium text-[#270CC5] hover:underline">{{ $member->user->name }}</a><p class="mt-1 text-[10px] text-[#858496]">{{ $member->category?->name ?? 'Unassigned' }}</p></td><td class="whitespace-nowrap px-2 py-4 text-[11px]">{{ $member->expires_at->format('M j, Y') }}</td><td class="whitespace-nowrap px-2 py-4"><span class="rounded-full px-2 py-1 text-[10px] font-medium {{ $daysRemaining <= 7 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">{{ $daysRemaining === 0 ? 'Expires today' : $daysRemaining.' '.Str::plural('day', $daysRemaining) }}</span></td></tr>
                @empty
                    <tr><td colspan="3" class="py-10 text-center text-[11px] text-[#858496]">No active memberships expire in the next 30 days.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </section>

    <section aria-labelledby="overview-heading" class="min-w-0 rounded-xl bg-white p-5 sm:p-6">
        <h2 id="overview-heading" class="text-sm font-semibold">Membership overview</h2><p class="mt-1 text-[11px] text-[#858496]">Member counts across all statuses.</p>
        <h3 class="mb-4 mt-6 text-[10px] font-medium uppercase tracking-wider text-[#858496]">By category</h3>
        <div class="space-y-4">
            @forelse($categoryOverview as $category)
                @php($percentage = $totalMembers ? round($category->members_count / $totalMembers * 100, 1) : 0)
                <a href="{{ route('members', ['category' => $category->id]) }}" class="block rounded-sm outline-offset-4 hover:opacity-75">
                    <div class="mb-2 flex items-center justify-between gap-3 text-xs"><span>{{ $category->name }}</span><span class="text-[11px] text-[#858496]"><strong class="font-semibold text-[#2E2D39]">{{ number_format($category->members_count) }}</strong> · {{ $percentage }}%</span></div>
                    <div aria-hidden="true" class="h-1.5 overflow-hidden rounded-full bg-[#F1EFFC]"><div class="h-full rounded-full bg-[#270CC5]" style="width: {{ $percentage }}%"></div></div>
                </a>
            @empty
                <p class="text-[11px] leading-5 text-[#858496]">No membership categories yet. <a href="{{ route('member-categories.create') }}" class="text-[#270CC5] hover:underline">Add a category</a> to get started.</p>
            @endforelse
        </div>
        <h3 class="mb-3 mt-6 border-t border-slate-100 pt-5 text-[10px] font-medium uppercase tracking-wider text-[#858496]">By level</h3>
        <div class="grid grid-cols-2 gap-3">
            @forelse($levelOverview as $level)
                <a href="{{ route('members', ['level' => $level->id]) }}" class="flex items-center justify-between gap-3 rounded-lg bg-[#F6F6FB] px-3 py-3 text-xs transition hover:bg-[#270CC5]/10"><span>{{ $level->name }}</span><span class="font-semibold text-[#270CC5]">{{ number_format($level->members_count) }}</span></a>
            @empty
                <p class="col-span-2 text-[11px] text-[#858496]">No membership levels yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
