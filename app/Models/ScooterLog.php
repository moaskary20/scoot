<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScooterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'scooter_id',
        'trip_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'severity',
        'data',
        'latitude',
        'longitude',
        'scooter_status',
        'battery_percentage',
        'was_locked',
        'is_resolved',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'data' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'battery_percentage' => 'integer',
        'was_locked' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function scooter(): BelongsTo
    {
        return $this->belongsTo(Scooter::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceRecord()
    {
        return $this->hasOne(MaintenanceRecord::class);
    }
}
