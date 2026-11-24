@extends('layout.layout')
@section('title', $level->exists ? 'Edit level' : 'Add level')
@section('content')
<x-page-heading :title="$level->exists ? 'Edit level' : 'Add level'" description="Keep your membership options up to date."><a href="{{ route('member-levels') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Back to levels</a></x-page-heading>
<form method="POST" action="{{ $level->exists ? route('member-levels.update', $level) : route('member-levels.store') }}" class="max-w-3xl rounded-xl bg-white p-6 sm:p-8">
    @csrf @if($level->exists) @method('PUT') @endif
    <div class="grid gap-5 sm:grid-cols-2">
        <x-input name="name" label="Level name" :value="$level->name" required maxlength="100" />
        <x-input name="fee" label="Level fee (₱)" type="number" :value="$level->fee" required min="0" max="9999999.99" step="0.01" />
        <x-input name="duration_months" label="Validity (months)" type="number" :value="$level->duration_months" required min="1" max="120" /><x-input name="rank" label="Display order" type="number" :value="$level->rank" required min="0" max="999" />
        <x-field name="description" label="Description" class="sm:col-span-2"><textarea id="description" name="description" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10">{{ old('description', $level->description) }}</textarea></x-field>
        <x-field name="benefits" label="Benefits" class="sm:col-span-2"><textarea id="benefits" name="benefits" rows="5" maxlength="5000" placeholder="Add one benefit per line" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10">{{ old('benefits', $level->benefits) }}</textarea></x-field>
        <x-field name="is_active" label="Availability" required><select id="is_active" name="is_active" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="1" @selected(old('is_active', $level->is_active) == 1)>Active</option><option value="0" @selected(old('is_active', $level->is_active) == 0)>Inactive</option></select></x-field>
    </div>
    <div class="mt-7 flex gap-3"><button class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Save level</button><a href="{{ route('member-levels') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Cancel</a></div>
</form>
@endsection
