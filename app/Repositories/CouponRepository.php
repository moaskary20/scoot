<?php

namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Coupon::query()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): Coupon
    {
        return Coupon::findOrFail($id);
    }

    public function findByCode(string $code): ?Coupon
    {
        return Coupon::where('code', $code)->first();
    }

    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);

        return $coupon;
    }

    public function delete(Coupon $coupon): void
    {
        $coupon->delete();
    }

    public function incrementUsage(Coupon $coupon): Coupon
    {
        $coupon->increment('usage_count');

        return $coupon->fresh();
    }

    public function getActiveCoupons(): \Illuminate\Database\Eloquent\Collection
    {
        return Coupon::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();
    }
}

