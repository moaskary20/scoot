<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $id): User
    {
        return User::findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function activate(User $user): User
    {
        $user->update(['is_active' => true]);

        return $user;
    }

    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);

        return $user;
    }

    public function addWalletBalance(User $user, float $amount): User
    {
        $user->increment('wallet_balance', $amount);

        return $user->fresh();
    }

    public function deductWalletBalance(User $user, float $amount): User
    {
        $user->decrement('wallet_balance', $amount);

        return $user->fresh();
    }

    public function addLoyaltyPoints(User $user, int $points): User
    {
        $user->increment('loyalty_points', $points);
        
        // تحديث مستوى الولاء تلقائياً
        $this->updateLoyaltyLevel($user);

        return $user->fresh();
    }

    public function deductLoyaltyPoints(User $user, int $points): User
    {
        $user->decrement('loyalty_points', max(0, $points));
        
        // تحديث مستوى الولاء تلقائياً
        $this->updateLoyaltyLevel($user);

        return $user->fresh();
    }

    protected function updateLoyaltyLevel(User $user): void
    {
        $points = $user->loyalty_points;
        
        // الحصول على العتبات من الإعدادات
        $thresholds = \DB::table('loyalty_settings')
            ->whereIn('key', ['bronze_threshold', 'silver_threshold', 'gold_threshold'])
            ->pluck('value', 'key')
            ->toArray();
        
        $goldThreshold = (int) ($thresholds['gold_threshold'] ?? 1000);
        $silverThreshold = (int) ($thresholds['silver_threshold'] ?? 500);
        $bronzeThreshold = (int) ($thresholds['bronze_threshold'] ?? 0);
        
        if ($points >= $goldThreshold) {
            $user->update(['loyalty_level' => 'gold']);
        } elseif ($points >= $silverThreshold) {
            $user->update(['loyalty_level' => 'silver']);
        } else {
            $user->update(['loyalty_level' => 'bronze']);
        }
    }
}

