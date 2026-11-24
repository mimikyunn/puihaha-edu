@props(['name', 'label', 'value' => '', 'type' => 'text', 'required' => false])
<div>
    <label for="{{ $name }}" class="mb-2 block text-xs font-medium text-slate-700">{{ $label }} @if($required)<span class="text-[#270CC5]">*</span>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type !== 'password') value="{{ old($name, $value) }}" @endif @required($required) @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif {{ $attributes->class(['w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs outline-none transition focus:border-[#270CC5] focus:ring-2 focus:ring-[#270CC5]/10']) }}>
    @error($name)<p id="{{ $name }}-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
