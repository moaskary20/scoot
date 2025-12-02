<?php

namespace App\Repositories;

use App\Models\MaintenanceRecord;
use App\Models\Scooter;
use App\Repositories\ScooterLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class MaintenanceRepository
{
    public function __construct(
        private readonly ScooterLogRepository $logRepository,
    ) {
        //
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return MaintenanceRecord::query()
            ->with(['scooter', 'scooterLog'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): MaintenanceRecord
    {
        return MaintenanceRecord::with(['scooter', 'scooterLog'])->findOrFail($id);
    }

    public function create(array $data): MaintenanceRecord
    {
        return MaintenanceRecord::create($data);
    }

    public function update(MaintenanceRecord $record, array $data): MaintenanceRecord
    {
        $record->update($data);

        return $record;
    }

    public function delete(MaintenanceRecord $record): void
    {
        $record->delete();
    }

    public function getScooterMaintenanceRecords(int $scooterId, int $perPage = 15): LengthAwarePaginator
    {
        return MaintenanceRecord::where('scooter_id', $scooterId)
            ->with(['scooterLog'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getPendingMaintenance(int $perPage = 15): LengthAwarePaginator
    {
        return MaintenanceRecord::where('status', 'pending')
            ->with(['scooter'])
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_at', 'asc')
            ->paginate($perPage);
    }

    public function getInProgressMaintenance(int $perPage = 15): LengthAwarePaginator
    {
        return MaintenanceRecord::where('status', 'in_progress')
            ->with(['scooter'])
            ->orderByDesc('started_at')
            ->paginate($perPage);
    }

    /**
     * Send scooter to maintenance
     */
    public function sendToMaintenance(
        Scooter $scooter,
        string $title,
        string $type = 'repair',
        ?string $description = null,
        ?string $faultDetails = null,
        ?int $scooterLogId = null,
        ?string $priority = 'medium',
        ?Carbon $scheduledAt = null
    ): MaintenanceRecord {
        // Change scooter status to maintenance
        $scooter->update([
            'status' => 'maintenance',
            'is_locked' => true,
        ]);

        // Log maintenance start
        $this->logRepository->create([
            'scooter_id' => $scooter->id,
            'event_type' => 'maintenance_start',
            'title' => 'Maintenance Started: ' . $title,
            'description' => $description ?? 'Scooter sent to maintenance',
            'severity' => 'info',
            'latitude' => $scooter->latitude,
            'longitude' => $scooter->longitude,
            'scooter_status' => 'maintenance',
            'battery_percentage' => $scooter->battery_percentage,
            'was_locked' => true,
        ]);

        // Create maintenance record
        return $this->create([
            'scooter_id' => $scooter->id,
            'scooter_log_id' => $scooterLogId,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'fault_details' => $faultDetails,
            'priority' => $priority,
            'status' => 'pending',
            'reported_at' => Carbon::now(),
            'scheduled_at' => $scheduledAt,
        ]);
    }

    /**
     * Start maintenance
     */
    public function startMaintenance(MaintenanceRecord $record, array $technicianData = []): MaintenanceRecord
    {
        $data = [
            'status' => 'in_progress',
            'started_at' => Carbon::now(),
        ];

        if (!empty($technicianData)) {
            $data = array_merge($data, $technicianData);
        }

        return $this->update($record, $data);
    }

    /**
     * Complete maintenance
     */
    public function completeMaintenance(
        MaintenanceRecord $record,
        ?float $actualCost = null,
        ?string $resolutionNotes = null,
        ?string $partsReplaced = null,
        ?int $qualityRating = null
    ): MaintenanceRecord {
        $scooter = $record->scooter;

        // Update maintenance record
        $updateData = [
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'actual_cost' => $actualCost,
            'resolution_notes' => $resolutionNotes,
            'parts_replaced' => $partsReplaced,
            'quality_rating' => $qualityRating,
        ];

        $this->update($record, $updateData);

        // Change scooter status back to available
        $scooter->update([
            'status' => 'available',
            'last_maintenance_at' => Carbon::now(),
        ]);

        // Log maintenance end
        $this->logRepository->create([
            'scooter_id' => $scooter->id,
            'event_type' => 'maintenance_end',
            'title' => 'Maintenance Completed: ' . $record->title,
            'description' => $resolutionNotes ?? 'Maintenance completed successfully',
            'severity' => 'info',
            'latitude' => $scooter->latitude,
            'longitude' => $scooter->longitude,
            'scooter_status' => 'available',
            'battery_percentage' => $scooter->battery_percentage,
            'was_locked' => $scooter->is_locked,
        ]);

        return $record->fresh();
    }

    /**
     * Cancel maintenance
     */
    public function cancelMaintenance(MaintenanceRecord $record, ?string $reason = null): MaintenanceRecord
    {
        $scooter = $record->scooter;

        // Update maintenance record
        $this->update($record, [
            'status' => 'cancelled',
            'resolution_notes' => $reason,
        ]);

        // Change scooter status back to available if it was in maintenance
        if ($scooter->status === 'maintenance') {
            $scooter->update(['status' => 'available']);
        }

        return $record->fresh();
    }
}

