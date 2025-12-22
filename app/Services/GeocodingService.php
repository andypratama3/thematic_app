<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private $providers = ['nominatim', 'photon'];
    private $currentProvider = 0;

    /**
     * Geocode address to coordinates using multiple providers
     */
    public function geocode($address, $village = null, $district = null, $regency = null, $province = null)
    {
        // Build full address
        $fullAddress = $this->buildFullAddress($address, $village, $district, $regency, $province);

        $cacheKey = 'geocode_' . md5($fullAddress);

        return Cache::remember($cacheKey, 86400 * 7, function () use ($fullAddress, $district, $regency) {
            // Try multiple geocoding methods
            $coords = null;

            // Method 1: Try Nominatim
            $coords = $this->geocodeWithNominatim($fullAddress);

            // Method 2: If failed, try Photon
            if (!$coords) {
                $coords = $this->geocodeWithPhoton($fullAddress);
            }

            // Method 3: If still failed, use predefined coordinates by district
            if (!$coords && $district) {
                $coords = $this->getCoordinatesByDistrict($district);
            }

            // Method 4: Last resort - use regency center
            if (!$coords && $regency) {
                $coords = $this->getCoordinatesByRegency($regency);
            }

            // Method 5: Ultimate fallback - Kediri center
            if (!$coords) {
                $coords = [
                    'latitude' => -7.8167 + (mt_rand(-200, 200) / 10000),
                    'longitude' => 112.0167 + (mt_rand(-200, 200) / 10000),
                    'source' => 'fallback'
                ];
            }

            return $coords;
        });
    }

    /**
     * Build full address string
     */
    private function buildFullAddress($address, $village, $district, $regency, $province)
    {
        $parts = array_filter([
            $village,
            $district,
            $regency ?: 'KEDIRI',
            $province ?: 'JAWA TIMUR',
            'Indonesia'
        ]);

        return implode(', ', $parts);
    }

    /**
     * Geocode using Nominatim (OpenStreetMap)
     */
    private function geocodeWithNominatim($address)
    {
        try {
            usleep(1000000); // 1 second delay for rate limiting

            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'GIS-Application/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'id',
                    'addressdetails' => 1
                ]);

            if ($response->successful() && $data = $response->json()) {
                if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                    Log::info("Nominatim geocoded: {$address} -> {$data[0]['lat']}, {$data[0]['lon']}");

                    return [
                        'latitude' => (float)$data[0]['lat'],
                        'longitude' => (float)$data[0]['lon'],
                        'source' => 'nominatim',
                        'display_name' => $data[0]['display_name'] ?? null
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Nominatim geocoding failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Geocode using Photon (Komoot)
     */
    private function geocodeWithPhoton($address)
    {
        try {
            $response = Http::timeout(5)
                ->get('https://photon.komoot.io/api/', [
                    'q' => $address,
                    'limit' => 1
                ]);

            if ($response->successful() && $data = $response->json()) {
                if (!empty($data['features'])) {
                    $coords = $data['features'][0]['geometry']['coordinates'];

                    Log::info("Photon geocoded: {$address} -> {$coords[1]}, {$coords[0]}");

                    return [
                        'latitude' => (float)$coords[1],
                        'longitude' => (float)$coords[0],
                        'source' => 'photon'
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Photon geocoding failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get predefined coordinates by district
     */
    private function getCoordinatesByDistrict($district)
    {
        $districtCoords = [
            // Kabupaten Kediri
            'KANDAT' => ['lat' => -7.8167, 'lng' => 112.0167],
            'GROGOL' => ['lat' => -7.7500, 'lng' => 112.0000],
            'NGADILUWIH' => ['lat' => -7.9667, 'lng' => 111.9333],
            'PARE' => ['lat' => -7.7667, 'lng' => 112.2000],
            'WATES' => ['lat' => -7.8333, 'lng' => 112.0833],
            'GURAH' => ['lat' => -7.8667, 'lng' => 112.0333],
            'PUNCU' => ['lat' => -7.9000, 'lng' => 112.1167],
            'PLOSOKLATEN' => ['lat' => -7.8500, 'lng' => 112.1500],
            'KEPUNG' => ['lat' => -7.7833, 'lng' => 112.0500],
            'MOJO' => ['lat' => -7.7167, 'lng' => 112.0500],
            'SEMEN' => ['lat' => -7.7000, 'lng' => 112.0833],
            'KRAS' => ['lat' => -7.7333, 'lng' => 112.1167],
            'NGANCAR' => ['lat' => -7.7833, 'lng' => 111.9500],
            'PAGU' => ['lat' => -7.8000, 'lng' => 111.8833],
            'PAPAR' => ['lat' => -7.7667, 'lng' => 111.9833],
            'PLEMAHAN' => ['lat' => -7.8167, 'lng' => 111.9667],
            'PURWOASRI' => ['lat' => -7.8333, 'lng' => 111.9167],
            'GAMPENGREJO' => ['lat' => -7.9000, 'lng' => 111.9833],
            'RINGINREJO' => ['lat' => -7.8500, 'lng' => 111.9500],
            'BADAS' => ['lat' => -7.8167, 'lng' => 112.0667],
            'KANDANGAN' => ['lat' => -7.8833, 'lng' => 112.0500],
            'KAYEN KIDUL' => ['lat' => -7.8000, 'lng' => 112.1000],
            'KEPUNG' => ['lat' => -7.7833, 'lng' => 112.0500],
            'TAROKAN' => ['lat' => -7.7500, 'lng' => 112.1333],
        ];

        $districtUpper = strtoupper(trim($district));

        foreach ($districtCoords as $name => $coords) {
            if (strpos($districtUpper, $name) !== false || strpos($name, $districtUpper) !== false) {
                Log::info("Using predefined coordinates for district: {$district}");

                return [
                    'latitude' => $coords['lat'] + (mt_rand(-200, 200) / 10000),
                    'longitude' => $coords['lng'] + (mt_rand(-200, 200) / 10000),
                    'source' => 'predefined_district'
                ];
            }
        }

        return null;
    }

    /**
     * Get coordinates by regency
     */
    private function getCoordinatesByRegency($regency)
    {
        $regencyCoords = [
            'KEDIRI' => ['lat' => -7.8167, 'lng' => 112.0167],
            'NGANJUK' => ['lat' => -7.6000, 'lng' => 111.9000],
            'TULUNGAGUNG' => ['lat' => -8.0667, 'lng' => 111.9000],
            'BLITAR' => ['lat' => -8.0983, 'lng' => 112.1681],
            'MALANG' => ['lat' => -7.9797, 'lng' => 112.6304],
            'JOMBANG' => ['lat' => -7.5500, 'lng' => 112.2333],
        ];

        $regencyUpper = strtoupper(trim($regency));

        foreach ($regencyCoords as $name => $coords) {
            if (strpos($regencyUpper, $name) !== false) {
                Log::info("Using predefined coordinates for regency: {$regency}");

                return [
                    'latitude' => $coords['lat'] + (mt_rand(-500, 500) / 10000),
                    'longitude' => $coords['lng'] + (mt_rand(-500, 500) / 10000),
                    'source' => 'predefined_regency'
                ];
            }
        }

        return null;
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode($latitude, $longitude)
    {
        $cacheKey = 'reverse_geocode_' . md5($latitude . '_' . $longitude);

        return Cache::remember($cacheKey, 86400, function () use ($latitude, $longitude) {
            try {
                usleep(1000000); // Rate limiting

                $response = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'GIS-Application/1.0'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'json',
                        'addressdetails' => 1
                    ]);

                if ($response->successful() && $data = $response->json()) {
                    return [
                        'display_name' => $data['display_name'] ?? null,
                        'address' => $data['address'] ?? [],
                        'village' => $data['address']['village'] ?? null,
                        'district' => $data['address']['suburb'] ?? $data['address']['county'] ?? null,
                        'regency' => $data['address']['city'] ?? $data['address']['state_district'] ?? null,
                        'province' => $data['address']['state'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Reverse geocoding error: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Batch geocode multiple addresses (with throttling)
     */
    public function batchGeocode(array $addresses)
    {
        $results = [];

        foreach ($addresses as $key => $address) {
            $results[$key] = $this->geocode(
                $address['address'] ?? '',
                $address['village'] ?? null,
                $address['district'] ?? null,
                $address['regency'] ?? null,
                $address['province'] ?? null
            );

            // Throttle to avoid rate limiting
            if (($key + 1) % 5 == 0) {
                sleep(2);
            }
        }

        return $results;
    }

    /**
     * Validate coordinates
     */
    public function validateCoordinates($latitude, $longitude)
    {
        // Indonesia bounds
        $minLat = -11;
        $maxLat = 6;
        $minLng = 95;
        $maxLng = 141;

        return is_numeric($latitude) && is_numeric($longitude) &&
               $latitude >= $minLat && $latitude <= $maxLat &&
               $longitude >= $minLng && $longitude <= $maxLng;
    }
}
