<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\User;
use App\Models\Trip;
use App\Models\Scooter;
use App\Repositories\PenaltyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PenaltyController extends Controller
{
    public function __construct(
        private readonly PenaltyRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = Penalty::query()->with(['user', 'trip', 'scooter']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('auto_applied')) {
            $query->where('is_auto_applied', $request->auto_applied === '1');
        }

        $penalties = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.penalties.index', compact('penalties'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $trips = Trip::where('status', 'completed')
            ->orWhere('status', 'active')
            ->orderByDesc('start_time')
            ->limit(100)
            ->get();
        $scooters = Scooter::where('is_active', true)->orderBy('code')->get();

        return view('admin.penalties.create', compact('users', 'trips', 'scooters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'trip_id' => ['nullable', 'exists:trips,id'],
            'scooter_id' => ['nullable', 'exists:scooters,id'],
            'type' => ['required', 'in:zone_exit,forbidden_parking,unlocked_scooter,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'evidence_data' => ['nullable', 'string'],
        ]);

        $data['is_auto_applied'] = false;
        $data['status'] = 'pending';

        if (!empty($data['evidence_data'])) {
            $data['evidence_data'] = json_decode($data['evidence_data'], true);
        }

        $penalty = $this->repository->create($data);

        // ربط الغرامة بالرحلة إذا كانت موجودة
        if ($penalty->trip_id) {
            $trip = Trip::find($penalty->trip_id);
            if ($trip) {
                $trip->update([
                    'penalty_id' => $penalty->id,
                    'penalty_amount' => $penalty->amount,
                    'cost' => $trip->base_cost + $penalty->amount - $trip->discount_amount,
                ]);
            }
        }

        return redirect()
            ->route('admin.penalties.show', $penalty)
            ->with('status', __('Penalty created successfully.'));
    }

    public function show(Penalty $penalty)
    {
        $penalty->load(['user', 'trip', 'scooter']);

        return view('admin.penalties.show', compact('penalty'));
    }

    public function edit(Penalty $penalty)
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $trips = Trip::orderByDesc('start_time')->limit(100)->get();
        $scooters = Scooter::where('is_active', true)->orderBy('code')->get();

        return view('admin.penalties.edit', compact('penalty', 'users', 'trips', 'scooters'));
    }

    public function update(Request $request, Penalty $penalty)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'trip_id' => ['nullable', 'exists:trips,id'],
            'scooter_id' => ['nullable', 'exists:scooters,id'],
            'type' => ['required', 'in:zone_exit,forbidden_parking,unlocked_scooter,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid,waived,cancelled'],
            'evidence_data' => ['nullable', 'string'],
        ]);

        if (!empty($data['evidence_data'])) {
            $data['evidence_data'] = json_decode($data['evidence_data'], true);
        }

        $this->repository->update($penalty, $data);

        // تحديث الرحلة المرتبطة
        if ($penalty->trip_id) {
            $trip = Trip::find($penalty->trip_id);
            if ($trip) {
                $trip->update([
                    'penalty_amount' => $penalty->amount,
                    'cost' => $trip->base_cost + $penalty->amount - $trip->discount_amount,
                ]);
            }
        }

        return redirect()
            ->route('admin.penalties.show', $penalty)
            ->with('status', __('Penalty updated successfully.'));
    }

    public function destroy(Penalty $penalty)
    {
        $this->repository->delete($penalty);

        return redirect()
            ->route('admin.penalties.index')
            ->with('status', __('Penalty deleted successfully.'));
    }

    public function markAsPaid(Penalty $penalty)
    {
        $this->repository->markAsPaid($penalty);

        return redirect()
            ->route('admin.penalties.show', $penalty)
            ->with('status', __('Penalty marked as paid.'));
    }

    public function waive(Penalty $penalty)
    {
        $this->repository->waive($penalty);

        return redirect()
            ->route('admin.penalties.show', $penalty)
            ->with('status', __('Penalty waived.'));
    }
}
