<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobileReferralController extends Controller
{
    /**
     * Get user's referral data
     */
    public function getReferralData(Request $request)
    {
        try {
            $user = $request->user();
            
            // Generate or get referral code
            $referralCode = $this->generateReferralCode($user->id);
            
            // Get completed referrals (friends who completed their first trip)
            $completedReferrals = Referral::where('referrer_id', $user->id)
                ->where('status', 'completed')
                ->count();
            
            // Get rewarded referrals (friends who earned reward for referrer)
            $rewardedReferrals = Referral::where('referrer_id', $user->id)
                ->where('status', 'rewarded')
                ->count();
            
            // Calculate total earned
            $totalEarned = Referral::where('referrer_id', $user->id)
                ->where('status', 'rewarded')
                ->sum('reward_amount');
            
            // Generate affiliate link
            $affiliateLink = $this->generateAffiliateLink($referralCode);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'referral_code' => $referralCode,
                    'affiliate_link' => $affiliateLink,
                    'referred_friends_count' => $completedReferrals,
                    'total_earned' => (float) $totalEarned,
                    'max_referrals' => 5,
                    'reward_per_referral' => 30.0,
                    'is_active' => true,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات الإحالة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate referral code from user ID
     */
    private function generateReferralCode(int $userId): string
    {
        // Generate a unique code based on user ID
        // Format: LINER + last 6 digits of user ID (padded)
        $code = 'LINER' . str_pad($userId, 6, '0', STR_PAD_LEFT);
        return strtoupper($code);
    }

    /**
     * Generate affiliate link
     */
    private function generateAffiliateLink(string $referralCode): string
    {
        // Get base URL from config or use default
        $baseUrl = config('app.url', 'http://192.168.1.44:8000');
        
        // Generate affiliate link
        // Format: https://app.linerscoot.com/register?ref=CODE
        // For now, we'll use a deep link format that can be handled by the app
        $affiliateLink = "$baseUrl/register?ref=$referralCode";
        
        return $affiliateLink;
    }

    /**
     * Track referral when user registers with referral code
     */
    public static function trackReferral(User $referredUser, string $referralCode): ?Referral
    {
        try {
            // Extract user ID from referral code (LINER000001 -> 1)
            $code = str_replace('LINER', '', strtoupper($referralCode));
            $referrerId = (int) ltrim($code, '0');
            
            if ($referrerId <= 0) {
                return null;
            }
            
            // Check if referrer exists
            $referrer = User::find($referrerId);
            if (!$referrer || $referrer->id === $referredUser->id) {
                return null; // Can't refer yourself
            }
            
            // Check if already referred
            $existing = Referral::where('referrer_id', $referrerId)
                ->where('referred_id', $referredUser->id)
                ->first();
            
            if ($existing) {
                return $existing;
            }
            
            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $referrerId,
                'referred_id' => $referredUser->id,
                'referral_code' => $referralCode,
                'status' => 'pending',
                'registered_at' => now(),
            ]);
            
            return $referral;
        } catch (\Exception $e) {
            \Log::error('Failed to track referral: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark referral as completed when user completes first trip
     */
    public static function markReferralCompleted(int $userId, int $tripId): void
    {
        try {
            $referral = Referral::where('referred_id', $userId)
                ->where('status', 'pending')
                ->first();
            
            if (!$referral) {
                return;
            }
            
            // Update referral status
            $referral->update([
                'status' => 'completed',
                'trip_id' => $tripId,
                'trip_completed_at' => now(),
            ]);
            
            // Check if referrer has reached a multiple of 5 completed referrals (that haven't been rewarded)
            $pendingCompletedCount = Referral::where('referrer_id', $referral->referrer_id)
                ->where('status', 'completed')
                ->whereNull('rewarded_at')
                ->count();
            
            // If reached a multiple of 5, give reward
            if ($pendingCompletedCount >= 5 && $pendingCompletedCount % 5 == 0) {
                self::giveReward($referral->referrer_id);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to mark referral as completed: ' . $e->getMessage());
        }
    }

    /**
     * Give reward to referrer (30 EGP for every 5 completed referrals)
     */
    private static function giveReward(int $referrerId): void
    {
        try {
            // Get all completed referrals that haven't been rewarded yet
            $pendingReferrals = Referral::where('referrer_id', $referrerId)
                ->where('status', 'completed')
                ->whereNull('rewarded_at')
                ->orderBy('trip_completed_at')
                ->get();
            
            // Process in batches of 5
            $batchSize = 5;
            $rewardAmount = 30.0; // 30 EGP for every 5 friends
            
            for ($i = 0; $i < $pendingReferrals->count(); $i += $batchSize) {
                $batch = $pendingReferrals->slice($i, $batchSize);
                
                if ($batch->count() < $batchSize) {
                    break; // Need exactly 5 to give reward
                }
                
                // Mark these 5 referrals as rewarded
                foreach ($batch as $referral) {
                    $referral->update([
                        'status' => 'rewarded',
                        'reward_amount' => $rewardAmount / $batchSize, // Distribute reward amount
                        'rewarded_at' => now(),
                    ]);
                }
                
                // Add reward to referrer's wallet (30 EGP for this batch of 5)
                $referrer = User::find($referrerId);
                if ($referrer) {
                    $balanceBefore = $referrer->wallet_balance;
                    $referrer->increment('wallet_balance', $rewardAmount);
                    
                    // Create wallet transaction
                    \App\Models\WalletTransaction::create([
                        'user_id' => $referrerId,
                        'type' => 'refund',
                        'transaction_type' => 'credit',
                        'amount' => $rewardAmount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $referrer->wallet_balance,
                        'payment_method' => 'referral',
                        'status' => 'completed',
                        'description' => "مكافأة إحالة - 5 أصدقاء أكملوا رحلتهم الأولى",
                        'processed_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to give referral reward: ' . $e->getMessage());
        }
    }
}
