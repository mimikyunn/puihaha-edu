@props(['name', 'label', 'required' => false])
<div {{ $attributes }}>
    <label for="{{ $name }}" class="mb-2 block text-xs font-medium text-slate-700">{{ $label }} @if($required)<span class="text-[#270CC5]">*</span>@endif</label>
    {{ $slot }}
    @error($name)<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
