<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scooter;
use Illuminate\Http\Request;

class MobileScooterController extends Controller
{
    /**
     * Get nearby scooters based on latitude and longitude
     */
    public function getNearbyScooters(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:0', // No max limit - show all scooters
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            $radius = (float) ($request->input('radius', 50000)); // Default 50km (increased to show all scooters)

            \Log::info('🛴 Fetching nearby scooters', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius' => $radius,
            ]);

            // Get all active scooters with GPS coordinates
            // Exclude rented scooters - they should not appear on the map
            $scooters = Scooter::where('is_active', true)
                ->where('status', '!=', 'rented') // لا نعرض السكوترات المؤجرة
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->get();

            \Log::info('📊 Found scooters in database', [
                'total_scooters' => $scooters->count(),
                'scooters' => $scooters->map(function($s) {
                    return [
                        'id' => $s->id,
                        'code' => $s->code,
                        'lat' => $s->latitude,
                        'lng' => $s->longitude,
                        'status' => $s->status,
                    ];
                })->toArray(),
            ]);

            // Calculate distance for each scooter (but don't filter - show all scooters)
            $nearbyScooters = $scooters->map(function ($scooter) use ($latitude, $longitude) {
                $distance = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    (float) $scooter->latitude,
                    (float) $scooter->longitude
                );
                $scooter->distance = $distance; // Add distance to scooter object
                \Log::debug('📍 Scooter distance calculated', [
                    'scooter_id' => $scooter->id,
                    'code' => $scooter->code,
                    'scooter_lat' => $scooter->latitude,
                    'scooter_lng' => $scooter->longitude,
                    'user_lat' => $latitude,
                    'user_lng' => $longitude,
                    'distance' => round($distance, 2),
                ]);
                return $scooter;
            })
            ->sortBy('distance') // Sort by distance but don't filter
            ->values();

            \Log::info('✅ Nearby scooters filtered', [
                'total_nearby' => $nearbyScooters->count(),
                'radius' => $radius,
            ]);

            // Format response
            $data = $nearbyScooters->map(function ($scooter) {
                return [
                    'id' => $scooter->id,
                    'code' => $scooter->code,
                    'latitude' => (float) $scooter->latitude,
                    'longitude' => (float) $scooter->longitude,
                    'battery_percentage' => $scooter->battery_percentage ?? 0,
                    'is_available' => $scooter->status === 'available',
                    'is_locked' => (bool) $scooter->is_locked,
                    'status' => $scooter->status,
                    'distance' => round($scooter->distance, 2), // Distance in meters
                    'last_seen_at' => $scooter->last_seen_at?->toIso8601String(),
                ];
            });

            \Log::info('✅ Returning nearby scooters', [
                'count' => $data->count(),
                'scooters' => $data->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب السكوترات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all available scooters (without location filter)
     */
    public function getAllScooters(Request $request)
    {
        try {
            // Exclude rented scooters - they should not appear on the map
            $scooters = Scooter::where('is_active', true)
                ->where('status', '!=', 'rented') // لا نعرض السكوترات المؤجرة
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            $data = $scooters->map(function ($scooter) {
                return [
                    'id' => $scooter->id,
                    'code' => $scooter->code,
                    'latitude' => (float) $scooter->latitude,
                    'longitude' => (float) $scooter->longitude,
                    'battery_percentage' => $scooter->battery_percentage ?? 0,
                    'is_available' => $scooter->status === 'available',
                    'is_locked' => (bool) $scooter->is_locked,
                    'status' => $scooter->status,
                    'last_seen_at' => $scooter->last_seen_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب السكوترات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get scooter details by ID
     */
    public function getScooterDetails(Request $request, int $id)
    {
        try {
            $scooter = Scooter::where('is_active', true)->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $scooter->id,
                    'code' => $scooter->code,
                    'latitude' => (float) $scooter->latitude,
                    'longitude' => (float) $scooter->longitude,
                    'battery_percentage' => $scooter->battery_percentage ?? 0,
                    'is_available' => $scooter->status === 'available',
                    'is_locked' => (bool) $scooter->is_locked,
                    'status' => $scooter->status,
                    'last_seen_at' => $scooter->last_seen_at?->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'السكوتر غير موجود',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
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
}
