<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class SubscriptionRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Subscription::query()
            ->with(['user', 'coupon'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): Subscription
    {
        return Subscription::with(['user', 'coupon'])->findOrFail($id);
    }

    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription;
    }

    public function delete(Subscription $subscription): void
    {
        $subscription->delete();
    }

    public function getUserSubscriptions(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Subscription::where('user_id', $userId)
            ->with(['coupon'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getActiveSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('status', 'active')
            ->where('expires_at', '>', Carbon::now())
            ->with(['user'])
            ->get();
    }

    public function getExpiringSoon(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('status', 'active')
            ->whereBetween('expires_at', [
                Carbon::now(),
                Carbon::now()->addDays($days)
            ])
            ->with(['user'])
            ->get();
    }

    public function renew(Subscription $subscription): Subscription
    {
        $nextRenewalDate = $subscription->getNextRenewalDate();

        $subscription->update([
            'starts_at' => Carbon::now(),
            'expires_at' => $nextRenewalDate,
            'renewed_at' => Carbon::now(),
            'minutes_used' => 0,
            'trips_count' => 0,
            'status' => 'active',
        ]);

        return $subscription->fresh();
    }

    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);

        return $subscription->fresh();
    }

    public function suspend(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'suspended',
        ]);

        return $subscription->fresh();
    }

    public function activate(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'active',
        ]);

        return $subscription->fresh();
    }

    public function addUsage(Subscription $subscription, int $minutes): Subscription
    {
        $subscription->increment('minutes_used', $minutes);
        $subscription->increment('trips_count');

        return $subscription->fresh();
    }
}

