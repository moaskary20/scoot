<?php

namespace App\Repositories;

use App\Models\Scooter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ScooterRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Scooter::query()
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $id): Scooter
    {
        return Scooter::findOrFail($id);
    }

    public function create(array $data): Scooter
    {
        return Scooter::create($data);
    }

    public function update(Scooter $scooter, array $data): Scooter
    {
        $scooter->update($data);

        return $scooter;
    }

    public function delete(Scooter $scooter): void
    {
        $scooter->delete();
    }

    public function lock(Scooter $scooter): Scooter
    {
        $scooter->update(['is_locked' => true]);

        return $scooter;
    }

    public function unlock(Scooter $scooter): Scooter
    {
        $scooter->update(['is_locked' => false]);

        return $scooter;
    }

    public function getAllWithGPS(): \Illuminate\Database\Eloquent\Collection
    {
        return Scooter::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_active', true)
            ->get();
    }
}



