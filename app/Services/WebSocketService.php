<?php

namespace App\Services;

use App\Events\ScooterCommand;
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
     * Send command to ESP32 via both WebSocket (for Postman/testing) and MQTT (for ESP32)
     */
    public function sendCommandToScooter(Scooter $scooter, array $command): void
    {
        Log::info('🔍 sendCommandToScooter called', [
            'scooter_id' => $scooter->id,
            'scooter_code' => $scooter->code,
            'device_imei_raw' => $scooter->device_imei,
            'device_imei_type' => gettype($scooter->device_imei),
            'device_imei_empty' => empty($scooter->device_imei),
            'device_imei_null' => is_null($scooter->device_imei),
        ]);

        if (!$scooter->device_imei) {
            Log::warning('❌ Cannot send command: Scooter has no device_imei', [
                'scooter_id' => $scooter->id,
                'scooter_code' => $scooter->code,
            ]);
            return;
        }

        Log::info('📡 Sending command to scooter via WebSocket and MQTT', [
            'scooter_id' => $scooter->id,
            'scooter_code' => $scooter->code,
            'device_imei' => $scooter->device_imei,
            'command' => $command,
        ]);

        // Send command via WebSocket (for Postman/testing)
        try {
            event(new ScooterCommand($scooter->device_imei, $command));
            Log::info('✅ Command sent via WebSocket successfully', [
                'imei' => $scooter->device_imei,
                'command' => $command,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to send command via WebSocket', [
                'imei' => $scooter->device_imei,
                'command' => $command,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        // Send command via MQTT (for ESP32)
        try {
            $mqttService = app(MqttService::class);
            $mqttService->publishCommand($scooter->device_imei, $command);
            
            Log::info('✅ Command sent via MQTT successfully', [
                'imei' => $scooter->device_imei,
                'command' => $command,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to send command via MQTT', [
                'imei' => $scooter->device_imei,
                'command' => $command,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

