<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {
        //
    }

    public function index()
    {
        $users = $this->repository->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'wallet_balance' => ['nullable', 'numeric', 'min:0'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
            'loyalty_level' => ['nullable', 'in:bronze,silver,gold'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['wallet_balance'] = $data['wallet_balance'] ?? 0;
        $data['loyalty_points'] = $data['loyalty_points'] ?? 0;
        $data['loyalty_level'] = $data['loyalty_level'] ?? 'bronze';
        $data['is_active'] = $request->boolean('is_active', true);

        $this->repository->create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('User created successfully.'));
    }

    public function show(User $user)
    {
        $user->loadCount(['trips', 'penalties']);
        $user->load('roles');
        $trips = $user->trips()->with(['scooter'])->orderByDesc('start_time')->limit(10)->get();
        $penalties = $user->penalties()->with(['trip', 'scooter'])->orderByDesc('created_at')->limit(10)->get();
        $loyaltyTransactions = $user->loyaltyPointsTransactions()->with(['trip'])->orderByDesc('created_at')->limit(10)->get();
        $allRoles = \App\Models\Role::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.users.show', compact('user', 'trips', 'penalties', 'loyaltyTransactions', 'allRoles'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'wallet_balance' => ['nullable', 'numeric', 'min:0'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
            'loyalty_level' => ['nullable', 'in:bronze,silver,gold'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', $user->is_active);

        $this->repository->update($user, $data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('User updated successfully.'));
    }

    public function destroy(User $user)
    {
        $this->repository->delete($user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('User deleted successfully.'));
    }

    public function toggleActive(User $user)
    {
        if ($user->is_active) {
            $this->repository->deactivate($user);
            $message = __('User deactivated successfully.');
        } else {
            $this->repository->activate($user);
            $message = __('User activated successfully.');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', $message);
    }

    public function addWalletBalance(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->repository->addWalletBalance($user, $data['amount']);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('Wallet balance added successfully.'));
    }

    public function addLoyaltyPoints(Request $request, User $user)
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ]);

        $this->repository->addLoyaltyPoints($user, $data['points']);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('Loyalty points added successfully.'));
    }

    public function assignRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        } else {
            $user->roles()->detach();
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', __('User roles updated successfully.'));
    }
}
