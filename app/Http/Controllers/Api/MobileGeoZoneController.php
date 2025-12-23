<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\GeoZoneRepository;

class MobileGeoZoneController extends Controller
{
    public function __construct(
        private readonly GeoZoneRepository $repository,
    ) {
        //
    }

    /**
     * Get active allowed geo zones for mobile map.
     */
    public function index()
    {
        try {
            $zones = $this->repository->allActive()
                ->where('type', 'allowed')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $zones->map(function ($zone) {
                    return [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'type' => $zone->type,
                        'color' => $zone->color,       // e.g. "#00C853"
                        'polygon' => $zone->polygon ?? [],
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب المناطق الجغرافية',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


