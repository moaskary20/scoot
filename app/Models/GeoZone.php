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
        'description',
    ];

    protected $casts = [
        'polygon' => 'array',
        'center_latitude' => 'float',
        'center_longitude' => 'float',
        'is_active' => 'boolean',
    ];
}
