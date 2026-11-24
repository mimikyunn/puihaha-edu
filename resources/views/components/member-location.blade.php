@props(['member', 'readonly' => false])
@php
    $latitude = $readonly ? $member->latitude : old('latitude', $member->latitude);
    $longitude = $readonly ? $member->longitude : old('longitude', $member->longitude);
@endphp
<section data-member-location data-readonly="{{ $readonly ? 'true' : 'false' }}" data-latitude="{{ $latitude }}" data-longitude="{{ $longitude }}" data-maps-key="{{ config('services.google_maps.key') }}" data-map-id="{{ config('services.google_maps.map_id') }}" data-weather-url="{{ route('weather.show') }}" data-weather-enabled="{{ filled(config('services.openweather.key')) ? 'true' : 'false' }}" class="mt-7 border-t border-slate-100 pt-6">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3"><div><h3 class="text-sm font-semibold">Member location</h3><p class="mt-1.5 text-xs leading-5 text-[#858496]">{{ $readonly ? 'Saved address and current weather at this location.' : 'Optionally add an address and choose a point on the map, or enter coordinates below.' }}</p></div>@unless($readonly)<button type="button" data-clear-location class="cursor-pointer text-xs text-[#270CC5] hover:underline">Clear location</button>@endunless</div>
    @if($readonly)
        <p class="mb-4 text-xs leading-6 text-slate-600">{{ $member->address ?: 'No street address provided.' }}</p>
        <p data-coordinates class="mb-4 text-xs text-[#858496]">{{ $latitude !== null && $longitude !== null ? 'Latitude: '.$latitude.' · Longitude: '.$longitude : 'No coordinates saved.' }}</p>
    @else
        <x-input name="address" label="Street address" :value="$member->address" maxlength="255" autocomplete="street-address" />
        <div class="my-5 grid gap-5 sm:grid-cols-2"><x-input name="latitude" label="Latitude" type="number" :value="$member->latitude" min="-90" max="90" step="any" placeholder="e.g. 14.5995" /><x-input name="longitude" label="Longitude" type="number" :value="$member->longitude" min="-180" max="180" step="any" placeholder="e.g. 120.9842" /></div>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><p data-coordinates class="text-[10px] text-[#858496]">Choose a location to save coordinates.</p><button type="button" data-use-manila class="cursor-pointer text-xs text-[#270CC5] hover:underline">Use Manila center</button></div>
    @endif
    <div data-map-canvas class="hidden h-72 w-full overflow-hidden rounded-lg border border-slate-200" aria-label="Member location map"></div>
    <p data-map-status role="status" class="rounded-lg bg-[#F6F6FB] px-4 py-3 text-xs leading-6 text-[#858496]">{{ filled(config('services.google_maps.key')) ? 'Loading location map…' : ($readonly ? 'The map is currently unavailable.' : 'The map is currently unavailable. You can still enter coordinates above.') }}</p>
    <div class="mt-4 rounded-lg border border-indigo-100 bg-indigo-50/40 p-4"><h4 class="text-xs font-medium text-[#270CC5]">Current weather</h4><p data-weather-status role="status" aria-live="polite" class="mt-2 text-xs leading-6 text-slate-600">Choose a location to see current weather.</p><p class="mt-2 text-[10px] text-[#858496]">Weather data by OpenWeather</p></div>
</section>
