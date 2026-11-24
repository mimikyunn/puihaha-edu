@extends('layout.layout')
@section('title', 'Members')
@section('content')
<x-page-heading title="Members" description="Manage your community, one membership at a time."><a href="{{ route('members.create') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">+ Register member</a></x-page-heading>
<div class="rounded-xl bg-white p-5">
    <form method="GET" action="{{ route('members') }}" class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_1fr_auto]">
        <input aria-label="Search members" name="search" value="{{ request('search') }}" placeholder="Search name, email or member number…" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10">
        <select aria-label="Filter by category" name="category" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select>
        <select aria-label="Filter by level" name="level" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="">All levels</option>@foreach($levels as $level)<option value="{{ $level->id }}" @selected(request('level') == $level->id)>{{ $level->name }}</option>@endforeach</select>
        <select aria-label="Filter by status" name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="">All statuses</option>@foreach(['pending', 'active', 'expired', 'inactive'] as $status)<option @selected(request('status') === $status) value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select>
        <button class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Search</button>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="border-b border-slate-100 text-[10px] uppercase tracking-wider text-[#858496]"><tr>@foreach(['Member', 'Membership number', 'Category', 'Level', 'Status', 'Expires', ''] as $heading)<th class="whitespace-nowrap px-3 py-4 font-medium">{{ $heading }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($members as $member)<tr class="hover:bg-slate-50/70">
                <td class="px-3 py-4"><a href="{{ route('members.show', $member) }}" class="font-medium text-[#270CC5] hover:underline">{{ $member->user->name }}</a><p class="mt-1 text-[10px] text-[#858496]">{{ $member->user->email }}</p></td>
                <td class="whitespace-nowrap px-3 py-4 text-[10px]">{{ $member->member_number }}</td><td class="px-3 py-4">{{ $member->category?->name ?? 'Unassigned' }}</td><td class="px-3 py-4">{{ $member->level?->name ?? 'Unassigned' }}</td><td class="px-3 py-4"><x-status :status="$member->status" /></td><td class="whitespace-nowrap px-3 py-4">{{ $member->expires_at?->format('M j, Y') ?? '—' }}</td><td class="px-3 py-4"><a href="{{ route('members.edit', $member) }}" class="text-[#270CC5] hover:underline">Edit</a></td>
            </tr>@empty<tr><td colspan="7" class="py-16 text-center"><p class="font-medium">No members found</p><p class="mt-2 text-xs text-[#858496]">Register a member or try a different search.</p><a href="{{ route('members') }}" class="mt-3 inline-block text-[#270CC5]">Clear filters</a></td></tr>@endforelse</tbody>
        </table>
    </div><div class="mt-5">{{ $members->links() }}</div>
</div>
@endsection
