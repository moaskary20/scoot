<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoyaltyPointsTransaction;
use App\Repositories\LoyaltyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    public function __construct(
        private readonly LoyaltyRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = LoyaltyPointsTransaction::query()->with(['user', 'trip']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.loyalty.index', compact('transactions'));
    }

    public function settings()
    {
        $pointsPerMinute = $this->repository->getPointsPerMinute();
        $thresholds = $this->repository->getLevelThresholds();

        return view('admin.loyalty.settings', compact('pointsPerMinute', 'thresholds'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'points_per_minute' => ['required', 'integer', 'min:0'],
            'bronze_threshold' => ['required', 'integer', 'min:0'],
            'silver_threshold' => ['required', 'integer', 'min:0', 'gte:bronze_threshold'],
            'gold_threshold' => ['required', 'integer', 'min:0', 'gte:silver_threshold'],
        ]);

        $this->repository->setPointsPerMinute($data['points_per_minute']);
        $this->repository->setLevelThresholds([
            'bronze' => $data['bronze_threshold'],
            'silver' => $data['silver_threshold'],
            'gold' => $data['gold_threshold'],
        ]);

        return redirect()
            ->route('admin.loyalty.settings')
            ->with('status', __('Loyalty settings updated successfully.'));
    }

    public function addPoints(Request $request, User $user)
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $this->repository->addPoints(
            $user,
            $data['points'],
            'adjusted',
            null,
            $data['description'] ?? __('Manual points adjustment')
        );

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('Points added successfully.'));
    }

    public function deductPoints(Request $request, User $user)
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->loyalty_points < $data['points']) {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', __('User does not have enough points.'));
        }

        $this->repository->deductPoints(
            $user,
            $data['points'],
            'adjusted',
            $data['description'] ?? __('Manual points deduction')
        );

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('Points deducted successfully.'));
    }
}
