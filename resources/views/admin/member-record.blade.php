@extends('layout.layout')
@section('title', 'Membership record')
@section('content')
<x-page-heading title="Membership record"><div class="flex gap-2"><a href="{{ route('members.show', $member) }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Back to profile</a><button type="button" data-print class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Print record</button></div></x-page-heading>
<article class="mx-auto max-w-3xl rounded-xl bg-white p-8 sm:p-10 print:max-w-none">
    <p class="flex items-center gap-2 text-xl font-bold text-[#270CC5]"><x-logo class="size-7 shrink-0 text-yellow-400" />PUIHAHA</p><h2 class="mt-2 text-sm text-[#858496]">Membership record</h2>
    <div class="my-7 flex flex-wrap items-center justify-between gap-4 border-y border-slate-100 py-6"><div><h3 class="text-2xl font-semibold">{{ $member->user->name }}</h3><p class="mt-2 text-xs text-[#858496]">{{ $member->member_number }}</p></div><x-status :status="$member->status" /></div>
    <dl class="grid gap-7 text-xs sm:grid-cols-2">@foreach(['Email' => $member->user->email, 'Phone' => $member->phone_number ?? '—', 'Category' => $member->category?->name ?? 'Unassigned', 'Level' => $member->level?->name ?? 'Unassigned', 'Member since' => $member->joined_at?->format('F j, Y') ?? 'Awaiting approval', 'Valid until' => $member->expires_at?->format('F j, Y') ?? 'Awaiting approval'] as $label => $value)<div><dt class="mb-2 text-[#858496]">{{ $label }}</dt><dd class="font-medium">{{ $value }}</dd></div>@endforeach</dl>
    @if($member->level?->benefits)<div class="mt-8 border-t border-slate-100 pt-6"><h4 class="mb-3 text-xs font-semibold">Membership benefits</h4><p class="whitespace-pre-line text-xs leading-6 text-slate-600">{{ $member->level->benefits }}</p></div>@endif
    <p class="mt-10 text-[10px] text-[#858496]">Issued {{ now()->format('F j, Y') }} · PUIHAHA Admin workspace</p>
</article>
@endsection
