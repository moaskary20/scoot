<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Scooter;
use App\Models\GeoZone;
use App\Repositories\TripRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class MobileTripController extends Controller
{
    public function __construct(
        private readonly TripRepository $tripRepository,
    ) {
        //
    }

    /**
     * Get authenticated user's trips
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = (int) $request->get('per_page', 20);

            $trips = $this->tripRepository->getUserTrips($user->id, $perPage);

            $data = $trips->getCollection()->map(function (Trip $trip) {
                return [
                    'id' => $trip->id,
                    'scooter_code' => $trip->scooter?->code,
                    'start_time' => optional($trip->start_time)->toDateTimeString(),
                    'end_time' => optional($trip->end_time)->toDateTimeString(),
                    'duration_minutes' => $trip->duration_minutes,
                    'cost' => (float) ($trip->cost ?? 0),
                    'base_cost' => (float) ($trip->base_cost ?? 0),
                    'discount_amount' => (float) ($trip->discount_amount ?? 0),
                    'penalty_amount' => (float) ($trip->penalty_amount ?? 0),
                    'status' => $trip->status,
                    'zone_exit_detected' => (bool) $trip->zone_exit_detected,
                    'zone_exit_details' => $trip->zone_exit_details,
                    'payment_status' => $trip->payment_status,
                    'paid_amount' => $trip->paid_amount,
                    'remaining_amount' => $trip->remaining_amount,
                    'end_image' => $trip->end_image,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $trips->currentPage(),
                    'last_page' => $trips->lastPage(),
                    'per_page' => $trips->perPage(),
                    'total' => $trips->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الرحلات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a trip by scanning QR code
     */
    public function start(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'qr_code' => 'required|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            // Check if user account is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'حسابك غير مفعل. يرجى الانتظار حتى يتم تفعيله من قبل الإدارة',
                ], 403);
            }

            // Check if user has negative wallet balance (debt)
            $walletBalance = (float) ($user->wallet_balance ?? 0);
            if ($walletBalance < 0) {
                $debtAmount = abs($walletBalance);
                return response()->json([
                    'success' => false,
                    'message' => "لا يمكنك بدء رحلة جديدة. لديك حساب مستحق بقيمة {$debtAmount} جنيه. يرجى تسديد المبلغ المستحق أولاً.",
                    'debt_amount' => $debtAmount,
                ], 400);
            }

            // Check if user has an active trip
            $activeTrip = Trip::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($activeTrip) {
                return response()->json([
                    'success' => false,
                    'message' => 'لديك رحلة نشطة بالفعل',
                    'trip_id' => $activeTrip->id,
                ], 400);
            }

            // Find scooter by QR code
            // Try exact match first
            $scooter = Scooter::where('qr_code', $request->qr_code)
                ->where('is_active', true)
                ->first();

            // If not found, try matching by code (in case QR code is the scooter code)
            if (!$scooter) {
                $scooter = Scooter::where('code', $request->qr_code)
                    ->where('is_active', true)
                    ->first();
            }

            if (!$scooter) {
                \Log::warning('Scooter not found', [
                    'qr_code' => $request->qr_code,
                    'user_id' => $user->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'السكوتر غير موجود أو غير نشط. يرجى التحقق من QR Code.',
                ], 404);
            }

            // Check if scooter is locked
            if ($scooter->is_locked) {
                \Log::warning('Scooter is locked', [
                    'scooter_id' => $scooter->id,
                    'scooter_code' => $scooter->code,
                    'user_id' => $user->id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'السكوتر مقفول حالياً. يرجى المحاولة لاحقاً.',
                    'error_code' => 'SCOOTER_LOCKED',
                ], 400);
            }

            // Check if scooter has an active trip (rented by another user)
            $activeTripForScooter = Trip::where('scooter_id', $scooter->id)
                ->where('status', 'active')
                ->first();
            
            if ($activeTripForScooter) {
                \Log::warning('Scooter already has active trip', [
                    'scooter_id' => $scooter->id,
                    'scooter_code' => $scooter->code,
                    'active_trip_id' => $activeTripForScooter->id,
                    'active_trip_user_id' => $activeTripForScooter->user_id,
                    'current_user_id' => $user->id,
                ]);
                
                // If the active trip belongs to current user, return that trip
                if ($activeTripForScooter->user_id === $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لديك رحلة نشطة بالفعل على هذا السكوتر',
                        'trip_id' => $activeTripForScooter->id,
                    ], 400);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'السكوتر مستأجر حالياً من مستخدم آخر. يرجى البحث عن سكوتر آخر.',
                    'error_code' => 'SCOOTER_RENTED',
                    'scooter_status' => 'rented',
                ], 400);
            }

            // Check if scooter is available
            // Allow if status is 'available' or 'charging' (charging scooters can still be used)
            if (!in_array($scooter->status, ['available', 'charging'])) {
                \Log::warning('Scooter not available', [
                    'scooter_id' => $scooter->id,
                    'scooter_code' => $scooter->code,
                    'status' => $scooter->status,
                    'user_id' => $user->id,
                ]);
                
                $statusMessages = [
                    'rented' => 'السكوتر مستأجر حالياً من مستخدم آخر. يرجى البحث عن سكوتر آخر.',
                    'maintenance' => 'السكوتر قيد الصيانة حالياً. يرجى البحث عن سكوتر آخر.',
                    'inactive' => 'السكوتر غير نشط. يرجى البحث عن سكوتر آخر.',
                    'damaged' => 'السكوتر معطل. يرجى البحث عن سكوتر آخر.',
                    'lost' => 'السكوتر مفقود. يرجى البحث عن سكوتر آخر.',
                ];
                
                $message = $statusMessages[$scooter->status] ?? 'السكوتر غير متاح حالياً. يرجى البحث عن سكوتر آخر.';
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => 'SCOOTER_NOT_AVAILABLE',
                    'scooter_status' => $scooter->status,
                ], 400);
            }

            // Unlock scooter
            $scooter->update([
                'is_locked' => false,
                'status' => 'rented',
            ]);

            // Create trip
            $trip = $this->tripRepository->create([
                'user_id' => $user->id,
                'scooter_id' => $scooter->id,
                'start_time' => Carbon::now(),
                'start_latitude' => $request->latitude,
                'start_longitude' => $request->longitude,
                'status' => 'active',
                'cost' => 0,
                'base_cost' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم بدء الرحلة بنجاح',
                'data' => [
                    'trip_id' => $trip->id,
                    'scooter_code' => $scooter->code,
                    'start_time' => $trip->start_time->toDateTimeString(),
                    'status' => $trip->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في بدء الرحلة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active trip for user
     */
    public function getActiveTrip(Request $request)
    {
        try {
            $user = $request->user();

            $trip = Trip::where('user_id', $user->id)
                ->where('status', 'active')
                ->with(['scooter'])
                ->first();

            if (!$trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد رحلة نشطة',
                ], 404);
            }

            // Safely parse start_time
            $startTime = null;
            if ($trip->start_time instanceof Carbon) {
                $startTime = $trip->start_time;
            } elseif (is_string($trip->start_time)) {
                $startTime = Carbon::parse($trip->start_time);
            } else {
                // Fallback to current time if start_time is invalid
                \Log::warning('Invalid start_time for trip', [
                    'trip_id' => $trip->id,
                    'start_time' => $trip->start_time,
                    'start_time_type' => gettype($trip->start_time),
                ]);
                $startTime = Carbon::now();
            }
            
            // Calculate duration in minutes with decimals (fractional minutes)
            $durationSeconds = $startTime->diffInSeconds(Carbon::now());
            $durationMinutes = $durationSeconds / 60.0; // Convert to minutes with decimals

            // Ensure scooter relationship is loaded and battery is from database
            $trip->load('scooter');
            
            // Get fresh battery data from database
            $batteryPercentage = 0;
            $scooterData = null;
            if ($trip->scooter) {
                // Refresh scooter to get latest battery data
                $trip->scooter->refresh();
                $batteryPercentage = (int) ($trip->scooter->battery_percentage ?? 0);
                
                // Ensure battery is valid (0-100)
                if ($batteryPercentage < 0) {
                    $batteryPercentage = 0;
                } elseif ($batteryPercentage > 100) {
                    $batteryPercentage = 100;
                }
                
                $scooterData = [
                    'id' => $trip->scooter->id,
                    'code' => $trip->scooter->code,
                    'battery_percentage' => $batteryPercentage, // From database
                ];
                
                \Log::info('🔋 Active trip battery data', [
                    'trip_id' => $trip->id,
                    'scooter_id' => $trip->scooter->id,
                    'scooter_code' => $trip->scooter->code,
                    'battery_percentage' => $batteryPercentage,
                    'raw_battery' => $trip->scooter->battery_percentage,
                ]);
            } else {
                \Log::warning('⚠️ No scooter found for active trip', [
                    'trip_id' => $trip->id,
                    'scooter_id' => $trip->scooter_id,
                ]);
            }
            
            // Always include scooter data in response (even if null) for consistency
            if ($scooterData === null) {
                $scooterData = [
                    'id' => null,
                    'code' => null,
                    'battery_percentage' => 0,
                ];
            }

            // Calculate current cost based on duration and geo zone pricing
            $currentCost = 0.0;
            if ($trip->start_latitude && $trip->start_longitude) {
                try {
                    $geoZone = \App\Models\GeoZone::where('type', 'allowed')
                        ->where('is_active', true)
                        ->get()
                        ->first(function ($zone) use ($trip) {
                            try {
                                return $this->isPointInPolygon(
                                    (float) $trip->start_latitude,
                                    (float) $trip->start_longitude,
                                    $zone->polygon ?? []
                                );
                            } catch (\Exception $e) {
                                \Log::warning('Error checking point in polygon', [
                                    'zone_id' => $zone->id,
                                    'error' => $e->getMessage(),
                                ]);
                                return false;
                            }
                        });
                } catch (\Exception $e) {
                    \Log::error('Error fetching geo zones', [
                        'trip_id' => $trip->id,
                        'error' => $e->getMessage(),
                    ]);
                    $geoZone = null;
                }
                
                if ($geoZone && $geoZone->price_per_minute) {
                    $tripStartFee = (float) ($geoZone->trip_start_fee ?? 0);
                    $pricePerMinute = (float) ($geoZone->price_per_minute ?? 0);
                    $currentCost = $tripStartFee + ($durationMinutes * $pricePerMinute);
                    
                    \Log::info('💰 Cost calculation (from geo zone)', [
                        'trip_id' => $trip->id,
                        'geo_zone_id' => $geoZone->id,
                        'duration_minutes' => $durationMinutes,
                        'trip_start_fee' => $tripStartFee,
                        'price_per_minute' => $pricePerMinute,
                        'calculated_cost' => $currentCost,
                    ]);
                } else {
                    // Fallback to default pricing if no geo zone found
                    $defaultTripStartFee = 5.0; // Default base cost
                    $defaultPricePerMinute = 0.5; // Default cost per minute
                    $currentCost = $defaultTripStartFee + ($durationMinutes * $defaultPricePerMinute);
                    
                    \Log::warning('⚠️ No geo zone or pricing found, using default pricing', [
                        'trip_id' => $trip->id,
                        'start_latitude' => $trip->start_latitude,
                        'start_longitude' => $trip->start_longitude,
                        'duration_minutes' => $durationMinutes,
                        'default_trip_start_fee' => $defaultTripStartFee,
                        'default_price_per_minute' => $defaultPricePerMinute,
                        'calculated_cost' => $currentCost,
                    ]);
                }
            } else {
                // If no coordinates, use default pricing based on duration only
                $defaultPricePerMinute = 0.5; // Default cost per minute
                $currentCost = $durationMinutes * $defaultPricePerMinute;
                
                \Log::warning('⚠️ Trip missing start coordinates, using default per-minute pricing', [
                    'trip_id' => $trip->id,
                    'start_latitude' => $trip->start_latitude,
                    'start_longitude' => $trip->start_longitude,
                    'duration_minutes' => $durationMinutes,
                    'default_price_per_minute' => $defaultPricePerMinute,
                    'calculated_cost' => $currentCost,
                ]);
            }
            
            // Ensure cost is not negative and has minimum value
            $currentCost = max(0, $currentCost);
            
            // Log final cost
            \Log::info('💰 Final cost calculation', [
                'trip_id' => $trip->id,
                'duration_minutes' => $durationMinutes,
                'final_cost' => $currentCost,
            ]);

            $responseData = [
                'success' => true,
                'data' => [
                    'id' => $trip->id,
                    'scooter_code' => $trip->scooter?->code,
                    'start_time' => $startTime->toDateTimeString(),
                    'duration_minutes' => round($durationMinutes, 2), // Round to 2 decimals
                    'status' => $trip->status,
                    'current_cost' => round($currentCost, 2), // Current cost based on duration
                    'scooter' => $scooterData, // Always include scooter data
                ],
            ];
            
            \Log::info('📤 Sending active trip response', [
                'trip_id' => $trip->id,
                'battery_percentage' => $scooterData['battery_percentage'] ?? 0,
                'current_cost' => $responseData['data']['current_cost'],
                'duration_minutes' => $responseData['data']['duration_minutes'],
            ]);
            
            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            \Log::error('❌ Error in getActiveTrip', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الرحلة النشطة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a point is inside a polygon
     * Uses ray casting algorithm
     */
    private function isPointInPolygon(float $latitude, float $longitude, array $polygon): bool
    {
        try {
            // If polygon is empty or invalid, return false
            if (empty($polygon) || !is_array($polygon)) {
                return false;
            }

            // Handle different polygon formats
            $points = [];
            if (isset($polygon[0]) && is_array($polygon[0])) {
                // Format: [[lat, lng], [lat, lng], ...]
                $points = $polygon;
            } elseif (isset($polygon['coordinates']) && is_array($polygon['coordinates'])) {
                // Format: {'coordinates': [[lat, lng], [lat, lng], ...]}
                $points = $polygon['coordinates'];
            } else {
                // Try to parse as flat array [lat1, lng1, lat2, lng2, ...]
                if (count($polygon) >= 4 && count($polygon) % 2 == 0) {
                    for ($i = 0; $i < count($polygon); $i += 2) {
                        if (isset($polygon[$i]) && isset($polygon[$i + 1])) {
                            $points[] = [$polygon[$i], $polygon[$i + 1]];
                        }
                    }
                }
            }

            // Need at least 3 points to form a polygon
            if (count($points) < 3) {
                return false;
            }

            $n = count($points);
            $inside = false;

            // Ray casting algorithm
            for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
                $xi = is_array($points[$i]) ? (float) $points[$i][0] : (float) $points[$i];
                $yi = is_array($points[$i]) ? (float) $points[$i][1] : (float) ($points[$i + 1] ?? 0);
                $xj = is_array($points[$j]) ? (float) $points[$j][0] : (float) $points[$j];
                $yj = is_array($points[$j]) ? (float) $points[$j][1] : (float) ($points[$j + 1] ?? 0);

                // Check if point is on the edge
                if (($yi == $longitude && $yj == $longitude && 
                     (($xi <= $latitude && $latitude <= $xj) || ($xj <= $latitude && $latitude <= $xi)))) {
                    return true;
                }

                // Check intersection
                $intersect = (($yi > $longitude) != ($yj > $longitude)) &&
                             ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);
                
                if ($intersect) {
                    $inside = !$inside;
                }
            }

            return $inside;
        } catch (\Exception $e) {
            \Log::error('Error in isPointInPolygon', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'polygon' => $polygon,
                'error' => $e->getMessage(),
            ]);
            return false; // Default to false on error
        }
    }

    /**
     * Complete a trip
     */
    public function complete(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'end_latitude' => 'nullable|numeric|between:-90,90',
                'end_longitude' => 'nullable|numeric|between:-180,180',
                'end_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $trip = Trip::where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->with(['scooter'])
                ->first();

            if (!$trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'الرحلة غير موجودة أو تم إغلاقها بالفعل',
                ], 404);
            }

            $endTime = Carbon::now();
            $startTime = Carbon::parse($trip->start_time);
            // Calculate duration in minutes with decimals (fractional minutes)
            $durationSeconds = $startTime->diffInSeconds($endTime);
            $durationMinutes = $durationSeconds / 60.0; // Convert to minutes with decimals

            // Calculate cost (simplified - you can add pricing logic here)
            $baseCost = 5.0; // Base cost
            $costPerMinute = 0.5; // Cost per minute
            $cost = $baseCost + ($durationMinutes * $costPerMinute);

            // Handle image upload
            $endImage = null;
            if ($request->hasFile('end_image')) {
                try {
                    $image = $request->file('end_image');
                    $imageName = 'trip_end_' . $trip->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/trip_end_images', $imageName);
                    $endImage = 'trip_end_images/' . $imageName;
                } catch (\Exception $imageError) {
                    \Log::error("Failed to upload trip end image: " . $imageError->getMessage());
                    // Continue without image if upload fails
                }
            }

            // Complete trip using repository (for wallet deduction, loyalty points, etc.)
            $completedTrip = $this->tripRepository->completeTrip($trip, [
                'end_latitude' => $request->end_latitude,
                'end_longitude' => $request->end_longitude,
                'cost' => $cost,
            ]);

            // Update end_image if it was uploaded
            if ($endImage) {
                $completedTrip->update(['end_image' => $endImage]);
            }

            // Lock scooter and set status to available
            if ($completedTrip->scooter) {
                $completedTrip->scooter->update([
                    'is_locked' => true,
                    'status' => 'available',
                ]);
            }

            // Refresh trip to get latest data
            $completedTrip->refresh();

            return response()->json([
                'success' => true,
                'message' => 'تم إغلاق الرحلة بنجاح',
                'data' => [
                    'trip_id' => $completedTrip->id,
                    'duration_minutes' => $completedTrip->duration_minutes,
                    'cost' => (float) $completedTrip->cost,
                    'end_time' => $completedTrip->end_time?->toDateTimeString(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إغلاق الرحلة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
