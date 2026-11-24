@extends('layout.layout')
@section('title', 'Payment receipt')
@section('content')
<x-page-heading title="Payment receipt" :description="$payment->payment_number"><div class="flex gap-2"><a href="{{ route('transactions.show', $payment->transaction) }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">View transaction</a><button type="button" data-print class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Print receipt</button></div></x-page-heading>
<article class="mx-auto max-w-3xl rounded-xl bg-white p-7 sm:p-10 print:max-w-none print:rounded-none">
    <div class="flex flex-wrap items-start justify-between gap-5 border-b border-slate-100 pb-7"><div><p class="flex items-center gap-2 text-xl font-bold text-[#270CC5]"><x-logo class="size-7 shrink-0 text-yellow-400" />PUIHAHA</p><p class="mt-2 text-xs text-[#858496]">Membership payment receipt</p></div><div class="text-right"><x-status :status="$payment->status" /><p class="mt-3 text-[10px] text-[#858496]">{{ $payment->paid_at?->format('F j, Y · g:i A') ?? 'Awaiting payment' }}</p></div></div>
    <dl class="mt-7 grid gap-6 text-xs sm:grid-cols-2">
        @foreach(['Receipt number' => $payment->payment_number, 'Transaction number' => $payment->transaction->transaction_number, 'Received from' => $payment->transaction->member->user->name, 'Membership number' => $payment->transaction->member->member_number, 'Payment method' => ucfirst(str_replace('_', ' ', $payment->payment_method)), 'External reference' => $payment->reference_number ?? '—'] as $label => $value)<div><dt class="mb-2 text-[#858496]">{{ $label }}</dt><dd class="break-all font-medium">{{ $value }}</dd></div>@endforeach
    </dl>
    <div class="mt-8 flex items-center justify-between rounded-lg bg-[#F6F6FB] p-5"><span class="text-xs font-medium">Amount received</span><span class="text-2xl font-semibold text-[#270CC5]">₱{{ number_format($payment->amount, 2) }}</span></div>
    <p class="mt-5 text-xs text-[#858496]">Remaining transaction balance: ₱{{ number_format($payment->transaction->balanceCents() / 100, 2) }}</p>
    <p class="mt-10 border-t border-slate-100 pt-5 text-center text-[10px] leading-5 text-[#858496]">Thank you for being part of PUIHAHA.<br>Keep this receipt for your records.</p>
</article>
@endsection
