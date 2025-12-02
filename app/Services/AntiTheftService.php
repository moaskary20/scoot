<?php

namespace App\Services;

use App\Models\Scooter;
use App\Models\Trip;
use App\Repositories\ScooterLogRepository;
use Illuminate\Support\Facades\DB;

class AntiTheftService
{
    public function __construct(
        private readonly ScooterLogRepository $logRepository,
    ) {
        //
    }

    /**
     * Check for unauthorized movement (movement without active trip)
     * This should be called periodically (e.g., via scheduled task) or when GPS updates
     */
    public function checkForUnauthorizedMovement(Scooter $scooter, float $newLatitude, float $newLongitude): bool
    {
        // If scooter is locked, no movement should be possible
        if ($scooter->is_locked) {
            return false;
        }

        // Check if scooter has an active trip
        $activeTrip = Trip::where('scooter_id', $scooter->id)
            ->where('status', 'active')
            ->whereNotNull('start_time')
            ->first();

        // If there's an active trip, movement is authorized
        if ($activeTrip) {
            return false;
        }

        // If no active trip and scooter has moved, it's unauthorized
        if ($scooter->latitude && $scooter->longitude) {
            $distance = $this->calculateDistance(
                $scooter->latitude,
                $scooter->longitude,
                $newLatitude,
                $newLongitude
            );

            // If moved more than 10 meters, consider it unauthorized movement
            if ($distance > 10) {
                $this->logRepository->logForcedMovement($scooter, [
                    'old_latitude' => $scooter->latitude,
                    'old_longitude' => $scooter->longitude,
                    'new_latitude' => $newLatitude,
                    'new_longitude' => $newLongitude,
                    'distance_meters' => round($distance, 2),
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * Update scooter GPS and check for unauthorized movement
     */
    public function updateScooterGPS(Scooter $scooter, float $latitude, float $longitude, ?int $batteryPercentage = null): Scooter
    {
        $oldBattery = $scooter->battery_percentage;
        $oldLatitude = $scooter->latitude;
        $oldLongitude = $scooter->longitude;

        // Check for unauthorized movement before updating
        $this->checkForUnauthorizedMovement($scooter, $latitude, $longitude);

        // Update GPS
        $scooter->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'last_seen_at' => now(),
            'battery_percentage' => $batteryPercentage ?? $scooter->battery_percentage,
        ]);

        // Log GPS update
        $this->logRepository->create([
            'scooter_id' => $scooter->id,
            'event_type' => 'gps_update',
            'title' => 'GPS Location Updated',
            'description' => 'Scooter GPS coordinates updated',
            'severity' => 'info',
            'data' => [
                'old_latitude' => $oldLatitude,
                'old_longitude' => $oldLongitude,
                'new_latitude' => $latitude,
                'new_longitude' => $longitude,
            ],
            'latitude' => $latitude,
            'longitude' => $longitude,
            'scooter_status' => $scooter->status,
            'battery_percentage' => $scooter->battery_percentage,
            'was_locked' => $scooter->is_locked,
        ]);

        // Check for battery drop
        if ($batteryPercentage !== null && $oldBattery !== null && $batteryPercentage < $oldBattery) {
            $this->logRepository->logBatteryDrop($scooter, $oldBattery, $batteryPercentage);
        }

        return $scooter->fresh();
    }

    /**
     * Check if scooter is in allowed zone
     */
    public function checkZoneExit(Scooter $scooter, ?Trip $trip = null): bool
    {
        if (!$scooter->latitude || !$scooter->longitude) {
            return false;
        }

        // Get active allowed zones
        $allowedZones = DB::table('geo_zones')
            ->where('type', 'allowed')
            ->where('is_active', true)
            ->get();

        $isInAllowedZone = false;

        foreach ($allowedZones as $zone) {
            $polygon = json_decode($zone->polygon, true);
            if ($this->isPointInPolygon($scooter->latitude, $scooter->longitude, $polygon)) {
                $isInAllowedZone = true;
                break;
            }
        }

        // If there are no allowed zones defined, consider everywhere as allowed
        if ($allowedZones->isEmpty()) {
            return false;
        }

        // If not in any allowed zone, it's a zone exit
        if (!$isInAllowedZone) {
            $this->logRepository->logZoneExit($scooter, $trip, [
                'message' => 'Scooter exited allowed zone',
                'latitude' => $scooter->latitude,
                'longitude' => $scooter->longitude,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Calculate distance between two coordinates in meters (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a point is inside a polygon (Ray casting algorithm)
     */
    private function isPointInPolygon(float $latitude, float $longitude, array $polygon): bool
    {
        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lat'] ?? $polygon[$i][0] ?? 0;
            $yi = $polygon[$i]['lng'] ?? $polygon[$i][1] ?? 0;
            $xj = $polygon[$j]['lat'] ?? $polygon[$j][0] ?? 0;
            $yj = $polygon[$j]['lng'] ?? $polygon[$j][1] ?? 0;

            $intersect = (($yi > $longitude) != ($yj > $longitude)) &&
                ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
    }
}

