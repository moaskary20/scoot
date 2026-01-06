<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.User Details') }}: {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.User details, trips, and penalties') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.Edit') }}
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="text-sm text-gray-600 hover:text-secondary">
                    {{ trans('messages.Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- User Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Wallet Balance') }}</div>
                    <div class="text-2xl font-bold text-emerald-600">
                        @php
                            $balance = $user->calculated_wallet_balance;
                            $isNegative = $balance < 0;
                        @endphp
                        <span class="{{ $isNegative ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ number_format($balance, 2) }} {{ trans('messages.EGP') }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.users.wallet.transactions', $user) }}" class="text-xs text-primary hover:underline">
                            {{ trans('messages.View Transactions') }}
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Loyalty Points') }}</div>
                    <div class="text-2xl font-bold text-secondary">
                        {{ number_format($user->loyalty_points) }}
                    </div>
                    @php
                        $levelColors = [
                            'bronze' => 'bg-amber-100 text-amber-700',
                            'silver' => 'bg-gray-100 text-gray-700',
                            'gold' => 'bg-yellow-100 text-yellow-700',
                        ];
                    @endphp
                    <div class="mt-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $levelColors[$user->loyalty_level] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ trans('messages.' . ucfirst($user->loyalty_level)) }}
                        </span>
                    </div>
                    <div class="mt-3 space-y-2">
                        <form action="{{ route('admin.users.loyalty.add-points', $user) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="number" min="1" name="points" placeholder="Points"
                                   class="flex-1 text-xs rounded border-gray-300" required>
                            <input type="text" name="description" placeholder="Description (optional)"
                                   class="flex-1 text-xs rounded border-gray-300">
                            <button type="submit" class="px-2 py-1 text-xs bg-primary text-secondary rounded hover:bg-yellow-400">
                                {{ trans('messages.Add') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.users.loyalty.deduct-points', $user) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="number" min="1" name="points" placeholder="{{ trans('messages.Points') }}"
                                   class="flex-1 text-xs rounded border-gray-300" required>
                            <input type="text" name="description" placeholder="{{ trans('messages.Description') }} ({{ trans('messages.optional') }})"
                                   class="flex-1 text-xs rounded border-gray-300">
                            <button type="submit" class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600">
                                {{ trans('messages.Deduct') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Trips') }}</div>
                    <div class="text-2xl font-bold text-secondary">
                        {{ $user->trips_count }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ trans('messages.trip') }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Penalties') }}</div>
                    <div class="text-2xl font-bold text-red-600">
                        {{ $user->penalties_count }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        @php
                            $pendingPenalties = $user->penalties()->where('status', 'pending')->count();
                        @endphp
                        {{ $pendingPenalties }} {{ trans('messages.Pending') }}
                    </div>
                </div>
            </div>

            <!-- User Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.User Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Name') }}</div>
                        <div class="font-semibold text-secondary">{{ $user->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Email') }}</div>
                        <div class="text-gray-700">{{ $user->email }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Phone') }}</div>
                        <div class="text-gray-700">{{ $user->phone ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Age') }}</div>
                        <div class="text-gray-700">{{ $user->age ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.University ID') }}</div>
                        <div class="text-gray-700">{{ $user->university_id ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Photo of national Id') }} ({{ trans('messages.Front') }})</div>
                        @if($user->national_id_front_photo)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $user->national_id_front_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $user->national_id_front_photo) }}" alt="National ID Front Photo" 
                                         class="h-32 w-auto rounded-lg border border-gray-300 hover:opacity-80 cursor-pointer">
                                </a>
                            </div>
                        @elseif($user->national_id_photo)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $user->national_id_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $user->national_id_photo) }}" alt="National ID Photo" 
                                         class="h-32 w-auto rounded-lg border border-gray-300 hover:opacity-80 cursor-pointer">
                                </a>
                                <p class="text-xs text-gray-400 mt-1">{{ trans('messages.Old format') }}</p>
                            </div>
                        @else
                            <div class="text-gray-500">-</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Photo of national Id') }} ({{ trans('messages.Back') }})</div>
                        @if($user->national_id_back_photo)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $user->national_id_back_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $user->national_id_back_photo) }}" alt="National ID Back Photo" 
                                         class="h-32 w-auto rounded-lg border border-gray-300 hover:opacity-80 cursor-pointer">
                                </a>
                            </div>
                        @else
                            <div class="text-gray-500">-</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                        <div>
                            @if($user->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                    {{ trans('messages.Active') }}
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-700">
                                    {{ trans('messages.Inactive') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Registered At') }}</div>
                        <div class="text-gray-700">{{ $user->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Last Updated') }}</div>
                        <div class="text-gray-700">{{ $user->updated_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Wallet Management -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Wallet Management') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Top Up -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-gray-700 mb-3">{{ trans('messages.Top Up Wallet') }}</h4>
                        <form action="{{ route('admin.users.wallet.top-up', $user) }}" method="POST">
                            @csrf
                            <div class="space-y-2">
                                <input type="number" step="0.01" min="0.01" name="amount" placeholder="{{ trans('messages.Amount') }} ({{ trans('messages.EGP') }})" required
                                       class="w-full text-sm rounded-lg border-gray-300">
                                <input type="text" name="payment_method" placeholder="{{ trans('messages.Payment Method') }} ({{ trans('messages.optional') }})"
                                       class="w-full text-sm rounded-lg border-gray-300">
                                <input type="text" name="reference" placeholder="{{ trans('messages.Reference') }} ({{ trans('messages.optional') }})"
                                       class="w-full text-sm rounded-lg border-gray-300">
                                <textarea name="description" rows="2" placeholder="{{ trans('messages.Description') }} ({{ trans('messages.optional') }})"
                                          class="w-full text-sm rounded-lg border-gray-300"></textarea>
                                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                                    {{ trans('messages.Top Up') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Refund -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-gray-700 mb-3">{{ trans('messages.Refund') }}</h4>
                        <form action="{{ route('admin.users.wallet.refund', $user) }}" method="POST">
                            @csrf
                            <div class="space-y-2">
                                <input type="number" step="0.01" min="0.01" name="amount" placeholder="{{ trans('messages.Amount') }} ({{ trans('messages.EGP') }})" required
                                       class="w-full text-sm rounded-lg border-gray-300">
                                <select name="trip_id" class="w-full text-sm rounded-lg border-gray-300">
                                    <option value="">{{ trans('messages.Select Trip') }} ({{ trans('messages.optional') }})</option>
                                    @foreach($user->trips()->latest()->limit(10)->get() as $trip)
                                        <option value="{{ $trip->id }}">{{ trans('messages.Trip') }} #{{ $trip->id }} - {{ number_format($trip->cost, 2) }} {{ trans('messages.EGP') }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="reference" placeholder="{{ trans('messages.Reference') }} ({{ trans('messages.optional') }})"
                                       class="w-full text-sm rounded-lg border-gray-300">
                                <textarea name="description" rows="2" placeholder="{{ trans('messages.Description') }} ({{ trans('messages.optional') }})"
                                          class="w-full text-sm rounded-lg border-gray-300"></textarea>
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                    {{ trans('messages.Refund') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Adjust -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-gray-700 mb-3">{{ trans('messages.Manual Adjustment') }}</h4>
                        <form action="{{ route('admin.users.wallet.adjust', $user) }}" method="POST">
                            @csrf
                            <div class="space-y-2">
                                <input type="number" step="0.01" min="0.01" name="amount" placeholder="{{ trans('messages.Amount') }} ({{ trans('messages.EGP') }})" required
                                       class="w-full text-sm rounded-lg border-gray-300">
                                <select name="transaction_type" required class="w-full text-sm rounded-lg border-gray-300">
                                    <option value="credit">{{ trans('messages.Add (Credit)') }}</option>
                                    <option value="debit">{{ trans('messages.Deduct (Debit)') }}</option>
                                </select>
                                <textarea name="description" rows="2" placeholder="{{ trans('messages.Description') }} ({{ trans('messages.optional') }})"
                                          class="w-full text-sm rounded-lg border-gray-300"></textarea>
                                <textarea name="notes" rows="2" placeholder="{{ trans('messages.Notes') }} ({{ trans('messages.optional') }})"
                                          class="w-full text-sm rounded-lg border-gray-300"></textarea>
                                <button type="submit" class="w-full px-4 py-2 bg-secondary text-primary text-sm font-medium rounded-lg hover:bg-secondary/90">
                                    {{ trans('messages.Adjust') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Roles Management -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Roles & Permissions') }}</h3>
                
                <form action="{{ route('admin.users.assign-roles', $user) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        @foreach($allRoles as $role)
                            <label class="flex items-start">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                       class="mt-1 rounded border-gray-300"
                                       {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                <div class="ml-2 flex-1">
                                    <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                    @if($role->description)
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $role->description }}</div>
                                    @endif
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ trans('messages.Permissions') }}: {{ $role->permissions->count() }}
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg hover:bg-yellow-400">
                            {{ trans('messages.Update Roles') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Trips Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-secondary">{{ trans('messages.User Trips') }}</h3>
                    <a href="{{ route('admin.trips.index', ['user_id' => $user->id]) }}"
                       class="text-xs text-primary hover:text-yellow-500">
                        {{ trans('messages.View All') }}
                    </a>
                </div>
                @if($trips->count() > 0)
                    <div class="overflow-x-auto" dir="rtl">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.ID') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Scooter') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Start Time') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Duration') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Cost') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Status') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($trips as $trip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-500">#{{ $trip->id }}</td>
                                    <td class="px-3 py-2 font-semibold text-secondary">{{ $trip->scooter->code }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $trip->start_time->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-gray-600">
                                        {{ $trip->duration_minutes ? $trip->duration_minutes . ' ' . trans('messages.min') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 font-semibold text-emerald-600">
                                        {{ number_format($trip->cost, 2) }} {{ trans('messages.EGP') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColors = [
                                                'active' => 'bg-blue-50 text-blue-700',
                                                'completed' => 'bg-emerald-50 text-emerald-700',
                                                'cancelled' => 'bg-red-50 text-red-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $statusColors[$trip->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ trans('messages.' . ucfirst($trip->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-left">
                                        <a href="{{ route('admin.trips.show', $trip) }}"
                                           class="text-primary hover:text-yellow-500">
                                            {{ trans('messages.View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-sm text-gray-500">
                        {{ trans('messages.No trips yet') }}
                    </div>
                @endif
            </div>

            <!-- Penalties Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-secondary">{{ trans('messages.User Penalties') }}</h3>
                    <a href="{{ route('admin.penalties.index', ['user_id' => $user->id]) }}"
                       class="text-xs text-primary hover:text-yellow-500">
                        {{ trans('messages.View All') }}
                    </a>
                </div>
                @if($penalties->count() > 0)
                    <div class="overflow-x-auto" dir="rtl">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.ID') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Type') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Title') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Amount') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Status') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Applied At') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($penalties as $penalty)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-500">#{{ $penalty->id }}</td>
                                    <td class="px-3 py-2">
                                        @php
                                            $typeLabels = [
                                                'zone_exit' => trans('messages.Zone Exit'),
                                                'forbidden_parking' => trans('messages.Forbidden Parking'),
                                                'unlocked_scooter' => trans('messages.Unlocked Scooter'),
                                                'other' => trans('messages.Other'),
                                            ];
                                        @endphp
                                        <span class="text-gray-600">{{ $typeLabels[$penalty->type] ?? ucfirst($penalty->type) }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700">{{ $penalty->title }}</td>
                                    <td class="px-3 py-2 font-semibold text-red-600">
                                        {{ number_format($penalty->amount, 2) }} {{ trans('messages.EGP') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-amber-50 text-amber-700',
                                                'paid' => 'bg-emerald-50 text-emerald-700',
                                                'waived' => 'bg-blue-50 text-blue-700',
                                                'cancelled' => 'bg-red-50 text-red-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $statusColors[$penalty->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ trans('messages.' . ucfirst($penalty->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">
                                        {{ $penalty->applied_at?->format('Y-m-d H:i') ?: '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-left">
                                        <a href="{{ route('admin.penalties.show', $penalty) }}"
                                           class="text-primary hover:text-yellow-500">
                                            {{ trans('messages.View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-sm text-gray-500">
                        {{ trans('messages.No penalties yet') }}
                    </div>
                @endif
            </div>

            <!-- Loyalty Points Transactions Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-secondary">{{ trans('messages.Loyalty Points Transactions') }}</h3>
                    <a href="{{ route('admin.loyalty.index', ['user_id' => $user->id]) }}"
                       class="text-xs text-primary hover:text-yellow-500">
                        {{ trans('messages.View All') }}
                    </a>
                </div>
                @if($loyaltyTransactions->count() > 0)
                    <div class="overflow-x-auto" dir="rtl">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.ID') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Type') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Points') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Balance After') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Description') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Date') }}</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($loyaltyTransactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-500">#{{ $transaction->id }}</td>
                                    <td class="px-3 py-2">
                                        @php
                                            $typeColors = [
                                                'earned' => 'bg-emerald-50 text-emerald-700',
                                                'redeemed' => 'bg-red-50 text-red-700',
                                                'adjusted' => 'bg-blue-50 text-blue-700',
                                                'expired' => 'bg-gray-100 text-gray-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $typeColors[$transaction->type] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ trans('messages.' . ucfirst($transaction->type)) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="font-semibold {{ $transaction->points > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $transaction->points > 0 ? '+' : '' }}{{ $transaction->points }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">{{ number_format($transaction->balance_after) }}</td>
                                    <td class="px-3 py-2 text-gray-600">
                                        {{ $transaction->description ?: '-' }}
                                        @if($transaction->trip)
                                            <div class="text-gray-500 mt-1">{{ trans('messages.Trip') }} #{{ $transaction->trip->id }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-sm text-gray-500">
                        {{ trans('messages.No loyalty transactions yet') }}
                    </div>
                @endif
            </div>

            <!-- Subscriptions Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-secondary">{{ trans('messages.User Subscriptions') }}</h3>
                    <a href="{{ route('admin.subscriptions.index', ['user_id' => $user->id]) }}"
                       class="text-xs text-primary hover:text-yellow-500">
                        {{ trans('messages.View All') }}
                    </a>
                </div>
                @php
                    $userSubscriptions = $user->subscriptions()->with(['coupon'])->orderByDesc('created_at')->limit(5)->get();
                @endphp
                @if($userSubscriptions->count() > 0)
                    <div class="overflow-x-auto" dir="rtl">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.ID') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Name') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Type') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Usage') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Status') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Expires At') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($userSubscriptions as $subscription)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-500">#{{ $subscription->id }}</td>
                                    <td class="px-3 py-2 font-semibold text-secondary">{{ $subscription->name }}</td>
                                    <td class="px-3 py-2">
                                        @if($subscription->type === 'unlimited')
                                            <span class="text-gray-600">{{ trans('messages.Unlimited') }}</span>
                                        @else
                                            <span class="text-gray-600">{{ $subscription->minutes_included }} {{ trans('messages.Minutes') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">
                                        @if($subscription->type === 'unlimited')
                                            {{ $subscription->trips_count }} {{ trans('messages.Trips') }}
                                        @else
                                            {{ $subscription->minutes_used }} / {{ $subscription->minutes_included }} {{ trans('messages.Minutes') }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $statusColors = [
                                                'active' => 'bg-emerald-50 text-emerald-700',
                                                'expired' => 'bg-red-50 text-red-700',
                                                'cancelled' => 'bg-gray-100 text-gray-700',
                                                'suspended' => 'bg-amber-50 text-amber-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ trans('messages.' . ucfirst($subscription->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">{{ $subscription->expires_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-left">
                                        <a href="{{ route('admin.subscriptions.show', $subscription) }}"
                                           class="text-primary hover:text-yellow-500">
                                            {{ trans('messages.View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-sm text-gray-500">
                        {{ trans('messages.No subscriptions yet') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

