<?php

namespace App\Repositories;

use App\Models\Scooter;
use App\Models\ScooterLog;
use App\Models\Trip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ScooterLogRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ScooterLog::query()
            ->with(['scooter', 'trip', 'user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): ScooterLog
    {
        return ScooterLog::with(['scooter', 'trip', 'user'])->findOrFail($id);
    }

    public function create(array $data): ScooterLog
    {
        return ScooterLog::create($data);
    }

    public function update(ScooterLog $log, array $data): ScooterLog
    {
        $log->update($data);

        return $log;
    }

    public function delete(ScooterLog $log): void
    {
        $log->delete();
    }

    public function getScooterLogs(int $scooterId, int $perPage = 15): LengthAwarePaginator
    {
        return ScooterLog::where('scooter_id', $scooterId)
            ->with(['trip', 'user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getCriticalLogs(int $perPage = 15): LengthAwarePaginator
    {
        return ScooterLog::where('severity', 'critical')
            ->where('is_resolved', false)
            ->with(['scooter', 'trip', 'user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getUnresolvedLogs(int $perPage = 15): LengthAwarePaginator
    {
        return ScooterLog::where('is_resolved', false)
            ->where('severity', '!=', 'info')
            ->with(['scooter', 'trip', 'user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function markAsResolved(ScooterLog $log, ?string $notes = null): ScooterLog
    {
        $log->update([
            'is_resolved' => true,
            'resolved_at' => Carbon::now(),
            'resolution_notes' => $notes,
        ]);

        return $log->fresh();
    }

    /**
     * Log battery drop event
     */
    public function logBatteryDrop(Scooter $scooter, int $oldBattery, int $newBattery): ScooterLog
    {
        $dropAmount = $oldBattery - $newBattery;
        $severity = $dropAmount > 20 ? 'critical' : ($dropAmount > 10 ? 'warning' : 'info');

        return $this->create([
            'scooter_id' => $scooter->id,
            'event_type' => 'battery_drop',
            'title' => "Battery dropped from {$oldBattery}% to {$newBattery}%",
            'description' => "Battery percentage decreased by {$dropAmount}%",
            'severity' => $severity,
            'data' => [
                'old_battery' => $oldBattery,
                'new_battery' => $newBattery,
                'drop_amount' => $dropAmount,
            ],
            'latitude' => $scooter->latitude,
            'longitude' => $scooter->longitude,
            'scooter_status' => $scooter->status,
            'battery_percentage' => $newBattery,
            'was_locked' => $scooter->is_locked,
        ]);
    }

    /**
     * Log zone exit event
     */
    public function logZoneExit(Scooter $scooter, ?Trip $trip = null, array $exitData = []): ScooterLog
    {
        return $this->create([
            'scooter_id' => $scooter->id,
            'trip_id' => $trip?->id,
            'user_id' => $trip?->user_id,
            'event_type' => 'zone_exit',
            'title' => 'Zone Exit Detected',
            'description' => $exitData['message'] ?? 'Scooter exited allowed zone',
            'severity' => 'warning',
            'data' => $exitData,
            'latitude' => $scooter->latitude,
            'longitude' => $scooter->longitude,
            'scooter_status' => $scooter->status,
            'battery_percentage' => $scooter->battery_percentage,
            'was_locked' => $scooter->is_locked,
        ]);
    }

    /**
     * Log forced movement (movement without active trip)
     */
    public function logForcedMovement(Scooter $scooter, array $movementData = []): ScooterLog
    {
        // قفل السكوتر تلقائياً
        $scooter->update(['is_locked' => true]);

        return $this->create([
            'scooter_id' => $scooter->id,
            'event_type' => 'forced_movement',
            'title' => 'Forced Movement Detected - Scooter Auto-Locked',
            'description' => 'Scooter moved without an active trip. Auto-locked for security.',
            'severity' => 'critical',
            'data' => $movementData,
            'latitude' => $scooter->latitude,
            'longitude' => $scooter->longitude,
            'scooter_status' => $scooter->status,
            'battery_percentage' => $scooter->battery_percentage,
            'was_locked' => false, // كان غير مقفول قبل القفل التلقائي
        ]);
    }

    /**
     * Log manual lock/unlock
     */
    public function logManualLock(Scooter $scooter, ?int $userId = null, bool $isLocked = true): ScooterLog
    {
        return $this->create([
            'scooter_id' => $scooter->id,
            'user_id' => $userId,
            'event_type' => $isLocked ? 'manual_lock' : 'manual_unlock',
            'title' => $isLocked ? 'Scooter Manually Locked' : 'Scooter Manually Unlocked',
            'description' => $isLocked ? 'Scooter was manually locked' : 'Scooter was manually unlocked',
            'severity' => 'info',
            'latitude' => $scooter->latitude,
            'longitude' => $scooter->longitude,
            'scooter_status' => $scooter->status,
            'battery_percentage' => $scooter->battery_percentage,
            'was_locked' => !$isLocked, // الحالة السابقة
        ]);
    }

    /**
     * Log auto lock/unlock
     */
    public function logAutoLock(Scooter $scooter, string $reason, bool $isLocked = true): ScooterLog
    {
        return $this->create([
            'scooter_id' => $scooter->id,
            'event_type' => $isLocked ? 'auto_lock' : 'auto_unlock',
            'title' => $isLocked ? 'Scooter Auto-Locked' : 'Scooter Auto-Unlocked',
            'description' => $reason,
            'severity' => $isLocked ? 'warning' : 'info',
            'latitude' => $scooter->latitude,
            'longitude' => $scooter->longitude,
            'scooter_status' => $scooter->status,
            'battery_percentage' => $scooter->battery_percentage,
            'was_locked' => !$isLocked,
        ]);
    }
}

