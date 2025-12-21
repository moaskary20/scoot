<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Active Users') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.View and manage all active users in the system') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <span class="text-sm font-semibold text-emerald-600">
                        {{ trans('messages.Total') }}: {{ $activeCount }} {{ trans('messages.Active Users') }}
                    </span>
                </div>
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:bg-gray-200 transition">
                    {{ trans('messages.All Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Active') }}</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $activeCount }}</p>
                        </div>
                        <div class="p-3 bg-emerald-100 rounded-lg">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">{{ trans('messages.With Trips') }}</p>
                            <p class="text-2xl font-bold text-blue-600">{{ \App\Models\User::where('is_active', true)->has('trips')->count() }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">{{ trans('messages.With Wallet Balance') }}</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\User::where('is_active', true)->where('wallet_balance', '>', 0)->count() }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">{{ trans('messages.With Loyalty Points') }}</p>
                            <p class="text-2xl font-bold text-purple-600">{{ \App\Models\User::where('is_active', true)->where('loyalty_points', '>', 0)->count() }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                <form method="GET" action="{{ route('admin.users.active') }}" id="filter-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Search') }}
                            </label>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="{{ trans('messages.Name, Email, Phone') }}"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>

                        <!-- Loyalty Level Filter -->
                        <div>
                            <label for="loyalty_level" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Loyalty Level') }}
                            </label>
                            <select name="loyalty_level" id="loyalty_level" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                                <option value="">{{ trans('messages.All Levels') }}</option>
                                <option value="bronze" {{ request('loyalty_level') === 'bronze' ? 'selected' : '' }}>{{ trans('messages.Bronze') }}</option>
                                <option value="silver" {{ request('loyalty_level') === 'silver' ? 'selected' : '' }}>{{ trans('messages.Silver') }}</option>
                                <option value="gold" {{ request('loyalty_level') === 'gold' ? 'selected' : '' }}>{{ trans('messages.Gold') }}</option>
                            </select>
                        </div>

                        <!-- Wallet Balance Min -->
                        <div>
                            <label for="wallet_min" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Min Wallet Balance') }} ({{ trans('messages.EGP') }})
                            </label>
                            <input type="number" 
                                   name="wallet_min" 
                                   id="wallet_min" 
                                   value="{{ request('wallet_min') }}" 
                                   step="0.01"
                                   min="0"
                                   placeholder="0"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>

                        <!-- Wallet Balance Max -->
                        <div>
                            <label for="wallet_max" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Max Wallet Balance') }} ({{ trans('messages.EGP') }})
                            </label>
                            <input type="number" 
                                   name="wallet_max" 
                                   id="wallet_max" 
                                   value="{{ request('wallet_max') }}" 
                                   step="0.01"
                                   min="0"
                                   placeholder="∞"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>

                        <!-- Sort By -->
                        <div>
                            <label for="sort_by" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Sort By') }}
                            </label>
                            <select name="sort_by" id="sort_by" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>{{ trans('messages.Registration Date') }}</option>
                                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>{{ trans('messages.Name') }}</option>
                                <option value="wallet_balance" {{ request('sort_by') === 'wallet_balance' ? 'selected' : '' }}>{{ trans('messages.Wallet Balance') }}</option>
                                <option value="loyalty_points" {{ request('sort_by') === 'loyalty_points' ? 'selected' : '' }}>{{ trans('messages.Loyalty Points') }}</option>
                                <option value="trips_count" {{ request('sort_by') === 'trips_count' ? 'selected' : '' }}>{{ trans('messages.Trips Count') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="sort_order" id="sort_order" value="{{ request('sort_order', 'desc') }}">
                        <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg hover:bg-yellow-400 transition">
                            {{ trans('messages.Filter') }}
                        </button>
                        @if(request()->hasAny(['search', 'loyalty_level', 'wallet_min', 'wallet_max', 'sort_by']))
                            <a href="{{ route('admin.users.active') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">
                                {{ trans('messages.Reset') }}
                            </a>
                        @endif
                        <button type="button" onclick="toggleSortOrder()" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                            <span id="sort-order-icon">{{ request('sort_order', 'desc') === 'desc' ? '↓' : '↑' }}</span>
                            {{ trans('messages.Sort Order') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="min-w-full divide-y divide-gray-200" dir="rtl" style="position: relative;">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable('name')">
                                    <div class="flex items-center gap-2">
                                        {{ trans('messages.Name') }}
                                        @if(request('sort_by') === 'name')
                                            <span class="text-primary">{{ request('sort_order') === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable('email')">
                                    <div class="flex items-center gap-2">
                                        {{ trans('messages.Email') }}
                                        @if(request('sort_by') === 'email')
                                            <span class="text-primary">{{ request('sort_order') === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Phone') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable('wallet_balance')">
                                    <div class="flex items-center gap-2">
                                        {{ trans('messages.Wallet Balance') }}
                                        @if(request('sort_by') === 'wallet_balance')
                                            <span class="text-primary">{{ request('sort_order') === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable('loyalty_points')">
                                    <div class="flex items-center gap-2">
                                        {{ trans('messages.Loyalty Points') }}
                                        @if(request('sort_by') === 'loyalty_points')
                                            <span class="text-primary">{{ request('sort_order') === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable('loyalty_level')">
                                    <div class="flex items-center gap-2">
                                        {{ trans('messages.Loyalty Level') }}
                                        @if(request('sort_by') === 'loyalty_level')
                                            <span class="text-primary">{{ request('sort_order') === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable('trips_count')">
                                    <div class="flex items-center gap-2">
                                        {{ trans('messages.Trips') }}
                                        @if(request('sort_by') === 'trips_count')
                                            <span class="text-primary">{{ request('sort_order') === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Last Activity') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50" x-data="{ showTimeline: false }">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center cursor-pointer" @click="showTimeline = !showTimeline" title="{{ trans('messages.Click to view activity timeline') }}">
                                                <span class="text-emerald-600 font-semibold text-sm">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                            <div class="mr-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                @if($user->age)
                                                    <div class="text-xs text-gray-500">{{ trans('messages.Age') }}: {{ $user->age }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->phone ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $balance = $user->wallet_balance;
                                            $isNegative = $balance < 0;
                                        @endphp
                                        <div class="text-sm font-semibold {{ $isNegative ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($balance, 2) }} {{ trans('messages.EGP') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ number_format($user->loyalty_points) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($user->loyalty_level === 'Gold') bg-yellow-100 text-yellow-800
                                            @elseif($user->loyalty_level === 'Silver') bg-gray-100 text-gray-800
                                            @else bg-orange-100 text-orange-800
                                            @endif">
                                            {{ trans('messages.' . ucfirst($user->loyalty_level)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-900">{{ $user->trips_count ?? 0 }}</span>
                                            @if($user->trips()->exists())
                                                @php
                                                    $lastTrip = $user->trips()->latest()->first();
                                                @endphp
                                                <span class="text-xs text-gray-500" title="{{ trans('messages.Last Trip') }}: {{ $lastTrip->created_at->format('Y-m-d H:i') }}">
                                                    ({{ $lastTrip->created_at->diffForHumans() }})
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            @if($user->trips()->exists())
                                                {{ $user->trips()->latest()->first()->created_at->diffForHumans() }}
                                            @else
                                                {{ $user->created_at->diffForHumans() }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-left">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.users.show', $user) }}"
                                               class="text-xs text-gray-600 hover:text-secondary">
                                                {{ trans('messages.View') }}
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                               class="text-xs text-primary hover:text-yellow-500">
                                                {{ trans('messages.Edit') }}
                                            </a>
                                            <div class="relative inline-block" 
                                                 x-data="{ 
                                                     showQuickActions: false,
                                                     positionMenu() {
                                                         if (this.showQuickActions) {
                                                             const button = this.$el.querySelector('button');
                                                             const menu = this.$el.querySelector('[x-show]');
                                                             if (button && menu) {
                                                                 const rect = button.getBoundingClientRect();
                                                                 menu.style.left = (rect.left - 220) + 'px';
                                                                 menu.style.top = (rect.bottom + 5) + 'px';
                                                             }
                                                         }
                                                     }
                                                 }" 
                                                 x-init="
                                                     $watch('showQuickActions', () => {
                                                         if (showQuickActions) {
                                                             setTimeout(() => positionMenu(), 10);
                                                         }
                                                     });
                                                 ">
                                                <button @click="showQuickActions = !showQuickActions" 
                                                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                                    {{ trans('messages.Quick Actions') }}
                                                </button>
                                                <div x-show="showQuickActions" 
                                                     @click.away="showQuickActions = false"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     class="fixed w-56 bg-white rounded-lg shadow-2xl border border-gray-300 z-[9999]" 
                                                     style="display: none;"
                                                     x-init="
                                                         $watch('showQuickActions', () => {
                                                             if (showQuickActions) {
                                                                 setTimeout(() => {
                                                                     const button = $el.previousElementSibling;
                                                                     if (button) {
                                                                         const rect = button.getBoundingClientRect();
                                                                         $el.style.left = (rect.left - 220) + 'px';
                                                                         $el.style.top = (rect.bottom + 5) + 'px';
                                                                     }
                                                                 }, 10);
                                                             }
                                                         });
                                                     "
                                                     dir="rtl">
                                                    <div class="py-2">
                                                        <a href="{{ route('admin.users.show', $user) }}#wallet" 
                                                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                            </svg>
                                                            <span>{{ trans('messages.Add Wallet Balance') }}</span>
                                                        </a>
                                                        <a href="{{ route('admin.users.show', $user) }}#loyalty" 
                                                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                            </svg>
                                                            <span>{{ trans('messages.Add Loyalty Points') }}</span>
                                                        </a>
                                                        <button onclick="sendNotification({{ $user->id }})" 
                                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition text-right">
                                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                            </svg>
                                                            <span>{{ trans('messages.Send Notification') }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Activity Timeline Row -->
                                <tr x-show="showTimeline" x-transition style="display: none;">
                                    <td colspan="9" class="px-4 py-4 bg-gray-50">
                                        <div class="space-y-3">
                                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ trans('messages.Activity Timeline') }} - {{ $user->name }}</h4>
                                            <div class="space-y-2">
                                                @php
                                                    $activities = collect();
                                                    // Add registration
                                                    $activities->push([
                                                        'type' => 'registration',
                                                        'date' => $user->created_at,
                                                        'title' => trans('messages.User Registered'),
                                                        'icon' => 'user-plus',
                                                        'color' => 'blue'
                                                    ]);
                                                    // Add trips
                                                    foreach($user->trips()->latest()->limit(5)->get() as $trip) {
                                                        $activities->push([
                                                            'type' => 'trip',
                                                            'date' => $trip->created_at,
                                                            'title' => trans('messages.Trip') . ' #' . $trip->id . ' - ' . trans('messages.Status') . ': ' . trans('messages.' . ucfirst($trip->status)),
                                                            'icon' => 'trip',
                                                            'color' => $trip->status === 'completed' ? 'green' : ($trip->status === 'active' ? 'yellow' : 'red')
                                                        ]);
                                                    }
                                                    // Add wallet transactions
                                                    foreach($user->walletTransactions()->latest()->limit(5)->get() as $transaction) {
                                                        $activities->push([
                                                            'type' => 'wallet',
                                                            'date' => $transaction->created_at,
                                                            'title' => trans('messages.Wallet Transaction') . ' - ' . ($transaction->transaction_type === 'credit' ? '+' : '-') . number_format($transaction->amount, 2) . ' ' . trans('messages.EGP'),
                                                            'icon' => 'wallet',
                                                            'color' => $transaction->transaction_type === 'credit' ? 'green' : 'red'
                                                        ]);
                                                    }
                                                    // Add loyalty transactions
                                                    foreach($user->loyaltyPointsTransactions()->latest()->limit(5)->get() as $transaction) {
                                                        $activities->push([
                                                            'type' => 'loyalty',
                                                            'date' => $transaction->created_at,
                                                            'title' => trans('messages.Loyalty Points') . ' - ' . ($transaction->type === 'earned' ? '+' : '-') . $transaction->points,
                                                            'icon' => 'star',
                                                            'color' => $transaction->type === 'earned' ? 'green' : 'red'
                                                        ]);
                                                    }
                                                    $activities = $activities->sortByDesc('date')->take(10);
                                                @endphp
                                                @foreach($activities as $activity)
                                                    <div class="flex items-start gap-3 text-sm">
                                                        <div class="flex-shrink-0 mt-1">
                                                            <div class="w-2 h-2 rounded-full bg-{{ $activity['color'] }}-500"></div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="text-gray-900">{{ $activity['title'] }}</div>
                                                            <div class="text-xs text-gray-500">{{ $activity['date']->format('Y-m-d H:i') }} ({{ $activity['date']->diffForHumans() }})</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @if($activities->isEmpty())
                                                    <p class="text-sm text-gray-500">{{ trans('messages.No activity recorded yet') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500" dir="rtl">
                                        {{ trans('messages.No active users found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        function sortTable(column) {
            const form = document.getElementById('filter-form');
            const sortByInput = document.getElementById('sort_by');
            const sortOrderInput = document.getElementById('sort_order');
            
            if (sortByInput.value === column) {
                // Toggle sort order
                sortOrderInput.value = sortOrderInput.value === 'desc' ? 'asc' : 'desc';
            } else {
                // Set new column
                sortByInput.value = column;
                sortOrderInput.value = 'desc';
            }
            
            form.submit();
        }

        function toggleSortOrder() {
            const sortOrderInput = document.getElementById('sort_order');
            const icon = document.getElementById('sort-order-icon');
            
            if (sortOrderInput.value === 'desc') {
                sortOrderInput.value = 'asc';
                icon.textContent = '↑';
            } else {
                sortOrderInput.value = 'desc';
                icon.textContent = '↓';
            }
            
            document.getElementById('filter-form').submit();
        }

        function sendNotification(userId) {
            const message = prompt('{{ trans('messages.Enter notification message') }}:');
            if (message) {
                // TODO: Implement notification sending
                alert('{{ trans('messages.Notification feature will be implemented soon') }}');
            }
        }
    </script>
</x-app-layout>
