<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Optional OpenStreetMap Nominatim proxy (no API key).
 * Failures return empty results so manual location entry always remains usable.
 */
class GeoController extends Controller
{
    private const USER_AGENT = 'BalajiRoyalEventsWebsite/1.0 (contact: balajievents19@gmail.com)';

    public function reverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => self::USER_AGENT,
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 16,
                    'addressdetails' => 1,
                ]);

            if ($response->successful()) {
                $label = $this->formatNominatimLabel($response->json());
                if ($label !== '') {
                    return response()->json([
                        'data' => [
                            'label' => $label,
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'source' => 'nominatim',
                        ],
                    ]);
                }
            }
        } catch (ConnectionException) {
            // fall through to coordinate fallback
        }

        return response()->json([
            'data' => [
                'label' => sprintf('%.5f, %.5f', $lat, $lng),
                'latitude' => $lat,
                'longitude' => $lng,
                'source' => 'coordinates',
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        $query = trim($validated['q']);

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => self::USER_AGENT,
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'jsonv2',
                    'q' => $query,
                    'limit' => 6,
                    'addressdetails' => 1,
                ]);

            if ($response->successful()) {
                $results = collect($response->json())
                    ->filter(fn ($row) => is_array($row) && filled($row['display_name'] ?? null))
                    ->take(6)
                    ->map(fn (array $row) => [
                        'label' => (string) $row['display_name'],
                        'latitude' => isset($row['lat']) ? (float) $row['lat'] : null,
                        'longitude' => isset($row['lon']) ? (float) $row['lon'] : null,
                    ])
                    ->values()
                    ->all();

                return response()->json(['data' => $results]);
            }
        } catch (ConnectionException) {
            // empty list below
        }

        return response()->json(['data' => []]);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function formatNominatimLabel(?array $payload): string
    {
        if (! is_array($payload)) {
            return '';
        }

        $display = trim((string) ($payload['display_name'] ?? ''));
        if ($display !== '') {
            return $display;
        }

        $address = is_array($payload['address'] ?? null) ? $payload['address'] : [];
        $parts = array_filter([
            $address['suburb'] ?? $address['neighbourhood'] ?? $address['village'] ?? null,
            $address['city'] ?? $address['town'] ?? $address['county'] ?? null,
            $address['state'] ?? null,
            $address['country'] ?? null,
        ]);

        return implode(', ', $parts);
    }
}
