@extends('layout.layout')
@section('title', $category->exists ? 'Edit category' : 'Add category')
@section('content')
<x-page-heading :title="$category->exists ? 'Edit category' : 'Add category'" description="Keep your membership options up to date."><a href="{{ route('member-categories') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Back to categories</a></x-page-heading>
<form method="POST" action="{{ $category->exists ? route('member-categories.update', $category) : route('member-categories.store') }}" class="max-w-3xl rounded-xl bg-white p-6 sm:p-8">
    @csrf @if($category->exists) @method('PUT') @endif
    <div class="grid gap-5 sm:grid-cols-2">
        <x-input name="name" label="Category name" :value="$category->name" required maxlength="100" />
        <x-input name="fee" label="Additional category fee (₱)" type="number" :value="$category->fee" required min="0" max="9999999.99" step="0.01" />
        
        <x-field name="description" label="Description" class="sm:col-span-2"><textarea id="description" name="description" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10">{{ old('description', $category->description) }}</textarea></x-field>
        
        <x-field name="is_active" label="Availability" required><select id="is_active" name="is_active" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10"><option value="1" @selected(old('is_active', $category->is_active) == 1)>Active</option><option value="0" @selected(old('is_active', $category->is_active) == 0)>Inactive</option></select></x-field>
    </div>
    <div class="mt-7 flex gap-3"><button class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">Save category</button><a href="{{ route('member-categories') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Cancel</a></div>
</form>
@endsection
