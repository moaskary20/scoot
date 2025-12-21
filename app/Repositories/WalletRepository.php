<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Trip;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WalletRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return WalletTransaction::query()
            ->with(['user', 'trip'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): WalletTransaction
    {
        return WalletTransaction::with(['user', 'trip'])->findOrFail($id);
    }

    public function getUserTransactions(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return WalletTransaction::where('user_id', $userId)
            ->with(['trip'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Top up wallet (add balance)
     */
    public function topUp(
        User $user,
        float $amount,
        ?string $paymentMethod = null,
        ?string $reference = null,
        ?string $description = null,
        ?array $metadata = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $paymentMethod, $reference, $description, $metadata) {
            $balanceBefore = (float) $user->wallet_balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update user balance
            $user->update(['wallet_balance' => $balanceAfter]);

            // Create transaction
            return WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'top_up',
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'description' => $description ?? "Wallet top-up of {$amount} EGP",
                'metadata' => $metadata,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Deduct from wallet (for trip payment, penalty, etc.)
     */
    public function deduct(
        User $user,
        float $amount,
        string $type = 'trip_payment',
        ?Trip $trip = null,
        ?string $description = null,
        ?array $metadata = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $type, $trip, $description, $metadata) {
            $balanceBefore = (float) $user->wallet_balance;

            // Check if user has sufficient balance
            if ($balanceBefore < $amount) {
                throw new \Exception("Insufficient wallet balance. Required: {$amount}, Available: {$balanceBefore}");
            }

            $balanceAfter = $balanceBefore - $amount;

            // Update user balance
            $user->update(['wallet_balance' => $balanceAfter]);

            // Create transaction
            return WalletTransaction::create([
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'type' => $type,
                'transaction_type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'description' => $description ?? "Payment for {$type}",
                'metadata' => $metadata,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Refund to wallet
     */
    public function refund(
        User $user,
        float $amount,
        ?Trip $trip = null,
        ?string $description = null,
        ?string $reference = null,
        ?array $metadata = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $trip, $description, $reference, $metadata) {
            $balanceBefore = (float) $user->wallet_balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update user balance
            $user->update(['wallet_balance' => $balanceAfter]);

            // Create transaction
            return WalletTransaction::create([
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'type' => 'refund',
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'status' => 'completed',
                'description' => $description ?? "Refund of {$amount} EGP",
                'metadata' => $metadata,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Manual adjustment (admin adjustment)
     */
    public function adjust(
        User $user,
        float $amount,
        string $transactionType, // 'credit' or 'debit'
        ?string $description = null,
        ?string $notes = null,
        ?array $metadata = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $transactionType, $description, $notes, $metadata) {
            $balanceBefore = (float) $user->wallet_balance;

            if ($transactionType === 'credit') {
                $balanceAfter = $balanceBefore + $amount;
            } else {
                // Check if user has sufficient balance for debit
                if ($balanceBefore < $amount) {
                    throw new \Exception("Insufficient wallet balance. Required: {$amount}, Available: {$balanceBefore}");
                }
                $balanceAfter = $balanceBefore - $amount;
            }

            // Update user balance
            $user->update(['wallet_balance' => $balanceAfter]);

            // Create transaction
            return WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'adjustment',
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'description' => $description ?? "Manual adjustment: {$transactionType} {$amount} EGP",
                'notes' => $notes,
                'metadata' => $metadata,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Calculate current wallet balance from transactions
     */
    public function calculateBalanceFromTransactions(int $userId): float
    {
        $lastTransaction = WalletTransaction::where('user_id', $userId)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($lastTransaction) {
            return (float) $lastTransaction->balance_after;
        }

        // If no transactions, return 0
        return 0.0;
    }

    /**
     * Sync user wallet balance from transactions
     */
    public function syncBalanceFromTransactions(User $user): void
    {
        $calculatedBalance = $this->calculateBalanceFromTransactions($user->id);
        $user->update(['wallet_balance' => $calculatedBalance]);
    }

    /**
     * Get wallet statistics for a user
     */
    public function getUserStatistics(int $userId): array
    {
        $transactions = WalletTransaction::where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        return [
            'total_top_ups' => $transactions->where('type', 'top_up')->sum('amount'),
            'total_spent' => $transactions->where('transaction_type', 'debit')->sum('amount'),
            'total_refunds' => $transactions->where('type', 'refund')->sum('amount'),
            'transaction_count' => $transactions->count(),
            'trip_payments' => $transactions->where('type', 'trip_payment')->sum('amount'),
            'penalties' => $transactions->where('type', 'penalty')->sum('amount'),
        ];
    }
}

