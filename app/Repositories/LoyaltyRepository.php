<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\LoyaltyPointsTransaction;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class LoyaltyRepository
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        //
    }

    public function addPoints(User $user, int $points, string $type = 'earned', ?int $tripId = null, ?string $description = null): LoyaltyPointsTransaction
    {
        return DB::transaction(function () use ($user, $points, $type, $tripId, $description) {
            // إضافة النقاط
            $this->userRepository->addLoyaltyPoints($user, $points);

            // إنشاء معاملة
            $transaction = LoyaltyPointsTransaction::create([
                'user_id' => $user->id,
                'trip_id' => $tripId,
                'type' => $type,
                'points' => $points,
                'balance_after' => $user->fresh()->loyalty_points,
                'description' => $description,
            ]);

            return $transaction;
        });
    }

    public function deductPoints(User $user, int $points, string $type = 'redeemed', ?string $description = null): LoyaltyPointsTransaction
    {
        return DB::transaction(function () use ($user, $points, $type, $description) {
            // خصم النقاط
            $this->userRepository->deductLoyaltyPoints($user, $points);

            // إنشاء معاملة
            $transaction = LoyaltyPointsTransaction::create([
                'user_id' => $user->id,
                'trip_id' => null,
                'type' => $type,
                'points' => -$points,
                'balance_after' => $user->fresh()->loyalty_points,
                'description' => $description,
            ]);

            return $transaction;
        });
    }

    public function getUserTransactions(int $userId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return LoyaltyPointsTransaction::where('user_id', $userId)
            ->with(['trip'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getAllTransactions(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return LoyaltyPointsTransaction::with(['user', 'trip'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getPointsPerMinute(): int
    {
        $setting = DB::table('loyalty_settings')
            ->where('key', 'points_per_minute')
            ->first();

        return (int) ($setting->value ?? 1);
    }

    public function setPointsPerMinute(int $points): void
    {
        DB::table('loyalty_settings')
            ->where('key', 'points_per_minute')
            ->update(['value' => $points, 'updated_at' => now()]);
    }

    public function getLevelThresholds(): array
    {
        $settings = DB::table('loyalty_settings')
            ->whereIn('key', ['bronze_threshold', 'silver_threshold', 'gold_threshold'])
            ->pluck('value', 'key')
            ->toArray();

        return [
            'bronze' => (int) ($settings['bronze_threshold'] ?? 0),
            'silver' => (int) ($settings['silver_threshold'] ?? 500),
            'gold' => (int) ($settings['gold_threshold'] ?? 1000),
        ];
    }

    public function setLevelThresholds(array $thresholds): void
    {
        foreach ($thresholds as $level => $value) {
            DB::table('loyalty_settings')
                ->where('key', $level . '_threshold')
                ->update(['value' => $value, 'updated_at' => now()]);
        }
    }

    public function calculatePointsForTrip(int $minutes): int
    {
        $pointsPerMinute = $this->getPointsPerMinute();
        return $minutes * $pointsPerMinute;
    }
}

