@props(['title', 'description' => ''])
<div class="mb-6 flex flex-wrap items-center justify-between gap-4 print:hidden">
    <div><h2 class="text-xl font-semibold tracking-tight">{{ $title }}</h2>@if($description)<p class="mt-1.5 text-xs leading-5 text-[#858496]">{{ $description }}</p>@endif</div>
    {{ $slot }}
</div>
