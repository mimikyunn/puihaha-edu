@extends('layout.layout')
@section('title', 'Transactions')
@section('content')
<x-page-heading title="Transactions" description="Follow every application and renewal from payment to approval."><a href="{{ route('transactions.create') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">+ New transaction</a></x-page-heading>
<div class="rounded-xl bg-white p-5">
    <form action="{{ route('transactions.index') }}" method="GET" class="mb-5 flex flex-wrap gap-3">
        <input name="search" value="{{ request('search') }}" aria-label="Search transactions" placeholder="Search reference or member name…" class="w-full sm:w-80 rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10">
        <select name="status" aria-label="Filter by status" class="w-full sm:w-44 rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="">All statuses</option>@foreach(['pending', 'paid', 'approved', 'expired', 'cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Search</button><a href="{{ route('transactions.index') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Reset</a>
    </form>
    <div class="overflow-x-auto"><table class="w-full text-left text-xs">
        <thead class="border-b border-slate-100 text-[10px] uppercase tracking-wider text-[#858496]"><tr>@foreach(['Transaction', 'Member', 'Type', 'Amount', 'Paid', 'Status', ''] as $heading)<th class="px-3 py-4 font-medium">{{ $heading }}</th>@endforeach</tr></thead>
        <tbody class="divide-y divide-slate-100">@forelse($transactions as $transaction)<tr class="hover:bg-slate-50/70">
            <td class="px-3 py-4"><a href="{{ route('transactions.show', $transaction) }}" class="whitespace-nowrap text-[10px] font-medium text-[#270CC5] hover:underline">{{ $transaction->transaction_number }}</a><p class="mt-1 text-[10px] text-[#858496]">{{ $transaction->created_at->format('M j, Y') }}</p></td>
            <td class="px-3 py-4"><a href="{{ route('members.show', $transaction->member) }}" class="hover:text-[#270CC5]">{{ $transaction->member->user->name }}</a></td><td class="px-3 py-4">{{ ucfirst($transaction->type) }}</td><td class="whitespace-nowrap px-3 py-4">₱{{ number_format($transaction->amount_due, 2) }}</td><td class="whitespace-nowrap px-3 py-4">₱{{ number_format($transaction->paid_total ?? 0, 2) }}</td><td class="px-3 py-4"><x-status :status="$transaction->status" /></td><td class="px-3 py-4"><a href="{{ route('transactions.show', $transaction) }}" class="text-[#270CC5] hover:underline">View</a></td>
        </tr>@empty<tr><td colspan="7" class="py-16 text-center text-[#858496]">No transactions found.</td></tr>@endforelse</tbody>
    </table></div><div class="mt-5">{{ $transactions->links() }}</div>
</div>
@endsection
