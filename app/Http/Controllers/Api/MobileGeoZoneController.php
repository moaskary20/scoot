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
     * Get all active geo zones for mobile map (allowed, forbidden, parking, ...).
     */
    public function index()
    {
        try {
            // Get all active zones of all types
            $zones = $this->repository->allActive();

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


