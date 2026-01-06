<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'age',
        'university_id',
        'national_id_photo',
        'national_id_front_photo',
        'national_id_back_photo',
        'wallet_balance',
        'loyalty_points',
        'loyalty_level',
        'avatar',
        'is_active',
        'review_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
            'loyalty_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function loyaltyPointsTransactions()
    {
        return $this->hasMany(LoyaltyPointsTransaction::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // Referrals where this user is the referrer
    public function referrals()
    {
        return $this->hasMany(\App\Models\Referral::class, 'referrer_id');
    }

    // Referral where this user was referred
    public function referredBy()
    {
        return $this->hasOne(\App\Models\Referral::class, 'referred_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * Get calculated wallet balance from transactions
     */
    public function getCalculatedWalletBalanceAttribute(): float
    {
        $lastTransaction = $this->walletTransactions()
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($lastTransaction) {
            return (float) $lastTransaction->balance_after;
        }

        return 0.0;
    }

    /**
     * Get calculated loyalty level from loyalty points
     */
    public function getCalculatedLoyaltyLevelAttribute(): string
    {
        $points = $this->loyalty_points;
        
        // الحصول على العتبات من الإعدادات
        $thresholds = \DB::table('loyalty_settings')
            ->whereIn('key', ['bronze_threshold', 'silver_threshold', 'gold_threshold'])
            ->pluck('value', 'key')
            ->toArray();
        
        $goldThreshold = (int) ($thresholds['gold_threshold'] ?? 1000);
        $silverThreshold = (int) ($thresholds['silver_threshold'] ?? 500);
        $bronzeThreshold = (int) ($thresholds['bronze_threshold'] ?? 0);
        
        if ($points >= $goldThreshold) {
            return 'gold';
        } elseif ($points >= $silverThreshold) {
            return 'silver';
        } else {
            return 'bronze';
        }
    }
}
