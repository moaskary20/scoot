<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Coupon;
use App\Repositories\SubscriptionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = Subscription::query()->with(['user', 'coupon']);

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

        if ($request->filled('auto_renew')) {
            $query->where('auto_renew', $request->auto_renew === '1');
        }

        $subscriptions = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $coupons = Coupon::where('is_active', true)
            ->where('applicable_to', '!=', 'trips')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('name')
            ->get();

        return view('admin.subscriptions.create', compact('users', 'coupons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:minutes,unlimited'],
            'minutes_included' => ['nullable', 'integer', 'min:1', 'required_if:type,minutes'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'in:daily,weekly,monthly,yearly'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after:starts_at'],
            'auto_renew' => ['sometimes', 'boolean'],
            'coupon_id' => ['nullable', 'exists:coupons,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['status'] = 'active';
        $data['minutes_used'] = 0;
        $data['trips_count'] = 0;
        $data['auto_renew'] = $request->boolean('auto_renew', false);
        $data['starts_at'] = Carbon::parse($data['starts_at']);
        $data['expires_at'] = Carbon::parse($data['expires_at']);

        if ($data['type'] === 'unlimited') {
            $data['minutes_included'] = null;
        }

        $subscription = $this->repository->create($data);

        return redirect()
            ->route('admin.subscriptions.show', $subscription)
            ->with('status', __('Subscription created successfully.'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'coupon']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $coupons = Coupon::where('is_active', true)
            ->where('applicable_to', '!=', 'trips')
            ->orderBy('name')
            ->get();

        return view('admin.subscriptions.edit', compact('subscription', 'users', 'coupons'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:minutes,unlimited'],
            'minutes_included' => ['nullable', 'integer', 'min:1', 'required_if:type,minutes'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'in:daily,weekly,monthly,yearly'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['required', 'in:active,expired,cancelled,suspended'],
            'auto_renew' => ['sometimes', 'boolean'],
            'coupon_id' => ['nullable', 'exists:coupons,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['auto_renew'] = $request->boolean('auto_renew', $subscription->auto_renew);
        $data['starts_at'] = Carbon::parse($data['starts_at']);
        $data['expires_at'] = Carbon::parse($data['expires_at']);

        if ($data['type'] === 'unlimited') {
            $data['minutes_included'] = null;
        }

        $this->repository->update($subscription, $data);

        return redirect()
            ->route('admin.subscriptions.show', $subscription)
            ->with('status', __('Subscription updated successfully.'));
    }

    public function destroy(Subscription $subscription)
    {
        $this->repository->delete($subscription);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('status', __('Subscription deleted successfully.'));
    }

    public function renew(Subscription $subscription)
    {
        $this->repository->renew($subscription);

        return redirect()
            ->route('admin.subscriptions.show', $subscription)
            ->with('status', __('Subscription renewed successfully.'));
    }

    public function cancel(Subscription $subscription)
    {
        $this->repository->cancel($subscription);

        return redirect()
            ->route('admin.subscriptions.show', $subscription)
            ->with('status', __('Subscription cancelled successfully.'));
    }

    public function suspend(Subscription $subscription)
    {
        $this->repository->suspend($subscription);

        return redirect()
            ->route('admin.subscriptions.show', $subscription)
            ->with('status', __('Subscription suspended successfully.'));
    }

    public function activate(Subscription $subscription)
    {
        $this->repository->activate($subscription);

        return redirect()
            ->route('admin.subscriptions.show', $subscription)
            ->with('status', __('Subscription activated successfully.'));
    }
}
