<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Repositories\WalletRepository;
use App\Repositories\LoyaltyRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class TripRepository
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly LoyaltyRepository $loyaltyRepository,
    ) {
        //
    }
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Trip::query()
            ->with(['user', 'scooter'])
            ->orderByDesc('start_time')
            ->paginate($perPage);
    }

    public function find(int $id): Trip
    {
        return Trip::with(['user', 'scooter', 'coupon', 'penalty'])->findOrFail($id);
    }

    public function create(array $data): Trip
    {
        return Trip::create($data);
    }

    public function update(Trip $trip, array $data): Trip
    {
        $wasCompleted = $trip->status === 'completed';
        $trip->update($data);
        $trip->refresh();

        // If trip status changed to completed and wasn't completed before
        if (!$wasCompleted && $trip->status === 'completed' && $trip->duration_minutes > 0) {
            // Check if loyalty points were already added for this trip
            $existingTransaction = \App\Models\LoyaltyPointsTransaction::where('trip_id', $trip->id)
                ->where('type', 'earned')
                ->first();

            // Only add points if no transaction exists for this trip
            if (!$existingTransaction) {
                try {
                    $pointsEarned = $this->loyaltyRepository->calculatePointsForTrip($trip->duration_minutes);
                    if ($pointsEarned > 0) {
                        $this->loyaltyRepository->addPoints(
                            $trip->user,
                            $pointsEarned,
                            'earned',
                            $trip->id,
                            "Points earned for completed trip #{$trip->id} - Duration: {$trip->duration_minutes} minutes"
                        );
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the trip update
                    \Log::error("Failed to add loyalty points for trip {$trip->id}: " . $e->getMessage());
                }
            }
        }

        return $trip;
    }

    public function delete(Trip $trip): void
    {
        $trip->delete();
    }

    public function getTodayTrips(): int
    {
        return Trip::whereDate('start_time', Carbon::today())->count();
    }

    public function getActiveTrips(): \Illuminate\Database\Eloquent\Collection
    {
        return Trip::where('status', 'active')
            ->with(['user', 'scooter'])
            ->get();
    }

    public function getUserTrips(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Trip::where('user_id', $userId)
            ->with(['scooter'])
            ->orderByDesc('start_time')
            ->paginate($perPage);
    }

    public function getScooterTrips(int $scooterId, int $perPage = 15): LengthAwarePaginator
    {
        return Trip::where('scooter_id', $scooterId)
            ->with(['user'])
            ->orderByDesc('start_time')
            ->paginate($perPage);
    }

    public function completeTrip(Trip $trip, array $endData): Trip
    {
        $endTime = Carbon::now();
        $startTime = Carbon::parse($trip->start_time);
        $durationMinutes = $startTime->diffInMinutes($endTime);

        // Calculate cost - use provided cost or calculate from base_cost, discount, and penalty
        $cost = $endData['cost'] ?? $trip->cost ?? ($trip->base_cost - $trip->discount_amount + $trip->penalty_amount);
        
        // Ensure cost is not negative
        $cost = max(0, (float) $cost);

        // Update trip status to completed
        $trip->update([
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'end_latitude' => $endData['end_latitude'] ?? $endData['latitude'] ?? null,
            'end_longitude' => $endData['end_longitude'] ?? $endData['longitude'] ?? null,
            'cost' => $cost,
            'status' => 'completed',
        ]);

        // Deduct from wallet if cost > 0 (allow negative balance for trips)
        if ($cost > 0) {
            try {
                $this->walletRepository->deduct(
                    $trip->user,
                    $cost,
                    'trip_payment',
                    $trip,
                    "Payment for trip #{$trip->id} - Duration: {$durationMinutes} minutes",
                    null,
                    true // Allow negative balance (debt)
                );
            } catch (\Exception $e) {
                // Log error but don't fail the trip completion
                \Log::error("Failed to deduct wallet for trip {$trip->id}: " . $e->getMessage());
            }
        }

        // Add loyalty points based on trip duration (only if not already added)
        if ($durationMinutes > 0) {
            try {
                // Check if loyalty points were already added for this trip
                $existingTransaction = \App\Models\LoyaltyPointsTransaction::where('trip_id', $trip->id)
                    ->where('type', 'earned')
                    ->first();

                // Only add points if no transaction exists for this trip
                if (!$existingTransaction) {
                    $pointsEarned = $this->loyaltyRepository->calculatePointsForTrip($durationMinutes);
                    if ($pointsEarned > 0) {
                        $this->loyaltyRepository->addPoints(
                            $trip->user,
                            $pointsEarned,
                            'earned',
                            $trip->id,
                            "Points earned for completed trip #{$trip->id} - Duration: {$durationMinutes} minutes"
                        );
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail the trip completion
                \Log::error("Failed to add loyalty points for trip {$trip->id}: " . $e->getMessage());
            }
        }

        // Track referral completion (mark referral as completed when user completes first trip)
        try {
            \App\Http\Controllers\Api\MobileReferralController::markReferralCompleted(
                $trip->user_id,
                $trip->id
            );
        } catch (\Exception $e) {
            // Log error but don't fail the trip completion
            \Log::error("Failed to mark referral as completed for trip {$trip->id}: " . $e->getMessage());
        }

        return $trip->fresh();
    }

    public function markZoneExit(Trip $trip, array $exitData): Trip
    {
        $trip->update([
            'zone_exit_detected' => true,
            'zone_exit_details' => json_encode($exitData),
        ]);

        return $trip->fresh();
    }
}

