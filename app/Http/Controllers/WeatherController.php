<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class WeatherController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);
        if (blank(config('services.openweather.key'))) {
            return response()->json(['message' => 'Weather is currently unavailable.'], 503);
        }
        $latitude = round((float) $data['lat'], 3);
        $longitude = round((float) $data['lon'], 3);
        try {
            $weather = Cache::remember('weather:'.$latitude.':'.$longitude, now()->addMinutes(10), function () use ($latitude, $longitude): array {
                $weather = Http::acceptJson()->connectTimeout(3)->timeout(8)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'units' => 'metric',
                    'appid' => config('services.openweather.key'),
                ])->throw()->json();
                if (! is_numeric(data_get($weather, 'main.temp')) || ! is_numeric(data_get($weather, 'main.humidity')) || ! is_string(data_get($weather, 'weather.0.description'))) {
                    throw new UnexpectedValueException('Incomplete weather response.');
                }

                return [
                    'place' => is_string(data_get($weather, 'name')) ? $weather['name'] : 'Selected location',
                    'temperature' => (float) $weather['main']['temp'],
                    'description' => $weather['weather'][0]['description'],
                    'humidity' => (int) $weather['main']['humidity'],
                ];
            });
        } catch (ConnectionException|RequestException|UnexpectedValueException) {
            return response()->json(['message' => 'Weather is currently unavailable. Please try again later.'], 503);
        }

        return response()->json($weather);
    }
}
