<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Coupon;
use App\Models\User;
use App\Repositories\WalletRepository;
use App\Repositories\CouponRepository;
use App\Services\PaymobService;
use Illuminate\Http\Request;

class MobileWalletController extends Controller
{
    public function __construct(
        private readonly WalletRepository $repository,
        private readonly CouponRepository $couponRepository,
        private readonly PaymobService $paymobService,
    ) {
        //
    }

    /**
     * Get current user's wallet balance
     */
    public function getBalance(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => (float) ($user->wallet_balance ?? 0),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب رصيد المحفظة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current user's wallet transactions
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $transactions = $this->repository->getUserTransactions($user->id, $perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب معاملات المحفظة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Top up wallet
     */
    public function topUp(Request $request)
    {
        try {
            $data = $request->validate([
                'amount' => ['required', 'numeric', 'min:0.01'],
                'payment_method' => ['nullable', 'string', 'in:paymob,manual'],
            ]);

            $user = $request->user();
            $paymentMethod = $data['payment_method'] ?? 'paymob';

            // If Paymob is selected, create Paymob payment
            if ($paymentMethod === 'paymob' && $this->paymobService->isEnabled()) {
                return $this->createPaymobPayment($user, $data['amount']);
            }

            // Otherwise, create manual transaction
            $transaction = $this->repository->topUp(
                $user,
                $data['amount'],
                $paymentMethod,
                null,
                'شحن رصيد من التطبيق',
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'تم بدء عملية الشحن بنجاح',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'balance_after' => $user->fresh()->wallet_balance,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في شحن الرصيد',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Paymob payment
     */
    private function createPaymobPayment(User $user, float $amount)
    {
        try {
            // Create pending transaction first
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'top_up',
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $user->wallet_balance,
                'balance_after' => $user->wallet_balance, // Will be updated after payment
                'payment_method' => 'paymob',
                'status' => 'pending',
                'description' => "شحن رصيد من التطبيق - {$amount} جنيه",
                'metadata' => [
                    'payment_gateway' => 'paymob',
                    'created_at' => now()->toDateTimeString(),
                ],
                'processed_at' => now(),
            ]);

            // Create Paymob order
            $order = $this->paymobService->createOrder(
                $amount,
                [
                    [
                        'name' => 'شحن رصيد المحفظة',
                        'amount_cents' => (int) ($amount * 100),
                        'description' => 'شحن رصيد المحفظة',
                        'quantity' => 1,
                    ],
                ],
                [
                    'apartment' => 'N/A',
                    'email' => $user->email,
                    'floor' => 'N/A',
                    'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                    'street' => 'N/A',
                    'building' => 'N/A',
                    'phone_number' => $user->phone ?? '+201000000000',
                    'shipping_method' => 'N/A',
                    'postal_code' => 'N/A',
                    'city' => 'N/A',
                    'country' => 'EG',
                    'last_name' => explode(' ', $user->name)[1] ?? '',
                    'state' => 'N/A',
                ]
            );

            // Get callback URL for mobile app
            $callbackUrl = url('/api/wallet/paymob/callback');

            // Get payment key
            $paymentKey = $this->paymobService->getPaymentKey(
                $order['id'],
                $amount,
                [
                    'apartment' => 'N/A',
                    'email' => $user->email,
                    'floor' => 'N/A',
                    'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                    'street' => 'N/A',
                    'building' => 'N/A',
                    'phone_number' => $user->phone ?? '+201000000000',
                    'shipping_method' => 'N/A',
                    'postal_code' => 'N/A',
                    'city' => 'N/A',
                    'country' => 'EG',
                    'last_name' => explode(' ', $user->name)[1] ?? '',
                    'state' => 'N/A',
                ],
                $callbackUrl
            );

            // Update transaction with Paymob order ID
            $transaction->update([
                'reference' => (string) $order['id'],
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'paymob_order_id' => $order['id'],
                    'paymob_payment_key' => $paymentKey,
                ]),
            ]);

            // Get payment URL
            $paymentUrl = $this->paymobService->getPaymentUrl($paymentKey);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء عملية الدفع بنجاح',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'payment_url' => $paymentUrl,
                    'order_id' => $order['id'],
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Paymob payment creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء عملية الدفع: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate promo code
     */
    public function validatePromoCode(Request $request)
    {
        try {
            $data = $request->validate([
                'code' => ['required', 'string', 'max:50'],
            ]);

            $user = $request->user();
            $coupon = $this->couponRepository->findByCode(strtoupper(trim($data['code'])));

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'كود البرومو غير صحيح',
                ], 404);
            }

            if (!$coupon->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'كود البرومو غير صالح أو منتهي الصلاحية',
                ], 400);
            }

