<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scooter;
use App\Repositories\ScooterRepository;
use App\Services\AntiTheftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScooterApiController extends Controller
{
    public function __construct(
        private readonly ScooterRepository $repository,
        private readonly AntiTheftService $antiTheftService,
    ) {
        //
    }

    /**
     * Authenticate scooter by IMEI and get commands
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imei' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $scooter = Scooter::where('device_imei', $request->imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not found or inactive',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'scooter_id' => $scooter->id,
            'code' => $scooter->code,
            'commands' => [
                'lock' => $scooter->is_locked ? false : true, // If not locked, command to lock
                'unlock' => $scooter->is_locked ? true : false, // If locked, command to unlock
            ],
            'status' => $scooter->status,
        ]);
    }

    /**
     * Update GPS location and battery
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imei' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'battery_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'lock_status' => ['nullable', 'boolean'], // Current lock status from device
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $scooter = Scooter::where('device_imei', $request->imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not found',
            ], 404);
        }

        // Update GPS and check for unauthorized movement
        $this->antiTheftService->updateScooterGPS(
            $scooter,
            $request->latitude,
            $request->longitude,
            $request->battery_percentage
        );

        // Update lock status if provided
        if ($request->has('lock_status')) {
            $scooter->update(['is_locked' => $request->lock_status]);
        }

        // Check zone exit
        $activeTrip = $scooter->trips()->where('status', 'active')->first();
        $this->antiTheftService->checkZoneExit($scooter, $activeTrip);

        // Get pending commands
        $commands = [
            'lock' => !$scooter->is_locked && $scooter->status !== 'rented',
            'unlock' => $scooter->is_locked && $scooter->status === 'rented',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Location updated',
            'commands' => $commands,
            'scooter_status' => $scooter->fresh()->status,
        ]);
    }

    /**
     * Update lock status
     */
    public function updateLockStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imei' => ['required', 'string'],
            'lock_status' => ['required', 'boolean'], // true = locked, false = unlocked
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $scooter = Scooter::where('device_imei', $request->imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not found',
            ], 404);
        }

        $scooter->update(['is_locked' => $request->lock_status]);

        // Log lock/unlock event
        $logRepository = app(\App\Repositories\ScooterLogRepository::class);
        $logRepository->logManualLock($scooter, null, $request->lock_status);

        return response()->json([
            'success' => true,
            'message' => 'Lock status updated',
            'is_locked' => $scooter->is_locked,
        ]);
    }

    /**
     * Get commands for scooter
     */
    public function getCommands(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imei' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $scooter = Scooter::where('device_imei', $request->imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not found',
            ], 404);
        }

        // Determine commands based on scooter status and current lock state
        // This allows ESP32 to sync with the database state
        $commands = [
            'lock' => false,
            'unlock' => false,
        ];

        // Check if there's a mismatch between desired state (is_locked) and actual state
        // If scooter should be locked but ESP32 reports it's unlocked, send lock command
        // If scooter should be unlocked but ESP32 reports it's locked, send unlock command
        
        // Priority 1: Manual admin commands (if is_locked is set, enforce it)
        // Priority 2: Business logic (rented = unlock, available = lock)
        
        // If scooter is rented, it should be unlocked
        if ($scooter->status === 'rented' && $scooter->is_locked) {
            $commands['unlock'] = true;
        }
        // If scooter is available/maintenance/charging and unlocked, lock it
        elseif (in_array($scooter->status, ['available', 'maintenance', 'charging']) && !$scooter->is_locked) {
            $commands['lock'] = true;
        }
        
        // Note: ESP32 should call this endpoint every 5-10 seconds to get commands

        return response()->json([
            'success' => true,
            'commands' => $commands,
            'scooter_status' => $scooter->status,
            'current_lock_status' => $scooter->is_locked,
        ]);
    }

    /**
     * Report battery status
     */
    public function updateBattery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imei' => ['required', 'string'],
            'battery_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $scooter = Scooter::where('device_imei', $request->imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not found',
            ], 404);
        }

        $oldBattery = $scooter->battery_percentage;
        $scooter->update(['battery_percentage' => $request->battery_percentage]);

        // Log battery drop if significant
        if ($oldBattery && $oldBattery > $request->battery_percentage) {
            $logRepository = app(\App\Repositories\ScooterLogRepository::class);
            $logRepository->logBatteryDrop($scooter, $oldBattery, $request->battery_percentage);
        }

        return response()->json([
            'success' => true,
            'message' => 'Battery updated',
            'battery_percentage' => $scooter->battery_percentage,
        ]);
    }
}
