<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\LoyaltyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileLoyaltyController extends Controller
{
    public function __construct(
        private readonly LoyaltyRepository $repository,
    ) {
        //
    }

    /**
     * Get current user's loyalty summary and recent transactions
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Basic safety check
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $perPage = (int) $request->get('per_page', 20);
            $perPage = max(1, min($perPage, 50)); // Limit page size

            // Get thresholds and redeem settings
            $thresholds = $this->repository->getLevelThresholds();
            $redeemSettings = $this->repository->getPointsRedeemSettings();

            // Get user loyalty transactions (paginated)
            $transactions = $this->repository->getUserTransactions($user->id, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'points' => (int) ($user->loyalty_points ?? 0),
                    'level' => $user->loyalty_level ?? 'bronze',
                    'thresholds' => $thresholds,
                    'redeem_settings' => $redeemSettings,
                    'transactions' => [
                        'data' => $transactions->getCollection()->map(function ($transaction) {
                            return [
                                'id' => $transaction->id,
                                'type' => $transaction->type,
                                'points' => (int) $transaction->points,
                                'balance_after' => (int) $transaction->balance_after,
                                'description' => $transaction->description,
                                'trip_id' => $transaction->trip_id,
                                'created_at' => $transaction->created_at?->toIso8601String(),
                            ];
                        })->values(),
                        'current_page' => $transactions->currentPage(),
                        'last_page' => $transactions->lastPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات نقاط الولاء',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Redeem loyalty points to wallet balance
     */
    public function redeem(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'points' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $points = (int) $request->input('points');

            // Get redeem settings to check minimum
            $redeemSettings = $this->repository->getPointsRedeemSettings();
            
            if (!$redeemSettings['enabled']) {
                return response()->json([
                    'success' => false,
                    'message' => 'استبدال النقاط معطل حالياً',
                ], 400);
            }

            $minPoints = $redeemSettings['min_points_to_redeem'] ?? 100;
            if ($points < $minPoints) {
                return response()->json([
                    'success' => false,
                    'message' => "الحد الأدنى لاستبدال النقاط هو {$minPoints} نقطة",
                ], 400);
            }

            // Check if user has enough points
            $userPoints = (int) ($user->loyalty_points ?? 0);
            if ($userPoints < $points) {
                return response()->json([
                    'success' => false,
                    'message' => "ليس لديك نقاط كافية. النقاط المتاحة: {$userPoints} نقطة",
                ], 400);
            }

            // Redeem points
            $result = $this->repository->redeemPoints($user, $points);

            // Refresh user to get updated balances
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => "تم استبدال {$points} نقطة بنجاح. تم إضافة {$result['wallet_amount']} جنيه إلى المحفظة",
                'data' => [
                    'points_redeemed' => $points,
                    'wallet_amount' => $result['wallet_amount'],
                    'new_points_balance' => (int) $user->loyalty_points,
                    'new_wallet_balance' => (float) $user->wallet_balance,
                    'points_transaction_id' => $result['points_transaction']->id,
                    'wallet_transaction_id' => $result['wallet_transaction']->id,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? 'حدث خطأ في استبدال النقاط',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


