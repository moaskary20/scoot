<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'minutes_included',
        'price',
        'billing_period',
        'starts_at',
        'expires_at',
        'renewed_at',
        'auto_renew',
        'status',
        'minutes_used',
        'trips_count',
        'coupon_id',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'renewed_at' => 'datetime',
        'auto_renew' => 'boolean',
        'minutes_included' => 'integer',
        'minutes_used' => 'integer',
        'trips_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && Carbon::now()->gte($this->starts_at)
            && Carbon::now()->lte($this->expires_at);
    }

    /**
     * Check if subscription has available minutes
     */
    public function hasAvailableMinutes(): bool
    {
        if ($this->type === 'unlimited') {
            return true;
        }

        return $this->minutes_used < $this->minutes_included;
    }

    /**
     * Get remaining minutes
     */
    public function getRemainingMinutes(): ?int
    {
        if ($this->type === 'unlimited') {
            return null; // Unlimited
        }

        return max(0, $this->minutes_included - $this->minutes_used);
    }

    /**
     * Calculate next renewal date
     */
    public function getNextRenewalDate(): Carbon
    {
        $periods = [
            'daily' => 1,
            'weekly' => 7,
            'monthly' => 30,
            'yearly' => 365,
        ];

        $days = $periods[$this->billing_period] ?? 30;
        
        return Carbon::parse($this->expires_at)->addDays($days);
    }
}
