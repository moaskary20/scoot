<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function __construct(
        private readonly CouponRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = Coupon::query();

        // Filters
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        if ($request->filled('applicable_to')) {
            $query->where('applicable_to', $request->applicable_to);
        }

        $coupons = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0', 'required_if:discount_type,percentage'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'user_usage_limit' => ['nullable', 'integer', 'min:1'],
            'applicable_to' => ['required', 'in:trips,subscriptions,all'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::random(8));
        } else {
            $data['code'] = strtoupper($data['code']);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['usage_count'] = 0;
        $data['min_amount'] = $data['min_amount'] ?? 0;
        $data['user_usage_limit'] = $data['user_usage_limit'] ?? 1;

        $coupon = $this->repository->create($data);

        return redirect()
            ->route('admin.coupons.show', $coupon)
            ->with('status', __('Coupon created successfully.'));
    }

    public function show(Coupon $coupon)
    {
        $coupon->loadCount('trips');
        $recentTrips = $coupon->trips()->with(['user', 'scooter'])->orderByDesc('start_time')->limit(10)->get();

        return view('admin.coupons.show', compact('coupon', 'recentTrips'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code,' . $coupon->id],
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0', 'required_if:discount_type,percentage'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'user_usage_limit' => ['nullable', 'integer', 'min:1'],
            'applicable_to' => ['required', 'in:trips,subscriptions,all'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', $coupon->is_active);

        $this->repository->update($coupon, $data);

        return redirect()
            ->route('admin.coupons.show', $coupon)
            ->with('status', __('Coupon updated successfully.'));
    }

    public function destroy(Coupon $coupon)
    {
        $this->repository->delete($coupon);

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', __('Coupon deleted successfully.'));
    }
}
