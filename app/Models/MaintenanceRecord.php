<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'scooter_id',
        'scooter_log_id',
        'type',
        'title',
        'description',
        'fault_details',
        'priority',
        'status',
        'technician_name',
        'technician_phone',
        'technician_email',
        'reported_at',
        'scheduled_at',
        'started_at',
        'completed_at',
        'estimated_cost',
        'actual_cost',
        'technician_notes',
        'resolution_notes',
        'parts_replaced',
        'quality_rating',
        'quality_notes',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'quality_rating' => 'integer',
        'parts_replaced' => 'array',
    ];

    public function scooter(): BelongsTo
    {
        return $this->belongsTo(Scooter::class);
    }

    public function scooterLog(): BelongsTo
    {
        return $this->belongsTo(ScooterLog::class);
    }
}
