<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scooter extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'qr_code',
        'status',
        'battery_percentage',
        'latitude',
        'longitude',
        'last_seen_at',
        'is_locked',
        'is_active',
        'device_imei',
        'firmware_version',
        'last_maintenance_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
        'battery_percentage' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function logs()
    {
        return $this->hasMany(ScooterLog::class);
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class);
    }
}
