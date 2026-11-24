@extends('layout.layout')
@section('title', $member->exists ? 'Edit member' : 'Register member')
@section('content')
<x-page-heading :title="$member->exists ? 'Edit member' : 'Register a member'" description="Choose a category and level to get started."><a href="{{ route('members') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Back to members</a></x-page-heading>
<form action="{{ $member->exists ? route('members.update', $member) : route('members.store') }}" method="POST" class="max-w-3xl rounded-xl bg-white p-6 sm:p-8">
    @csrf @if($member->exists) @method('PUT') @endif
    <h3 class="mb-6 font-semibold">Member details</h3>
    <div class="grid gap-5 sm:grid-cols-2">
        <x-input name="name" label="Full name" :value="$member->user?->name" required maxlength="255" autocomplete="name" />
        <x-input name="email" label="Email address" type="email" :value="$member->user?->email" required maxlength="255" autocomplete="email" />
        <x-input name="phone_number" label="Phone number" type="tel" :value="$member->phone_number" maxlength="30" autocomplete="tel" />
        <x-input name="member_number" label="Membership number" :value="$member->member_number" :required="$member->exists" maxlength="60" placeholder="Automatically generated if left blank" />
        <x-field name="membership_category_id" label="Membership category" required><select id="membership_category_id" name="membership_category_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="">Select a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('membership_category_id', $member->membership_category_id) == $category->id)>{{ $category->name }} · ₱{{ number_format($category->fee, 2) }}{{ $category->is_active ? '' : ' (inactive)' }}</option>@endforeach</select></x-field>
        <x-field name="membership_level_id" label="Membership level" required><select id="membership_level_id" name="membership_level_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="">Select a level</option>@foreach($levels as $level)<option value="{{ $level->id }}" @selected(old('membership_level_id', $member->membership_level_id) == $level->id)>{{ $level->name }} · ₱{{ number_format($level->fee, 2) }} / {{ $level->duration_months }} months{{ $level->is_active ? '' : ' (inactive)' }}</option>@endforeach</select></x-field>
    </div>
    <x-member-location :member="$member" />
    <div class="mt-6 rounded-lg bg-[#F6F6FB] p-4 text-xs leading-6 text-[#858496]">{{ $member->exists ? 'Category and level changes apply to the next transaction. Complete or cancel any open transaction first.' : 'Registration creates a pending application. The total fee is the category fee plus the level fee. Membership starts after full payment and approval.' }}</div>
    <div class="mt-7 flex gap-3"><button class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">{{ $member->exists ? 'Save changes' : 'Register member' }}</button><a href="{{ route('members') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Cancel</a></div>
</form>
@endsection
