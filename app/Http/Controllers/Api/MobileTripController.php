<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Scooter;
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

            $startTime = Carbon::parse($trip->start_time);
            $durationMinutes = $startTime->diffInMinutes(Carbon::now());

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $trip->id,
                    'scooter_code' => $trip->scooter?->code,
                    'start_time' => $trip->start_time->toDateTimeString(),
                    'duration_minutes' => $durationMinutes,
                    'status' => $trip->status,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الرحلة النشطة',
                'error' => $e->getMessage(),
            ], 500);
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
            $durationMinutes = $startTime->diffInMinutes($endTime);

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
