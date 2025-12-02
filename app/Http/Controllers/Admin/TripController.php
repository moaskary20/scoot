<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use App\Models\Scooter;
use App\Repositories\TripRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TripController extends Controller
{
    public function __construct(
        private readonly TripRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = Trip::query()->with(['user', 'scooter']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('scooter_id')) {
            $query->where('scooter_id', $request->scooter_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_time', '<=', $request->date_to);
        }

        if ($request->filled('zone_exit')) {
            $query->where('zone_exit_detected', $request->zone_exit === '1');
        }

        $trips = $query->orderByDesc('start_time')->paginate(20);

        return view('admin.trips.index', compact('trips'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $scooters = Scooter::where('is_active', true)
            ->where('status', 'available')
            ->orderBy('code')
            ->get();

        return view('admin.trips.create', compact('users', 'scooters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'scooter_id' => ['required', 'exists:scooters,id'],
            'start_latitude' => ['nullable', 'numeric'],
            'start_longitude' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $scooter = Scooter::findOrFail($data['scooter_id']);
        
        // تحديث حالة السكوتر إلى "rented"
        $scooter->update(['status' => 'rented']);

        $data['start_time'] = Carbon::now();
        $data['status'] = 'active';
        $data['cost'] = 0;
        $data['base_cost'] = 0;

        $trip = $this->repository->create($data);

        return redirect()
            ->route('admin.trips.show', $trip)
            ->with('status', __('Trip started successfully.'));
    }

    public function show(Trip $trip)
    {
        $trip->load(['user', 'scooter', 'coupon', 'penalty']);

        return view('admin.trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $scooters = Scooter::where('is_active', true)->orderBy('code')->get();

        return view('admin.trips.edit', compact('trip', 'users', 'scooters'));
    }

    public function update(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'scooter_id' => ['required', 'exists:scooters,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'base_cost' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'start_latitude' => ['nullable', 'numeric'],
            'start_longitude' => ['nullable', 'numeric'],
            'end_latitude' => ['nullable', 'numeric'],
            'end_longitude' => ['nullable', 'numeric'],
            'status' => ['required', 'in:active,completed,cancelled'],
            'zone_exit_detected' => ['sometimes', 'boolean'],
            'zone_exit_details' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->repository->update($trip, $data);

        return redirect()
            ->route('admin.trips.show', $trip)
            ->with('status', __('Trip updated successfully.'));
    }

    public function destroy(Trip $trip)
    {
        $this->repository->delete($trip);

        return redirect()
            ->route('admin.trips.index')
            ->with('status', __('Trip deleted successfully.'));
    }

    public function complete(Request $request, Trip $trip)
    {
        if ($trip->status !== 'active') {
            return redirect()
                ->route('admin.trips.show', $trip)
                ->with('error', __('Trip is not active.'));
        }

        $data = $request->validate([
            'end_latitude' => ['nullable', 'numeric'],
            'end_longitude' => ['nullable', 'numeric'],
            'cost' => ['required', 'numeric', 'min:0'],
        ]);

        $this->repository->completeTrip($trip, $data);

        // تحديث حالة السكوتر إلى "available"
        $trip->scooter->update(['status' => 'available']);

        return redirect()
            ->route('admin.trips.show', $trip)
            ->with('status', __('Trip completed successfully.'));
    }

    public function cancel(Trip $trip)
    {
        if ($trip->status !== 'active') {
            return redirect()
                ->route('admin.trips.show', $trip)
                ->with('error', __('Trip is not active.'));
        }

        $trip->update([
            'status' => 'cancelled',
            'end_time' => Carbon::now(),
        ]);

        // تحديث حالة السكوتر إلى "available"
        $trip->scooter->update(['status' => 'available']);

        return redirect()
            ->route('admin.trips.show', $trip)
            ->with('status', __('Trip cancelled successfully.'));
    }
}
