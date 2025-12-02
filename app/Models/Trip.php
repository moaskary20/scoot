<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scooter_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'cost',
        'base_cost',
        'discount_amount',
        'penalty_amount',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'status',
        'zone_exit_detected',
        'zone_exit_details',
        'coupon_id',
        'penalty_id',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cost' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'end_latitude' => 'float',
        'end_longitude' => 'float',
        'zone_exit_detected' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scooter(): BelongsTo
    {
        return $this->belongsTo(Scooter::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Coupon::class);
    }

    public function penalty(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Penalty::class);
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
