<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Repositories\WalletRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class TripRepository
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
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
        $trip->update($data);

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

        // Calculate cost if not already set
        $cost = $trip->cost ?? ($trip->base_cost - $trip->discount_amount + $trip->penalty_amount);

        $trip->update([
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'end_latitude' => $endData['latitude'] ?? null,
            'end_longitude' => $endData['longitude'] ?? null,
            'cost' => $cost,
            'status' => 'completed',
        ]);

        // Deduct from wallet if cost > 0
        if ($cost > 0) {
            try {
                $this->walletRepository->deduct(
                    $trip->user,
                    $cost,
                    'trip_payment',
                    $trip,
                    "Payment for trip #{$trip->id} - Duration: {$durationMinutes} minutes"
                );
            } catch (\Exception $e) {
                // Log error but don't fail the trip completion
                \Log::error("Failed to deduct wallet for trip {$trip->id}: " . $e->getMessage());
            }
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

