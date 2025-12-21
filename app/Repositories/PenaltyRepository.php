<?php

namespace App\Repositories;

use App\Models\Penalty;
use App\Repositories\WalletRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class PenaltyRepository
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
    ) {
        //
    }
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Penalty::query()
            ->with(['user', 'trip', 'scooter'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): Penalty
    {
        return Penalty::with(['user', 'trip', 'scooter'])->findOrFail($id);
    }

    public function create(array $data): Penalty
    {
        $data['applied_at'] = $data['applied_at'] ?? Carbon::now();
        
        $penalty = Penalty::create($data);
        
        // Deduct penalty amount directly from wallet (allows negative balance)
        if ($penalty->amount > 0 && $penalty->user) {
            try {
                $this->walletRepository->deductPenalty(
                    $penalty->user,
                    $penalty->amount,
                    $penalty->trip,
                    "Penalty applied: {$penalty->title}",
                    [
                        'penalty_id' => $penalty->id,
                        'penalty_type' => $penalty->type,
                    ]
                );
                
                // Mark penalty as paid since it's automatically deducted
                $penalty->update([
                    'status' => 'paid',
                    'paid_at' => Carbon::now(),
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail penalty creation
                \Log::error("Failed to deduct wallet for penalty {$penalty->id}: " . $e->getMessage());
            }
        }
        
        return $penalty->fresh();
    }

    public function update(Penalty $penalty, array $data): Penalty
    {
        $penalty->update($data);

        return $penalty;
    }

    public function delete(Penalty $penalty): void
    {
        $penalty->delete();
    }

    public function markAsPaid(Penalty $penalty, bool $deductFromWallet = true): Penalty
    {
        // If penalty is already paid (auto-deducted on creation), just return
        if ($penalty->status === 'paid') {
            return $penalty->fresh();
        }

        $penalty->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        // Deduct from wallet if requested (allows negative balance)
        if ($deductFromWallet && $penalty->amount > 0) {
            try {
                $this->walletRepository->deductPenalty(
                    $penalty->user,
                    $penalty->amount,
                    $penalty->trip,
                    "Penalty payment: {$penalty->title}",
                    [
                        'penalty_id' => $penalty->id,
                        'penalty_type' => $penalty->type,
                    ]
                );
            } catch (\Exception $e) {
                // Log error but don't fail the payment marking
                \Log::error("Failed to deduct wallet for penalty {$penalty->id}: " . $e->getMessage());
            }
        }

        return $penalty->fresh();
    }

    public function waive(Penalty $penalty): Penalty
    {
        $penalty->update([
            'status' => 'waived',
        ]);

        return $penalty->fresh();
    }

    public function getUserPenalties(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Penalty::where('user_id', $userId)
            ->with(['trip', 'scooter'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getPendingPenalties(): \Illuminate\Database\Eloquent\Collection
    {
        return Penalty::where('status', 'pending')
            ->with(['user', 'trip'])
            ->get();
    }

    public function applyAutoPenalty(array $data): Penalty
    {
        $data['is_auto_applied'] = true;
        $data['applied_at'] = Carbon::now();
        $data['status'] = 'pending';

        return $this->create($data);
    }
}

