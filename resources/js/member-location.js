const locationPanel = document.querySelector('[data-member-location]');

if (locationPanel) {
    const readOnly = locationPanel.dataset.readonly === 'true';
    const latitudeInput = locationPanel.querySelector('[name="latitude"]');
    const longitudeInput = locationPanel.querySelector('[name="longitude"]');
    const coordinates = locationPanel.querySelector('[data-coordinates]');
    const weatherStatus = locationPanel.querySelector('[data-weather-status]');
    const mapStatus = locationPanel.querySelector('[data-map-status]');
    const mapCanvas = locationPanel.querySelector('[data-map-canvas]');
    let map;
    let marker;
    let markerClass;
    let weatherRequest;
    let weatherTimer;

    function selectedLocation() {
        const lat = readOnly ? locationPanel.dataset.latitude : latitudeInput.value;
        const lng = readOnly ? locationPanel.dataset.longitude : longitudeInput.value;
        if (lat === '' || lng === '') {
            return null;
        }
        const point = { lat: Number(lat), lng: Number(lng) };
        return Number.isFinite(point.lat) && Number.isFinite(point.lng) && Math.abs(point.lat) <= 90 && Math.abs(point.lng) <= 180 ? point : null;
    }

    async function loadWeather(point) {
        weatherRequest?.abort();
        if (!point) {
            weatherStatus.textContent = 'Choose a location to see current weather.';
            return;
        }
        if (locationPanel.dataset.weatherEnabled !== 'true') {
            weatherStatus.textContent = 'Weather is currently unavailable.';
            return;
        }
        const request = new AbortController();
        weatherRequest = request;
        weatherStatus.textContent = 'Loading current weather…';
        const url = new URL(locationPanel.dataset.weatherUrl, window.location.origin);
        url.searchParams.set('lat', point.lat);
        url.searchParams.set('lon', point.lng);
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: request.signal });
            if (!response.ok) {
                throw new Error('Weather unavailable');
            }
            const weather = await response.json();
            if (!request.signal.aborted) {
                weatherStatus.textContent = `${weather.place}: ${weather.temperature}°C, ${weather.description}. Humidity: ${weather.humidity}%.`;
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                weatherStatus.textContent = 'Weather is currently unavailable. Please try again later.';
            }
        }
    }

    function showMarker(point) {
        if (!map || !markerClass) {
            return;
        }
        if (!point) {
            if (marker) {
                marker.map = null;
                marker = null;
            }
            return;
        }
        if (marker) {
            marker.position = point;
        } else {
            marker = new markerClass({ map, position: point, title: 'Member location', gmpDraggable: !readOnly });
            if (!readOnly) {
                marker.addListener('dragend', (event) => {
                    if (event.latLng) {
                        setLocation({ lat: event.latLng.lat(), lng: event.latLng.lng() });
                    }
                });
            }
        }
    }

    function refreshLocation() {
        const point = selectedLocation();
        coordinates.textContent = point ? `Latitude: ${point.lat.toFixed(5)} · Longitude: ${point.lng.toFixed(5)}` : 'No location selected. Enter both latitude and longitude.';
        showMarker(point);
        weatherRequest?.abort();
        clearTimeout(weatherTimer);
        weatherTimer = setTimeout(() => loadWeather(point), 400);
    }

    function setLocation(point) {
        latitudeInput.value = point.lat.toFixed(7);
        longitudeInput.value = point.lng.toFixed(7);
        map?.panTo(point);
        refreshLocation();
    }

    if (!readOnly) {
        latitudeInput.addEventListener('input', refreshLocation);
        longitudeInput.addEventListener('input', refreshLocation);
        locationPanel.querySelector('[data-use-manila]').addEventListener('click', () => setLocation({ lat: 14.5995, lng: 120.9842 }));
        locationPanel.querySelector('[data-clear-location]').addEventListener('click', () => {
            latitudeInput.value = '';
            longitudeInput.value = '';
            refreshLocation();
        });
    }

    function mapUnavailable() {
        mapCanvas.classList.add('hidden');
        mapStatus.classList.remove('hidden');
        mapStatus.textContent = readOnly ? 'The map is currently unavailable.' : 'The map is currently unavailable. You can still enter coordinates above.';
    }

    async function initializeMap() {
        try {
            const { Map } = await window.google.maps.importLibrary('maps');
            const { AdvancedMarkerElement } = await window.google.maps.importLibrary('marker');
            markerClass = AdvancedMarkerElement;
            mapCanvas.classList.remove('hidden');
            map = new Map(mapCanvas, { center: selectedLocation() || { lat: 14.5995, lng: 120.9842 }, zoom: 13, mapId: locationPanel.dataset.mapId || 'DEMO_MAP_ID', streetViewControl: false, mapTypeControl: false });
            showMarker(selectedLocation());
            if (!readOnly) {
                map.addListener('click', (event) => {
                    if (event.latLng) {
                        setLocation({ lat: event.latLng.lat(), lng: event.latLng.lng() });
                    }
                });
            }
            mapStatus.classList.add('hidden');
        } catch {
            mapUnavailable();
        }
    }

    if (locationPanel.dataset.mapsKey) {
        if (window.google?.maps?.importLibrary) {
            initializeMap();
        } else {
            window.initMembershipLocation = initializeMap;
            window.gm_authFailure = mapUnavailable;
            const script = document.createElement('script');
            const url = new URL('https://maps.googleapis.com/maps/api/js');
            url.search = new URLSearchParams({ key: locationPanel.dataset.mapsKey, loading: 'async', callback: 'initMembershipLocation', v: 'weekly' }).toString();
            script.src = url.toString();
            script.async = true;
            script.onerror = mapUnavailable;
            document.head.append(script);
        }
    }
    refreshLocation();
}
