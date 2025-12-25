<?php

namespace App\Http\Controllers\WebSocket;

use App\Http\Controllers\Controller;
use App\Services\WebSocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ScooterWebSocketController extends Controller
{
    public function __construct(
        private readonly WebSocketService $webSocketService
    ) {
        //
    }

    /**
     * Handle WebSocket messages from ESP32
     * This endpoint will be called via HTTP POST for compatibility
     * In a full WebSocket implementation, this would be handled differently
     */
    public function handleMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event' => ['required', 'string'],
            'imei' => ['required', 'string'],
            'data' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $event = $request->input('event');
        $imei = $request->input('imei');
        $data = $request->input('data', []);

        try {
            return match ($event) {
                'authenticate' => $this->handleAuthenticate($imei),
                'update-location' => $this->handleUpdateLocation($imei, $data),
                'update-lock-status' => $this->handleUpdateLockStatus($imei, $data),
                'update-battery' => $this->handleUpdateBattery($imei, $data),
                'get-commands' => $this->handleGetCommands($imei),
                default => response()->json([
                    'success' => false,
                    'message' => 'Unknown event: ' . $event,
                ], 400),
            };
        } catch (\Exception $e) {
            Log::error('WebSocket message handling error', [
                'event' => $event,
                'imei' => $imei,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    private function handleAuthenticate(string $imei)
    {
        $result = $this->webSocketService->handleAuthenticate($imei);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    private function handleUpdateLocation(string $imei, array $data)
    {
        $validator = Validator::make($data, [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'battery_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'lock_status' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors(),
            ], 400);
        }

        $result = $this->webSocketService->handleUpdateLocation(
            $imei,
            $data['latitude'],
            $data['longitude'],
            $data['battery_percentage'] ?? null,
            $data['lock_status'] ?? null
        );

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    private function handleUpdateLockStatus(string $imei, array $data)
    {
        $validator = Validator::make($data, [
            'lock_status' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors(),
            ], 400);
        }

        $result = $this->webSocketService->handleUpdateLockStatus($imei, $data['lock_status']);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    private function handleUpdateBattery(string $imei, array $data)
    {
        $validator = Validator::make($data, [
            'battery_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors(),
            ], 400);
        }

        $result = $this->webSocketService->handleUpdateBattery($imei, $data['battery_percentage']);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    private function handleGetCommands(string $imei)
    {
        $scooter = \App\Models\Scooter::where('device_imei', $imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not found',
            ], 404);
        }

        $commands = $this->webSocketService->getCommandsForScooter($scooter);

        return response()->json([
            'success' => true,
            'commands' => $commands,
            'scooter_status' => $scooter->status,
            'current_lock_status' => $scooter->is_locked,
        ]);
    }

    /**
     * Get commands in WebSocket format (with JSON object data, not string)
     * This endpoint returns commands in the format ESP32 expects
     */
    public function getCommandsWebSocketFormat(Request $request)
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

        $imei = $request->input('imei');
        $scooter = \App\Models\Scooter::where('device_imei', $imei)
            ->where('is_active', true)
            ->first();

        if (!$scooter) {
            return response()->json([
                'success' => false,
                'message' => 'Scooter not found',
            ], 404);
        }

        $reverbConfig = config('reverb.apps.apps.0', []);
        $commands = $this->webSocketService->getCommandsForScooter($scooter);

        // Return in WebSocket format with JSON object (not string)
        return response()->json([
            'event' => 'command',
            'data' => [
                'commands' => $commands,
                'timestamp' => now()->toIso8601String(),
                'timeout' => $reverbConfig['activity_timeout'] ?? env('REVERB_APP_ACTIVITY_TIMEOUT', 120),
                'ping_interval' => $reverbConfig['ping_interval'] ?? env('REVERB_APP_PING_INTERVAL', 60),
            ],
            'channel' => 'scooter.' . $imei,
        ]);
    }
}

