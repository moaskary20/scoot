<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Repositories\WalletRepository;
use App\Services\PaymobService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletRepository $repository,
        private readonly PaymobService $paymobService,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = WalletTransaction::query()->with(['user', 'trip']);

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20)->appends(request()->query());
        
        // Get all users for filter dropdown
        $users = User::orderBy('name')->get();

        return view('admin.wallet.index', compact('transactions', 'users'));
    }

    public function show(WalletTransaction $walletTransaction)
    {
        $walletTransaction->load(['user', 'trip']);

        return view('admin.wallet.show', compact('walletTransaction'));
    }

    public function topUp(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $transaction = $this->repository->topUp(
                $user,
                $data['amount'],
                $data['payment_method'] ?? null,
                $data['reference'] ?? null,
                $data['description'] ?? null
            );

            return redirect()
                ->route('admin.users.show', $user)
                ->with('status', __('Wallet topped up successfully. New balance: :balance EGP', ['balance' => number_format($user->fresh()->wallet_balance, 2)]));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function refund(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'trip_id' => ['nullable', 'exists:trips,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $trip = $data['trip_id'] ? \App\Models\Trip::find($data['trip_id']) : null;

            $transaction = $this->repository->refund(
                $user,
                $data['amount'],
                $trip,
                $data['description'] ?? null,
                $data['reference'] ?? null
            );

            return redirect()
                ->route('admin.users.show', $user)
                ->with('status', __('Refund processed successfully. New balance: :balance EGP', ['balance' => number_format($user->fresh()->wallet_balance, 2)]));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function adjust(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_type' => ['required', 'in:credit,debit'],
            'description' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $transaction = $this->repository->adjust(
                $user,
                $data['amount'],
                $data['transaction_type'],
                $data['description'] ?? null,
                $data['notes'] ?? null
            );

            $message = $data['transaction_type'] === 'credit'
                ? __('Balance adjusted (added) successfully. New balance: :balance EGP', ['balance' => number_format($user->fresh()->wallet_balance, 2)])
                : __('Balance adjusted (deducted) successfully. New balance: :balance EGP', ['balance' => number_format($user->fresh()->wallet_balance, 2)]);

            return redirect()
                ->route('admin.users.show', $user)
                ->with('status', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function userTransactions(User $user)
    {
        $transactions = $this->repository->getUserTransactions($user->id, 20);
        $statistics = $this->repository->getUserStatistics($user->id);

        return view('admin.wallet.user-transactions', compact('user', 'transactions', 'statistics'));
    }

    public function createTransaction(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'transaction_type' => ['required', 'in:credit,debit'],
            'type' => ['required', 'in:top_up,adjustment,refund,penalty,trip_payment,subscription'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'in:manual,paymob'],
            'description' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $user = User::findOrFail($data['user_id']);
            $paymentMethod = $data['payment_method'] ?? 'manual';

            // If Paymob is selected and transaction is credit, create Paymob payment
            if ($paymentMethod === 'paymob' && $data['transaction_type'] === 'credit' && $this->paymobService->isEnabled()) {
                return $this->createPaymobPayment($user, $data);
            }

            // Otherwise, create transaction directly
            $transaction = $this->repository->adjust(
                $user,
                $data['amount'],
                $data['transaction_type'],
                $data['description'] ?? null,
                $data['notes'] ?? null
            );

            // Update transaction type and payment method if needed
            if ($data['type'] !== 'adjustment') {
                $transaction->update(['type' => $data['type']]);
            }
            
            if ($paymentMethod !== 'manual') {
                $transaction->update(['payment_method' => $paymentMethod]);
            }

            $message = $data['transaction_type'] === 'credit'
                ? trans('messages.Balance added successfully. New balance: :balance EGP', ['balance' => number_format($user->fresh()->wallet_balance, 2)])
                : trans('messages.Balance deducted successfully. New balance: :balance EGP', ['balance' => number_format($user->fresh()->wallet_balance, 2)]);

            return redirect()
                ->route('admin.wallet.index')
                ->with('status', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function createPaymobPayment(User $user, array $data)
    {
        try {
            // Create pending transaction first
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'type' => $data['type'],
                'transaction_type' => 'credit',
                'amount' => $data['amount'],
                'balance_before' => $user->wallet_balance,
                'balance_after' => $user->wallet_balance, // Will be updated after payment
                'payment_method' => 'paymob',
                'status' => 'pending',
                'description' => $data['description'] ?? "Wallet top-up via Paymob - {$data['amount']} EGP",
                'notes' => $data['notes'],
                'metadata' => [
                    'payment_gateway' => 'paymob',
                    'created_at' => now()->toDateTimeString(),
                ],
                'processed_at' => now(),
            ]);

            // Create Paymob order
            $order = $this->paymobService->createOrder(
                $data['amount'],
                [
                    [
                        'name' => 'Wallet Top-up',
                        'amount_cents' => (int) ($data['amount'] * 100),
                        'description' => $data['description'] ?? "Wallet top-up for user {$user->name}",
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

            // Get callback URL
            $callbackUrl = $this->paymobService->getCallbackUrl();

            // Get payment key
            $paymentKey = $this->paymobService->getPaymentKey(
                $order['id'],
                $data['amount'],
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
                'reference' => $order['id'],
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'paymob_order_id' => $order['id'],
                    'paymob_payment_key' => $paymentKey,
                ]),
            ]);

            // Get payment URL - Use direct redirect instead of iframe to avoid "Must be iframe owner" error
            $paymentUrl = $this->paymobService->getPaymentUrl($paymentKey);

            // Store transaction ID in session for return handling
            session(['paymob_transaction_id' => $transaction->id]);

            // Redirect directly to Paymob payment page
            // Paymob will redirect back to our callback URL after payment
            return redirect($paymentUrl);
        } catch (\Exception $e) {
            \Log::error('Paymob payment creation failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create Paymob payment: ' . $e->getMessage()]);
        }
    }

    public function paymobPayment(Request $request)
    {
        $paymentUrl = session('paymob_payment_url');
        $transactionId = session('paymob_transaction_id');

        if (!$paymentUrl || !$transactionId) {
            return redirect()
                ->route('admin.wallet.index')
                ->with('error', trans('messages.Invalid payment session. Please try again.'));
        }

        return view('admin.wallet.paymob-payment', [
            'paymentUrl' => $paymentUrl,
            'transactionId' => $transactionId,
        ]);
    }

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
            $transaction = WalletTransaction::where('reference', $orderId)
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymobReturn(Request $request)
    {
        // Handle return from Paymob payment page
        $orderId = $request->get('order_id');
        $transactionId = $request->get('transaction_id');
        
        // Clear session
        session()->forget(['paymob_payment_url', 'paymob_transaction_id']);

        $transaction = null;
        
        if ($transactionId) {
            $transaction = WalletTransaction::find($transactionId);
        } elseif ($orderId) {
            $transaction = WalletTransaction::where('reference', $orderId)
                ->where('payment_method', 'paymob')
                ->first();
        }

        if ($transaction) {
            // Refresh transaction from database
            $transaction->refresh();
            
            if ($transaction->status === 'completed') {
                return redirect()
                    ->route('admin.wallet.index')
                    ->with('status', trans('messages.Payment completed successfully. Balance updated.'));
            } elseif ($transaction->status === 'pending') {
                return redirect()
                    ->route('admin.wallet.index')
                    ->with('error', trans('messages.Payment is still processing. Please wait.'));
            } else {
                return redirect()
                    ->route('admin.wallet.index')
                    ->with('error', trans('messages.Payment failed. Please try again.'));
            }
        }

        return redirect()
            ->route('admin.wallet.index')
            ->with('error', trans('messages.Invalid payment return.'));
    }
}
