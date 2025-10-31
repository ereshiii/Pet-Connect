<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class LocationService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public static function calculateDistance(
        float $lat1, 
        float $lng1, 
        float $lat2, 
        float $lng2, 
        string $unit = 'km'
    ): float {
        $earthRadius = $unit === 'miles' ? 3959 : 6371; // Earth's radius in km or miles
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }

    /**
     * Get formatted distance with appropriate unit
     */
    public static function getFormattedDistance(float $distance): string
    {
        if ($distance < 1) {
            return round($distance * 1000) . ' m';
        }
        
        return round($distance, 1) . ' km';
    }

    /**
     * Verify if coordinates are within Philippines bounds
     */
    public static function isWithinPhilippines(float $lat, float $lng): bool
    {
        // Philippines bounds (approximate)
        $minLat = 4.5;
        $maxLat = 21.5;
        $minLng = 116.0;
        $maxLng = 127.0;
        
        return $lat >= $minLat && $lat <= $maxLat && 
               $lng >= $minLng && $lng <= $maxLng;
    }

    /**
     * Get user's location info from IP (fallback)
     */
    public static function getLocationFromIP(?string $ip = null): ?array
    {
        try {
            $ip = $ip ?: request()->ip();
            
            // Skip for local development
            if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
                return [
                    'lat' => 14.5995, // Manila default
                    'lng' => 120.9842,
                    'city' => 'Manila',
                    'region' => 'Metro Manila',
                    'country' => 'Philippines'
                ];
            }

            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    return [
                        'lat' => (float) $data['lat'],
                        'lng' => (float) $data['lon'],
                        'city' => $data['city'],
                        'region' => $data['regionName'],
                        'country' => $data['country']
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to get location from IP: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get clinics within specified radius
     */
    public static function getClinicsWithinRadius(
        float $userLat, 
        float $userLng, 
        float $radiusKm, 
        $clinics
    ): array {
        return $clinics->filter(function ($clinic) use ($userLat, $userLng, $radiusKm) {
            if (!$clinic->latitude || !$clinic->longitude) {
                return false;
            }
            
            $distance = self::calculateDistance(
                $userLat, 
                $userLng, 
                (float) $clinic->latitude, 
                (float) $clinic->longitude
            );
            
            return $distance <= $radiusKm;
        })->map(function ($clinic) use ($userLat, $userLng) {
            $distance = self::calculateDistance(
                $userLat, 
                $userLng, 
                (float) $clinic->latitude, 
                (float) $clinic->longitude
            );
            
            $clinic->distance_km = $distance;
            $clinic->formatted_distance = self::getFormattedDistance($distance);
            
            return $clinic;
        })->sortBy('distance_km')->values();
    }

    /**
     * Validate coordinates
     */
    public static function validateCoordinates(float $lat, float $lng): bool
    {
        return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
    }

    /**
     * Get travel time estimate (simplified)
     */
    public static function getEstimatedTravelTime(float $distanceKm): string
    {
        // Simple estimation: walking 5km/h, driving 30km/h in city
        $walkingMinutes = round(($distanceKm / 5) * 60);
        $drivingMinutes = round(($distanceKm / 30) * 60);
        
        if ($distanceKm < 2) {
            return "{$walkingMinutes} min walk";
        } elseif ($distanceKm < 10) {
            return "{$drivingMinutes} min drive / {$walkingMinutes} min walk";
        } else {
            return "{$drivingMinutes} min drive";
        }
    }
}