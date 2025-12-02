<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Repositories\WalletRepository;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletRepository $repository,
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
            'description' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $user = User::findOrFail($data['user_id']);
            
            $transaction = $this->repository->adjust(
                $user,
                $data['amount'],
                $data['transaction_type'],
                $data['description'] ?? null,
                $data['notes'] ?? null
            );

            // Update transaction type if needed
            if ($data['type'] !== 'adjustment') {
                $transaction->update(['type' => $data['type']]);
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
}
