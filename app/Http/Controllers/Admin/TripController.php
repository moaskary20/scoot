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

        // Search by user name, phone, or university_id
        if ($request->filled('user_search')) {
            $search = $request->user_search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('university_id', 'like', "%{$search}%");
            });
        }

        $trips = $query->orderByDesc('start_time')->paginate(20);
        
        // Load wallet transactions for payment status calculation
        $trips->load('walletTransactions');

        return view('admin.trips.index', compact('trips'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $scooters = Scooter::where('is_active', true)
            ->where('status', 'available')
            ->orderBy('code')
            ->get();
        $coupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($query) {
                $query->where('applicable_to', 'trips')
                      ->orWhere('applicable_to', 'all');
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->orderBy('code')
            ->get();
        $geoZones = \App\Models\GeoZone::where('type', 'allowed')
            ->where('is_active', true)
            ->where('allow_trip_start', true)
            ->orderBy('name')
            ->get();

        return view('admin.trips.create', compact('users', 'scooters', 'coupons', 'geoZones'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'scooter_id' => ['required', 'exists:scooters,id'],
            'coupon_id' => ['nullable', 'exists:coupons,id'],
            'geo_zone_id' => ['nullable', 'exists:geo_zones,id'],
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

        // إذا تم اختيار منطقة جغرافية، استخدم إحداثيات المركز
        if (isset($data['geo_zone_id']) && $data['geo_zone_id']) {
            $geoZone = \App\Models\GeoZone::find($data['geo_zone_id']);
            if ($geoZone && $geoZone->center_latitude && $geoZone->center_longitude) {
                $data['start_latitude'] = $geoZone->center_latitude;
                $data['start_longitude'] = $geoZone->center_longitude;
            }
        }

        // إزالة geo_zone_id من البيانات قبل الحفظ (لا يوجد عمود في جدول trips)
        unset($data['geo_zone_id']);

        $trip = $this->repository->create($data);

        return redirect()
            ->route('admin.trips.show', $trip)
            ->with('status', __('Trip started successfully.'));
    }

    public function show(Trip $trip)
    {
        $trip->load(['user', 'scooter', 'coupon', 'penalty', 'walletTransactions']);

        // Get geo zone for this trip based on start location
        $geoZone = null;
        if ($trip->start_latitude && $trip->start_longitude) {
            $geoZone = \App\Models\GeoZone::where('type', 'allowed')
                ->where('is_active', true)
                ->get()
                ->first(function ($zone) use ($trip) {
                    return $this->isPointInPolygon(
                        $trip->start_latitude,
                        $trip->start_longitude,
                        $zone->polygon
                    );
                });
        }

        return view('admin.trips.show', compact('trip', 'geoZone'));
    }

    /**
     * Check if a point is inside a polygon (Ray casting algorithm)
     */
    private function isPointInPolygon(float $latitude, float $longitude, array $polygon): bool
    {
        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lat'] ?? $polygon[$i][0] ?? 0;
            $yi = $polygon[$i]['lng'] ?? $polygon[$i][1] ?? 0;
            $xj = $polygon[$j]['lat'] ?? $polygon[$j][0] ?? 0;
            $yj = $polygon[$j]['lng'] ?? $polygon[$j][1] ?? 0;

            $intersect = (($yi > $longitude) != ($yj > $longitude)) &&
                ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
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

        // Calculate cost based on duration and geo zone pricing
        $endTime = Carbon::now();
        $startTime = Carbon::parse($trip->start_time);
        $durationMinutes = $startTime->diffInMinutes($endTime);
        
        // Get geo zone for pricing
        $geoZone = null;
        if ($trip->start_latitude && $trip->start_longitude) {
            $geoZone = \App\Models\GeoZone::where('type', 'allowed')
                ->where('is_active', true)
                ->get()
                ->first(function ($zone) use ($trip) {
                    return $this->isPointInPolygon(
                        $trip->start_latitude,
                        $trip->start_longitude,
                        $zone->polygon
                    );
                });
        }
        
        // Calculate cost
        $cost = 0;
        if ($geoZone && $geoZone->price_per_minute) {
            $tripStartFee = $geoZone->trip_start_fee ?? 0;
            $pricePerMinute = $geoZone->price_per_minute ?? 0;
            $cost = $tripStartFee + ($durationMinutes * $pricePerMinute);
        } else {
            // Use existing cost or calculate from base_cost, discount, and penalty
            $cost = $trip->base_cost - $trip->discount_amount + $trip->penalty_amount;
        }
        
        // Ensure cost is not negative
        $cost = max(0, (float) $cost);

        $data = [
            'end_latitude' => $request->input('end_latitude'),
            'end_longitude' => $request->input('end_longitude'),
            'cost' => $cost,
        ];

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

    public function settings()
    {
        $settings = $this->getTripSettings();

        return view('admin.trips.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'max_trip_duration_minutes' => ['required', 'integer', 'min:1'],
            'max_trip_cost' => ['required', 'numeric', 'min:0'],
            'max_trips_per_day' => ['required', 'integer', 'min:1'],
            'max_coupon_uses_per_month' => ['required', 'integer', 'min:0'],
            'max_penalties_before_account_suspension' => ['required', 'integer', 'min:0'],
            'max_trip_distance_km' => ['nullable', 'numeric', 'min:0'],
            'min_trip_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'min_trip_cost' => ['nullable', 'numeric', 'min:0'],
            'max_discount_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'enable_trip_duration_warning' => ['sometimes', 'boolean'],
            'trip_duration_warning_threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
            'enable_cost_warning' => ['sometimes', 'boolean'],
            'cost_warning_threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            $this->setTripSetting($key, $value);
        }

        return redirect()
            ->route('admin.trips.settings')
            ->with('status', trans('messages.Trip settings updated successfully.'));
    }

    private function getTripSettings(): array
    {
        $keys = [
            'max_trip_duration_minutes',
            'max_trip_cost',
            'max_trips_per_day',
            'max_coupon_uses_per_month',
            'max_penalties_before_account_suspension',
            'max_trip_distance_km',
            'min_trip_duration_minutes',
            'min_trip_cost',
            'max_discount_percentage',
            'max_penalty_amount',
            'enable_trip_duration_warning',
            'trip_duration_warning_threshold',
            'enable_cost_warning',
            'cost_warning_threshold',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $setting = \DB::table('trip_settings')->where('key', $key)->first();
            $settings[$key] = $setting ? $setting->value : null;
        }

        return $settings;
    }

    private function setTripSetting(string $key, $value): void
    {
        \DB::table('trip_settings')
            ->updateOrInsert(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'updated_at' => now(),
                ]
            );
    }
}
