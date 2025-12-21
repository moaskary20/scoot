<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {
        //
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $users = $this->repository->paginate(20, $search);
        $users->load('roles');

        // تحديث مستويات الولاء بناءً على نقاط الولاء
        foreach ($users as $user) {
            $calculatedLevel = $user->calculated_loyalty_level;
            if ($user->loyalty_level !== $calculatedLevel) {
                $user->update(['loyalty_level' => $calculatedLevel]);
            }
        }

        return view('admin.users.index', compact('users', 'search'));
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
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'university_id' => ['nullable', 'string', 'max:255'],
            'national_id_photo' => ['nullable', 'image', 'max:5120'], // 5MB max
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

        // Handle file upload
        if ($request->hasFile('national_id_photo')) {
            $data['national_id_photo'] = $request->file('national_id_photo')->store('national_id_photos', 'public');
        }

        $this->repository->create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', trans('messages.User created successfully.'));
    }

    public function show(User $user)
    {
        // تحديث مستوى الولاء بناءً على نقاط الولاء
        $calculatedLevel = $user->calculated_loyalty_level;
        if ($user->loyalty_level !== $calculatedLevel) {
            $user->update(['loyalty_level' => $calculatedLevel]);
            $user->refresh();
        }

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
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'university_id' => ['nullable', 'string', 'max:255'],
            'national_id_photo' => ['nullable', 'image', 'max:5120'], // 5MB max
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

        // Handle file upload
        if ($request->hasFile('national_id_photo')) {
            // Delete old photo if exists
            if ($user->national_id_photo && \Storage::disk('public')->exists($user->national_id_photo)) {
                \Storage::disk('public')->delete($user->national_id_photo);
            }
            $data['national_id_photo'] = $request->file('national_id_photo')->store('national_id_photos', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', $user->is_active);

        $this->repository->update($user, $data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', trans('messages.User updated successfully.'));
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

    public function inactive(Request $request)
    {
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $export = $request->get('export');

        $query = User::where('is_active', false)
            ->with('roles')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('university_id', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Export functionality
        if ($export === 'csv') {
            $users = $query->get();
            return $this->exportToCsv($users);
        }

        $users = $query->paginate(20)->appends(request()->query());
        $inactiveCount = User::where('is_active', false)->count();

        return view('admin.users.inactive', compact('users', 'search', 'inactiveCount', 'dateFrom', 'dateTo'));
    }

    private function exportToCsv($users)
    {
        $filename = 'inactive_users_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                trans('messages.Name'),
                trans('messages.Email'),
                trans('messages.Phone'),
                trans('messages.University ID'),
                trans('messages.Age'),
                trans('messages.Email Verified'),
                trans('messages.Phone Verified'),
                trans('messages.University ID Verified'),
                trans('messages.Registered At'),
                trans('messages.Review Notes'),
            ]);

            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->phone ?? '-',
                    $user->university_id ?? '-',
                    $user->age ?? '-',
                    $user->email_verified_at ? trans('messages.Yes') : trans('messages.No'),
                    $user->phone ? trans('messages.Yes') : trans('messages.No'),
                    $user->university_id ? trans('messages.Yes') : trans('messages.No'),
                    $user->created_at->format('Y-m-d H:i'),
                    $user->review_notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function active(Request $request)
    {
        $search = $request->get('search');
        $loyaltyLevel = $request->get('loyalty_level');
        $walletMin = $request->get('wallet_min');
        $walletMax = $request->get('wallet_max');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $query = User::where('is_active', true)
            ->with(['roles', 'trips' => function($q) {
                $q->latest()->limit(1);
            }])
            ->withCount('trips');

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('university_id', 'like', "%{$search}%");
            });
        }

        // Loyalty level filter
        if ($loyaltyLevel) {
            $query->where('loyalty_level', $loyaltyLevel);
        }

        // Wallet balance range filter
        if ($walletMin !== null) {
            $query->where('wallet_balance', '>=', $walletMin);
        }
        if ($walletMax !== null) {
            $query->where('wallet_balance', '<=', $walletMax);
        }

        // Sorting
        $allowedSorts = ['name', 'email', 'wallet_balance', 'loyalty_points', 'loyalty_level', 'created_at', 'trips_count'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'trips_count') {
                $query->orderBy('trips_count', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(20)->appends(request()->query());
        $activeCount = User::where('is_active', true)->count();

        // تحديث مستويات الولاء
        foreach ($users as $user) {
            $calculatedLevel = $user->calculated_loyalty_level;
            if ($user->loyalty_level !== $calculatedLevel) {
                $user->update(['loyalty_level' => $calculatedLevel]);
            }
        }

        return view('admin.users.active', compact('users', 'search', 'activeCount', 'loyaltyLevel', 'walletMin', 'walletMax', 'sortBy', 'sortOrder'));
    }

    public function bulkActivate(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $count = User::whereIn('id', $request->user_ids)
            ->where('is_active', false)
            ->update(['is_active' => true]);

        return redirect()
            ->back()
            ->with('status', trans('messages.:count users activated successfully.', ['count' => $count]));
    }

    public function quickPreview(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'university_id' => $user->university_id,
            'age' => $user->age,
            'email_verified' => (bool) $user->email_verified_at,
            'registered_at' => $user->created_at->format('Y-m-d H:i'),
            'review_notes' => $user->review_notes,
        ]);
    }

    public function getReviewNotes(User $user)
    {
        return response()->json([
            'review_notes' => $user->review_notes,
        ]);
    }

    public function updateReviewNotes(Request $request, User $user)
    {
        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user->update(['review_notes' => $data['review_notes'] ?? null]);

        return redirect()
            ->back()
            ->with('status', trans('messages.Review notes updated successfully.'));
    }
}
