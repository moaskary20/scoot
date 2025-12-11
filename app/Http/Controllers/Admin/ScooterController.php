<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scooter;
use App\Repositories\ScooterLogRepository;
use App\Repositories\ScooterRepository;
use App\Services\WebSocketService;
use Illuminate\Http\Request;

class ScooterController extends Controller
{
    public function __construct(
        private readonly ScooterRepository $repository,
        private readonly ScooterLogRepository $logRepository,
        private readonly WebSocketService $webSocketService,
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $scooters = $this->repository->paginate(20);

        return view('admin.scooters.index', compact('scooters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.scooters.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:scooters,code'],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:available,rented,charging,maintenance'],
            'battery_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_locked' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'device_imei' => ['nullable', 'string', 'max:100'],
            'firmware_version' => ['nullable', 'string', 'max:100'],
        ]);

        $data['is_locked'] = $request->boolean('is_locked', true);
        $data['is_active'] = $request->boolean('is_active', true);

        $this->repository->create($data);

        return redirect()
            ->route('admin.scooters.index')
            ->with('status', __('Scooter created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Scooter $scooter)
    {
        return view('admin.scooters.show', compact('scooter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scooter $scooter)
    {
        return view('admin.scooters.edit', compact('scooter'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Scooter $scooter)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:scooters,code,' . $scooter->id],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:available,rented,charging,maintenance'],
            'battery_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_locked' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'device_imei' => ['nullable', 'string', 'max:100'],
            'firmware_version' => ['nullable', 'string', 'max:100'],
        ]);

        $data['is_locked'] = $request->boolean('is_locked', $scooter->is_locked);
        $data['is_active'] = $request->boolean('is_active', $scooter->is_active);

        $this->repository->update($scooter, $data);

        return redirect()
            ->route('admin.scooters.index')
            ->with('status', __('Scooter updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scooter $scooter)
    {
        $this->repository->delete($scooter);

        return redirect()
            ->route('admin.scooters.index')
            ->with('status', __('Scooter deleted successfully.'));
    }

    /**
     * Get all scooters with GPS data for map display
     */
    public function getMapData()
    {
        $scooters = $this->repository->getAllWithGPS();

        return response()->json([
            'scooters' => $scooters->map(function ($scooter) {
                return [
                    'id' => $scooter->id,
                    'code' => $scooter->code,
                    'status' => $scooter->status,
                    'battery_percentage' => $scooter->battery_percentage,
                    'is_locked' => $scooter->is_locked,
                    'latitude' => (float) $scooter->latitude,
                    'longitude' => (float) $scooter->longitude,
                    'last_seen_at' => $scooter->last_seen_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Lock scooter remotely
     */
    public function lock(Request $request, Scooter $scooter)
    {
        $this->repository->lock($scooter);
        $this->logRepository->logManualLock($scooter, auth()->id(), true);

        // Send command via WebSocket
        if ($scooter->device_imei) {
            $this->webSocketService->sendCommandToScooter($scooter, ['lock' => true, 'unlock' => false]);
        }

        return redirect()
            ->route('admin.scooters.show', $scooter)
            ->with('status', __('Scooter locked successfully.'));
    }

    /**
     * Unlock scooter remotely
     */
    public function unlock(Request $request, Scooter $scooter)
    {
        $this->repository->unlock($scooter);
        $this->logRepository->logManualLock($scooter, auth()->id(), false);

        // Send command via WebSocket
        if ($scooter->device_imei) {
            $this->webSocketService->sendCommandToScooter($scooter, ['lock' => false, 'unlock' => true]);
        }

        return redirect()
            ->route('admin.scooters.show', $scooter)
            ->with('status', __('Scooter unlocked successfully.'));
    }

    /**
     * Get current lock status and battery (for AJAX updates)
     */
    public function getLockStatus(Scooter $scooter)
    {
        $scooter->refresh();
        
        return response()->json([
            'success' => true,
            'is_locked' => $scooter->is_locked,
            'battery_percentage' => $scooter->battery_percentage,
            'status' => $scooter->status,
            'last_seen_at' => $scooter->last_seen_at?->toIso8601String(),
        ]);
    }
}
