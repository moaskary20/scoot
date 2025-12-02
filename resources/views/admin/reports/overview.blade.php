<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Overview Report') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إحصائيات شاملة للنظام') }}
                </p>
            </div>
            <a href="{{ route('admin.reports.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع للتقارير') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Date Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form method="GET" action="{{ route('admin.reports.overview') }}" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date From') }}</label>
                        <input type="date" name="date_from" value="{{ $stats['period']['from'] }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date To') }}</label>
                        <input type="date" name="date_to" value="{{ $stats['period']['to'] }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg">
                        {{ trans('messages.Filter') }}
                    </button>
                </form>
            </div>

            <!-- Scooters Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Scooters Statistics') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total') }}</div>
                        <div class="text-2xl font-bold text-secondary">{{ $stats['scooters']['total'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Active') }}</div>
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['scooters']['active'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Available') }}</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['scooters']['available'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Rented') }}</div>
                        <div class="text-2xl font-bold text-purple-600">{{ $stats['scooters']['rented'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Charging') }}</div>
                        <div class="text-2xl font-bold text-amber-600">{{ $stats['scooters']['charging'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Maintenance') }}</div>
                        <div class="text-2xl font-bold text-red-600">{{ $stats['scooters']['maintenance'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Users Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Users Statistics') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Users') }}</div>
                        <div class="text-2xl font-bold text-secondary">{{ $stats['users']['total'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Active Users') }}</div>
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['users']['active'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.New This Period') }}</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['users']['new_this_period'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Trips Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Trips Statistics') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Trips') }}</div>
                        <div class="text-2xl font-bold text-secondary">{{ $stats['trips']['total'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Completed') }}</div>
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['trips']['completed'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Cancelled') }}</div>
                        <div class="text-2xl font-bold text-red-600">{{ $stats['trips']['cancelled'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Duration') }}</div>
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['trips']['total_duration'] ?? 0) }} min</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Avg Duration') }}</div>
                        <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['trips']['avg_duration'] ?? 0, 1) }} min</div>
                    </div>
                </div>
            </div>

            <!-- Revenue Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Revenue Statistics') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Revenue') }}</div>
                        <div class="text-2xl font-bold text-emerald-600">{{ number_format($stats['revenue']['total'] ?? 0, 2) }} EGP</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.From Trips') }}</div>
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['revenue']['from_trips'] ?? 0, 2) }} EGP</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.From Penalties') }}</div>
                        <div class="text-2xl font-bold text-red-600">{{ number_format($stats['revenue']['from_penalties'] ?? 0, 2) }} EGP</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Wallet Top Ups') }}</div>
                        <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['revenue']['wallet_top_ups'] ?? 0, 2) }} EGP</div>
                    </div>
                </div>
            </div>

            <!-- Penalties Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Penalties Statistics') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Penalties') }}</div>
                        <div class="text-2xl font-bold text-secondary">{{ $stats['penalties']['total'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Paid') }}</div>
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['penalties']['paid'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Pending') }}</div>
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats['penalties']['pending'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Amount') }}</div>
                        <div class="text-2xl font-bold text-red-600">{{ number_format($stats['penalties']['total_amount'] ?? 0, 2) }} EGP</div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Maintenance Statistics') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Records') }}</div>
                        <div class="text-2xl font-bold text-secondary">{{ $stats['maintenance']['total'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Completed') }}</div>
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['maintenance']['completed'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.In Progress') }}</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['maintenance']['in_progress'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Cost') }}</div>
                        <div class="text-2xl font-bold text-red-600">{{ number_format($stats['maintenance']['total_cost'] ?? 0, 2) }} EGP</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Avg Cost') }}</div>
                        <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['maintenance']['avg_cost'] ?? 0, 2) }} EGP</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

