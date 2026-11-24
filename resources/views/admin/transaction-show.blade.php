@extends('layout.layout')
@section('title', 'Transaction details')
@section('content')
<x-page-heading title="Transaction details" :description="$transaction->transaction_number"><a href="{{ route('transactions.index') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">All transactions</a></x-page-heading>
<div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
    <div class="rounded-xl bg-white p-6">
        <div class="mb-6 flex items-center justify-between gap-3"><h3 class="font-semibold">{{ ucfirst($transaction->type) }}</h3><x-status :status="$transaction->status" /></div>
        <dl class="grid gap-6 text-xs sm:grid-cols-2">
            <div><dt class="mb-2 text-[#858496]">Member</dt><dd><a href="{{ route('members.show', $transaction->member) }}" class="font-medium text-[#270CC5] hover:underline">{{ $transaction->member->user->name }}</a></dd></div>
            @foreach(['Created' => $transaction->created_at->format('F j, Y'), 'Category' => $transaction->category->name, 'Level' => $transaction->level?->name ?? 'Legacy transaction', 'Validity' => $transaction->duration_months.' months', 'Starts' => $transaction->starts_at?->format('F j, Y') ?? 'On approval', 'Ends' => $transaction->ends_at?->format('F j, Y') ?? 'On approval', 'Approved' => $transaction->approved_at?->format('F j, Y g:i A') ?? '—'] as $label => $value)<div><dt class="mb-2 text-[#858496]">{{ $label }}</dt><dd class="font-medium">{{ $value }}</dd></div>@endforeach
        </dl>
        @if($transaction->notes)<div class="mt-6 border-t border-slate-100 pt-5"><p class="mb-2 text-xs text-[#858496]">Notes</p><p class="whitespace-pre-line text-xs leading-6">{{ $transaction->notes }}</p></div>@endif
    </div>
    <div class="rounded-xl bg-white p-6">
        <h3 class="mb-6 font-semibold">Payment summary</h3>
        <dl class="space-y-4 text-xs"><div class="flex justify-between gap-3"><dt class="text-[#858496]">Total fee</dt><dd>₱{{ number_format($transaction->amount_due, 2) }}</dd></div><div class="flex justify-between gap-3"><dt class="text-[#858496]">Total paid</dt><dd>₱{{ number_format($transaction->paidCents() / 100, 2) }}</dd></div><div class="flex justify-between gap-3 border-t border-slate-100 pt-4 font-semibold"><dt>Balance</dt><dd class="text-[#270CC5]">₱{{ number_format($transaction->balanceCents() / 100, 2) }}</dd></div></dl>
        <div class="mt-6 space-y-3">
            @if($transaction->status === 'pending' && $transaction->member->status !== 'inactive')<a href="{{ route('payments.create', ['transaction' => $transaction->id]) }}" class="flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Record payment</a>@endif
            @if($transaction->status === 'paid' && $transaction->member->status !== 'inactive')<form method="POST" action="{{ route('transactions.approve', $transaction) }}" data-confirm="Approve this fully paid transaction and activate the membership?">@csrf @method('PATCH')<button class="w-full inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Approve membership</button></form>@endif
            @if(in_array($transaction->status, ['pending', 'paid']) && $transaction->paidCents() === 0)<form method="POST" action="{{ route('transactions.cancel', $transaction) }}" data-confirm="Cancel this transaction?">@csrf @method('PATCH')<button class="w-full inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Cancel transaction</button></form>@endif
        </div>
        <p class="mt-5 text-[10px] leading-5 text-[#858496]">Full payment is required before approval. Transactions with recorded payments are kept for your financial records.</p>
    </div>
</div>
<div class="mt-6 rounded-xl bg-white p-6"><h3 class="mb-5 font-semibold">Payments</h3><div class="space-y-3">@forelse($transaction->payments as $payment)<a href="{{ route('payments.show', $payment) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-100 p-4 hover:border-[#270CC5]/30"><div><p class="text-xs font-medium text-[#270CC5]">{{ $payment->payment_number }}</p><p class="mt-1 text-[10px] text-[#858496]">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }} · {{ $payment->paid_at?->format('M j, Y g:i A') }} · {{ $payment->reference_number ?? 'Cash payment' }}</p></div><div class="flex items-center gap-4"><span class="text-xs font-medium">₱{{ number_format($payment->amount, 2) }}</span><x-status :status="$payment->status" /></div></a>@empty<p class="py-6 text-center text-xs text-[#858496]">No payments recorded yet.</p>@endforelse</div></div>
@endsection
