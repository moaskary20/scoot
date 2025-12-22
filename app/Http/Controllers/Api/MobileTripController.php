<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Repositories\TripRepository;
use Illuminate\Http\Request;

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
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
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
}
