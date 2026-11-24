@extends('layout.layout')
@section('title', 'Member profile')
@section('content')
<x-page-heading :title="$member->user->name" :description="$member->member_number">
    <div class="flex flex-wrap gap-2"><a href="{{ route('members') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">All members</a><a href="{{ route('members.record', $member) }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Print record</a><a href="{{ route('members.edit', $member) }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Edit member</a></div>
</x-page-heading>
<div class="mb-6 rounded-xl bg-white p-6">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><h3 class="font-semibold">Membership details</h3><x-status :status="$member->status" /></div>
    <dl class="grid gap-6 text-xs sm:grid-cols-2 xl:grid-cols-4">
        @foreach(['Email address' => $member->user->email, 'Phone number' => $member->phone_number ?? '—', 'Category' => $member->category?->name ?? 'Unassigned', 'Level' => $member->level?->name ?? 'Unassigned', 'Joined' => $member->joined_at?->format('F j, Y') ?? 'Awaiting approval', 'Valid until' => $member->expires_at?->format('F j, Y') ?? 'Awaiting approval'] as $label => $value)<div><dt class="mb-2 text-[#858496]">{{ $label }}</dt><dd class="break-words font-medium">{{ $value }}</dd></div>@endforeach
    </dl>
    <x-member-location :member="$member" readonly />
    <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
        @if($member->status !== 'inactive')
            @if($openTransaction)
                <a href="{{ route('transactions.show', $openTransaction) }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">View open transaction</a>
            @else
                <a href="{{ route('transactions.create', ['member' => $member->id]) }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">New application / renewal</a>
            @endif
            <form action="{{ route('members.destroy', $member) }}" method="POST" data-confirm="Deactivate this member? Their history will be kept.">@csrf @method('DELETE')<button class="cursor-pointer rounded-lg border border-red-100 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50">Deactivate member</button></form>
        @else
            <form action="{{ route('members.reactivate', $member) }}" method="POST">@csrf @method('PATCH')<button class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Reactivate member</button></form>
        @endif
    </div>
</div>
<div class="rounded-xl bg-white p-6">
    <h3 class="mb-5 font-semibold">Transaction history</h3>
    <div class="space-y-4">@forelse($transactions as $transaction)
        <div class="rounded-lg border border-slate-100 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><a href="{{ route('transactions.show', $transaction) }}" class="text-xs font-medium text-[#270CC5] hover:underline">{{ $transaction->transaction_number }}</a><p class="mt-1 text-[10px] text-[#858496]">{{ ucfirst($transaction->type) }} · {{ $transaction->created_at->format('M j, Y') }} · {{ $transaction->category?->name }} / {{ $transaction->level?->name ?? 'Legacy level' }}</p></div><div class="flex items-center gap-4"><span class="text-xs font-medium">₱{{ number_format($transaction->amount_due, 2) }}</span><x-status :status="$transaction->status" /></div></div>
            @foreach($transaction->payments as $payment)<div class="mt-3 flex flex-wrap justify-between gap-2 border-t border-slate-100 pt-3 text-[10px] text-[#858496]"><a href="{{ route('payments.show', $payment) }}" class="text-[#270CC5] hover:underline">{{ $payment->payment_number }}</a><span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }} · ₱{{ number_format($payment->amount, 2) }} · {{ $payment->paid_at?->format('M j, Y') }} · {{ ucfirst($payment->status) }}</span></div>@endforeach
        </div>
    @empty<p class="py-10 text-center text-xs text-[#858496]">No transactions yet.</p>@endforelse</div>
    <div class="mt-5">{{ $transactions->links() }}</div>
</div>
@endsection
