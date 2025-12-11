<?php

namespace App\Services;

use App\Models\Scooter;
use App\Repositories\ScooterLogRepository;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

class WebSocketService
{
    public function __construct(
        private readonly AntiTheftService $antiTheftService,
        private readonly ScooterLogRepository $logRepository,
    ) {
        //
    }

    /**
     * Handle authentication from ESP32
     */
    public function handleAuthenticate(string $imei): array
    {
        $scooter = Scooter::where('device_imei', $imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return [
                'success' => false,
                'message' => 'Scooter not found or inactive',
            ];
        }

        // Get initial commands
        $commands = $this->getCommandsForScooter($scooter);

        return [
            'success' => true,
            'scooter_id' => $scooter->id,
            'code' => $scooter->code,
            'commands' => $commands,
            'status' => $scooter->status,
        ];
    }

    /**
     * Handle location update from ESP32
     */
    public function handleUpdateLocation(string $imei, float $latitude, float $longitude, ?int $batteryPercentage = null, ?bool $lockStatus = null): array
    {
        $scooter = Scooter::where('device_imei', $imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return [
                'success' => false,
                'message' => 'Scooter not found',
            ];
        }

        // Update GPS and check for unauthorized movement
        $this->antiTheftService->updateScooterGPS(
            $scooter,
            $latitude,
            $longitude,
            $batteryPercentage
        );

        // Update lock status if provided
        if ($lockStatus !== null) {
            $scooter->update(['is_locked' => $lockStatus]);
        }

        // Check zone exit
        $activeTrip = $scooter->trips()->where('status', 'active')->first();
        $this->antiTheftService->checkZoneExit($scooter, $activeTrip);

        // Get pending commands
        $commands = $this->getCommandsForScooter($scooter);

        return [
            'success' => true,
            'message' => 'Location updated',
            'commands' => $commands,
            'scooter_status' => $scooter->fresh()->status,
        ];
    }

    /**
     * Handle lock status update from ESP32
     */
    public function handleUpdateLockStatus(string $imei, bool $lockStatus): array
    {
        $scooter = Scooter::where('device_imei', $imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return [
                'success' => false,
                'message' => 'Scooter not found',
            ];
        }

        $scooter->update(['is_locked' => $lockStatus]);

        // Log lock/unlock event
        $this->logRepository->logManualLock($scooter, null, $lockStatus);

        return [
            'success' => true,
            'message' => 'Lock status updated',
            'is_locked' => $scooter->is_locked,
        ];
    }

    /**
     * Handle battery update from ESP32
     */
    public function handleUpdateBattery(string $imei, int $batteryPercentage): array
    {
        $scooter = Scooter::where('device_imei', $imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return [
                'success' => false,
                'message' => 'Scooter not found',
            ];
        }

        $oldBattery = $scooter->battery_percentage;
        $scooter->update(['battery_percentage' => $batteryPercentage]);

        // Log battery drop if significant
        if ($oldBattery !== null && $oldBattery > $batteryPercentage) {
            $this->logRepository->logBatteryDrop($scooter, $oldBattery, $batteryPercentage);
        }

        return [
            'success' => true,
            'message' => 'Battery updated',
            'battery_percentage' => $scooter->battery_percentage,
        ];
    }

    /**
     * Get commands for scooter
     */
    public function getCommandsForScooter(Scooter $scooter): array
    {
        $commands = [
            'lock' => false,
            'unlock' => false,
        ];

        // If scooter is rented, it should be unlocked
        if ($scooter->status === 'rented' && $scooter->is_locked) {
            $commands['unlock'] = true;
        }
        // If scooter is available/maintenance/charging and unlocked, lock it
        elseif (in_array($scooter->status, ['available', 'maintenance', 'charging']) && !$scooter->is_locked) {
            $commands['lock'] = true;
        }

        return $commands;
    }

    /**
     * Send command to ESP32 via WebSocket
     */
    public function sendCommandToScooter(Scooter $scooter, array $command): void
    {
        if (!$scooter->device_imei) {
            return;
        }

        // Broadcast command to scooter channel
        broadcast(new \App\Events\ScooterCommand($scooter->device_imei, $command));
    }
}

