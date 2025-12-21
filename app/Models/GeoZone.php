<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeoZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'color',
        'polygon',
        'center_latitude',
        'center_longitude',
        'is_active',
        'allow_trip_start',
        'price_per_minute',
        'trip_start_fee',
        'description',
    ];

    protected $casts = [
        'polygon' => 'array',
        'center_latitude' => 'float',
        'center_longitude' => 'float',
        'is_active' => 'boolean',
        'allow_trip_start' => 'boolean',
        'price_per_minute' => 'decimal:2',
        'trip_start_fee' => 'decimal:2',
    ];
}