            if (!$coupon->canBeUsedBy($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد استخدمت هذا الكود من قبل',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'كود البرومو صحيح',
                'data' => [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'max_discount' => $coupon->max_discount,
                    'description' => $coupon->description,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في التحقق من كود البرومو',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Paymob callback handler
     */
    public function paymobCallback(Request $request)
    {
        try {
            $callbackData = $request->all();
            
            // Verify HMAC
            if (!$this->paymobService->verifyCallback($callbackData)) {
                \Log::warning('Invalid Paymob callback HMAC', $callbackData);
                return response()->json(['error' => 'Invalid HMAC'], 400);
            }

            $orderId = $callbackData['order']['id'] ?? null;
            $success = $callbackData['success'] ?? false;
            $amountCents = $callbackData['amount_cents'] ?? 0;
            $amount = $amountCents / 100;

            // Find transaction by Paymob order ID
            $transaction = WalletTransaction::where('reference', (string) $orderId)
                ->where('payment_method', 'paymob')
                ->where('status', 'pending')
                ->first();

            if (!$transaction) {
                \Log::warning('Transaction not found for Paymob order', ['order_id' => $orderId]);
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            if ($success) {
                // Update transaction status
                $user = $transaction->user;
                $balanceBefore = $user->wallet_balance;
                $balanceAfter = $balanceBefore + $amount;

                $transaction->update([
                    'status' => 'completed',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'paymob_transaction_id' => $callbackData['id'] ?? null,
                        'paymob_callback_data' => $callbackData,
                        'completed_at' => now()->toDateTimeString(),
                    ]),
                    'processed_at' => now(),
                ]);

                // Update user balance
                $user->update(['wallet_balance' => $balanceAfter]);

                \Log::info('Paymob payment completed', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                ]);
            } else {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'paymob_callback_data' => $callbackData,
                        'failed_at' => now()->toDateTimeString(),
                    ]),
                ]);

                \Log::warning('Paymob payment failed', [
                    'transaction_id' => $transaction->id,
                    'callback_data' => $callbackData,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Paymob callback error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json(['error' => $e->getMessage()            ], 500);
        }
    }

    /**
     * Save card (tokenize with Paymob)
     */
    public function saveCard(Request $request)
    {
        try {
            $data = $request->validate([
                'card_number' => ['required', 'string', 'min:13', 'max:19'],
                'card_holder_name' => ['required', 'string', 'max:255'],
                'expiry_month' => ['required', 'string', 'size:2'],
                'expiry_year' => ['required', 'string', 'size:4'],
                'cvv' => ['required', 'string', 'min:3', 'max:4'],
                'is_default' => ['nullable', 'boolean'],
            ]);

            $user = $request->user();

            // Tokenize card with Paymob
            $token = $this->tokenizeCardWithPaymob($data);

            // Save card to database
            $card = \DB::table('user_cards')->insertGetId([
                'user_id' => $user->id,
                'card_number' => substr($data['card_number'], -4), // Store only last 4 digits
                'card_holder_name' => $data['card_holder_name'],
                'expiry_month' => $data['expiry_month'],
                'expiry_year' => $data['expiry_year'],
                'token' => $token,
                'is_default' => $data['is_default'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // If this is default, unset other default cards
            if ($data['is_default'] ?? false) {
                \DB::table('user_cards')
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $card)
                    ->update(['is_default' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الكارت بنجاح',
                'data' => [
                    'id' => $card,
                    'card_number' => '**** **** **** ' . substr($data['card_number'], -4),
                    'card_holder_name' => $data['card_holder_name'],
                    'expiry_month' => $data['expiry_month'],
                    'expiry_year' => $data['expiry_year'],
                    'token' => $token,
                    'is_default' => $data['is_default'] ?? false,
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Save card error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في حفظ الكارت: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's saved cards
     */
    public function getCards(Request $request)
    {
        try {
            $user = $request->user();

            $cards = \DB::table('user_cards')
                ->where('user_id', $user->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'card_number' => $card->card_number,
                        'card_holder_name' => $card->card_holder_name,
                        'expiry_month' => $card->expiry_month,
                        'expiry_year' => $card->expiry_year,
                        'token' => $card->token,
                        'is_default' => (bool) $card->is_default,
                        'created_at' => $card->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $cards,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الكروت',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete card
     */
    public function deleteCard(Request $request, int $id)
    {
        try {
            $user = $request->user();

            $deleted = \DB::table('user_cards')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الكارت بنجاح',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'الكارت غير موجود',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في حذف الكارت',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tokenize card with Paymob
     * Note: Paymob tokenization requires special setup. For now, we'll create a reference token.
     * In production, you should use Paymob's tokenization API properly.
     */
    private function tokenizeCardWithPaymob(array $cardData): string
    {
        try {
            // Generate a unique token reference
            // In production, you would call Paymob's tokenization API here
            $token = 'pmb_' . uniqid() . '_' . substr($cardData['card_number'], -4) . '_' . time();
            
            \Log::info('Card tokenized', [
                'last_4' => substr($cardData['card_number'], -4),
                'token' => $token,
            ]);
            
            return $token;
        } catch (\Exception $e) {
            \Log::error('Card tokenization failed: ' . $e->getMessage());
            // Return a reference token anyway
            return 'pmb_' . uniqid() . '_' . substr($cardData['card_number'], -4);
        }
    }
}

