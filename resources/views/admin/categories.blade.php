@extends('layout.layout')
@section('title', 'Membership categories')
@section('content')
<x-page-heading title="Membership categories" description="Organize members into categories with an optional category fee."><a href="{{ route('member-categories.create') }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#270CC5] px-4 py-2.5 text-xs font-medium text-white transition hover:bg-[#1800A6]">+ Add category</a></x-page-heading>
<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-2">
@forelse($categories as $category)
    <article class="flex flex-col rounded-xl bg-white p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><span class="flex size-10 items-center justify-center rounded-lg bg-[#270CC5]/5 text-[#270CC5]"><x-icon name="categories" class="size-5"/></span><x-status :status="$category->is_active ? 'active' : 'inactive'" /></div>
        <h3 class="text-base font-semibold">{{ $category->name }}</h3>
        <p class="mt-2 min-h-10 text-xs leading-5 text-[#858496]">{{ $category->description ?: 'No description added.' }}</p>
        <p class="mt-5 text-2xl font-semibold">₱{{ number_format($category->fee, 2) }}</p><p class="mt-1 text-[10px] text-[#858496]">Additional category fee</p>
        
        <p class="mb-5 mt-4 text-[10px] text-[#858496]">{{ $category->members_count }} {{ Str::plural('member', $category->members_count) }}</p>
        <div class="mt-auto flex flex-wrap gap-2 border-t border-slate-100 pt-4"><a href="{{ route('member-categories.edit', $category) }}" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Edit category</a>@if($category->is_active)<form method="POST" action="{{ route('member-categories.destroy', $category) }}" data-confirm="Deactivate this category? Existing records will be kept.">@csrf @method('DELETE')<button class="cursor-pointer px-2 py-2.5 text-xs text-[#858496] hover:text-red-600">Deactivate</button></form>@endif</div>
    </article>
@empty<div class="col-span-full rounded-xl bg-white p-12 text-center"><p class="font-medium">No categories yet</p><p class="mt-2 text-xs text-[#858496]">Add a category to start registering members.</p></div>@endforelse
</div>
<div class="mt-6">{{ $categories->links() }}</div>
@endsection
