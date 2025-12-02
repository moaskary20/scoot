<?php

namespace App\Repositories;

use App\Models\GeoZone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GeoZoneRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return GeoZone::query()
            ->orderBy('type')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function allActive(): \Illuminate\Database\Eloquent\Collection
    {
        return GeoZone::where('is_active', true)->get();
    }

    public function find(int $id): GeoZone
    {
        return GeoZone::findOrFail($id);
    }

    public function create(array $data): GeoZone
    {
        return GeoZone::create($data);
    }

    public function update(GeoZone $zone, array $data): GeoZone
    {
        $zone->update($data);

        return $zone;
    }

    public function delete(GeoZone $zone): void
    {
        $zone->delete();
    }
}


