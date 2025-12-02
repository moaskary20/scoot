<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trip_id',
        'scooter_id',
        'type',
        'title',
        'description',
        'amount',
        'status',
        'is_auto_applied',
        'applied_at',
        'paid_at',
        'evidence_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_auto_applied' => 'boolean',
        'applied_at' => 'datetime',
        'paid_at' => 'datetime',
        'evidence_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function scooter(): BelongsTo
    {
        return $this->belongsTo(Scooter::class);
    }
}
