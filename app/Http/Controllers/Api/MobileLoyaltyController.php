<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\LoyaltyRepository;
use Illuminate\Http\Request;

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
}


