<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\LoyaltyPointsTransaction;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;

class LoyaltyRepository
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly WalletRepository $walletRepository,
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

    /**
     * Get points redeem settings
     */
    public function getPointsRedeemSettings(): array
    {
        $settings = DB::table('loyalty_settings')
            ->whereIn('key', [
                'points_redeem_enabled',
                'points_to_egp_rate',
                'min_points_to_redeem',
                'max_redeem_percentage',
            ])
            ->pluck('value', 'key')
            ->toArray();

        return [
            'enabled' => (bool) ($settings['points_redeem_enabled'] ?? true),
            'points_to_egp_rate' => (int) ($settings['points_to_egp_rate'] ?? 100),
            'min_points_to_redeem' => (int) ($settings['min_points_to_redeem'] ?? 100),
            'max_redeem_percentage' => (int) ($settings['max_redeem_percentage'] ?? 50),
        ];
    }

    /**
     * Set points redeem settings
     */
    public function setPointsRedeemSettings(array $settings): void
    {
        $data = [
            'points_redeem_enabled' => $settings['enabled'] ?? true,
            'points_to_egp_rate' => $settings['points_to_egp_rate'] ?? 100,
            'min_points_to_redeem' => $settings['min_points_to_redeem'] ?? 100,
            'max_redeem_percentage' => $settings['max_redeem_percentage'] ?? 50,
        ];

        foreach ($data as $key => $value) {
            $exists = DB::table('loyalty_settings')->where('key', $key)->exists();
            
            if ($exists) {
                DB::table('loyalty_settings')
                    ->where('key', $key)
                    ->update(['value' => $value, 'updated_at' => now()]);
            } else {
                DB::table('loyalty_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Calculate discount amount from points
     */
    public function calculateDiscountFromPoints(int $points, float $tripCost): float
    {
        $settings = $this->getPointsRedeemSettings();

        if (!$settings['enabled']) {
            return 0.0;
        }

        // Convert points to EGP
        $discountAmount = ($points / $settings['points_to_egp_rate']);

        // Calculate max discount based on percentage
        $maxDiscount = ($tripCost * $settings['max_redeem_percentage']) / 100;

        // Return the minimum between calculated discount and max discount
        return min($discountAmount, $maxDiscount);
    }

    /**
     * Calculate required points for a discount amount
     */
    public function calculatePointsRequiredForDiscount(float $discountAmount): int
    {
        $settings = $this->getPointsRedeemSettings();

        if (!$settings['enabled']) {
            return 0;
        }

        return (int) ceil($discountAmount * $settings['points_to_egp_rate']);
    }

    /**
     * Redeem loyalty points to wallet balance
     * 
     * @param User $user
     * @param int $points Number of points to redeem
     * @return array Returns ['success' => bool, 'wallet_amount' => float, 'transaction' => LoyaltyPointsTransaction, 'wallet_transaction' => WalletTransaction]
     */
    public function redeemPoints(User $user, int $points): array
    {
        $settings = $this->getPointsRedeemSettings();

        // Check if redeem is enabled
        if (!$settings['enabled']) {
            throw new \Exception('استبدال النقاط معطل حالياً');
        }

        // Check minimum points
        $minPoints = $settings['min_points_to_redeem'] ?? 100;
        if ($points < $minPoints) {
            throw new \Exception("الحد الأدنى لاستبدال النقاط هو {$minPoints} نقطة");
        }

        // Check if user has enough points
        $userPoints = (int) ($user->loyalty_points ?? 0);
        if ($userPoints < $points) {
            throw new \Exception("ليس لديك نقاط كافية. النقاط المتاحة: {$userPoints} نقطة");
        }

        return DB::transaction(function () use ($user, $points, $settings) {
            // Calculate wallet amount from points
            $pointsToEgpRate = $settings['points_to_egp_rate'] ?? 100;
            $walletAmount = $points / $pointsToEgpRate;

            // Deduct points from user
            $pointsTransaction = $this->deductPoints(
                $user,
                $points,
                'redeemed',
                "استبدال {$points} نقطة برصيد {$walletAmount} جنيه في المحفظة"
            );

            // Add balance to wallet
            $walletTransaction = $this->walletRepository->topUp(
                $user,
                $walletAmount,
                'loyalty_points_redeem',
                "LP-{$pointsTransaction->id}",
                "استبدال {$points} نقطة ولاء برصيد {$walletAmount} جنيه",
                [
                    'loyalty_points_transaction_id' => $pointsTransaction->id,
                    'points_redeemed' => $points,
                    'points_to_egp_rate' => $pointsToEgpRate,
                ]
            );

            return [
                'success' => true,
                'wallet_amount' => $walletAmount,
                'points_transaction' => $pointsTransaction,
                'wallet_transaction' => $walletTransaction,
            ];
        });
    }

    /**
     * Verify and recalculate loyalty points balance from transactions
     * This ensures data integrity by recalculating balance from all transactions
     */
    public function verifyAndRecalculateBalance(User $user): array
    {
        $transactions = LoyaltyPointsTransaction::where('user_id', $user->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $calculatedBalance = 0;
        $issues = [];

        foreach ($transactions as $transaction) {
            $calculatedBalance += $transaction->points;
            
            // Verify balance_after matches calculated balance
            if ($transaction->balance_after != $calculatedBalance) {
                $issues[] = [
                    'transaction_id' => $transaction->id,
                    'expected_balance' => $calculatedBalance,
                    'stored_balance' => $transaction->balance_after,
                    'difference' => $calculatedBalance - $transaction->balance_after,
                ];
            }
        }

        // Check if user's current balance matches calculated balance
        if ($user->loyalty_points != $calculatedBalance) {
            $issues[] = [
                'type' => 'user_balance_mismatch',
                'user_id' => $user->id,
                'expected_balance' => $calculatedBalance,
                'stored_balance' => $user->loyalty_points,
                'difference' => $calculatedBalance - $user->loyalty_points,
            ];
        }

        return [
            'calculated_balance' => $calculatedBalance,
            'current_balance' => $user->loyalty_points,
            'issues' => $issues,
            'is_valid' => empty($issues),
        ];
    }

    /**
     * Fix loyalty points balance by recalculating from transactions
     */
    public function fixBalanceFromTransactions(User $user): User
    {
        $transactions = LoyaltyPointsTransaction::where('user_id', $user->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $calculatedBalance = 0;

        foreach ($transactions as $transaction) {
            $calculatedBalance += $transaction->points;
            
            // Update balance_after if incorrect
            if ($transaction->balance_after != $calculatedBalance) {
                $transaction->update(['balance_after' => $calculatedBalance]);
            }
        }

        // Update user's balance
        if ($user->loyalty_points != $calculatedBalance) {
            $user->update(['loyalty_points' => $calculatedBalance]);
            // Update loyalty level
            $this->userRepository->updateLoyaltyLevel($user);
        }

        return $user->fresh();
    }
}
