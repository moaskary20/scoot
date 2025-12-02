<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Scooters Report') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تقارير السكوترات والأداء') }}
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
                <form method="GET" action="{{ route('admin.reports.scooters') }}" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date From') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date To') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg">
                        {{ trans('messages.Filter') }}
                    </button>
                </form>
            </div>

            <!-- Battery Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Average Battery') }}</div>
                    <div class="text-3xl font-bold text-secondary">{{ number_format($batteryStats['avg'] ?? 0, 1) }}%</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Low Battery') }} (&lt;20%)</div>
                    <div class="text-3xl font-bold text-red-600">{{ $batteryStats['low'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Medium Battery') }} (20-80%)</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $batteryStats['medium'] ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.High Battery') }} (&gt;80%)</div>
                    <div class="text-3xl font-bold text-emerald-600">{{ $batteryStats['high'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Status Distribution') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @forelse($statusDistribution as $status)
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-secondary">{{ $status->count }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ trans('messages.' . ucfirst($status->status)) }}</div>
                        </div>
                    @empty
                        <div class="col-span-4 text-center text-sm text-gray-500 py-4">{{ trans('messages.No data available') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Scooter Performance -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Scooter Performance') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Scooter Code') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Battery') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Trips Count') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Revenue') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Total Duration') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($scooterPerformance as $scooter)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-secondary">
                                        <a href="{{ route('admin.scooters.show', $scooter->id) }}" class="hover:text-primary">
                                            {{ $scooter->code }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'available' => 'bg-emerald-100 text-emerald-800',
                                                'rented' => 'bg-blue-100 text-blue-800',
                                                'charging' => 'bg-yellow-100 text-yellow-800',
                                                'maintenance' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$scooter->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ trans('messages.' . ucfirst($scooter->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                <div class="bg-{{ $scooter->battery_percentage > 80 ? 'emerald' : ($scooter->battery_percentage > 20 ? 'yellow' : 'red') }}-600 h-2 rounded-full" style="width: {{ $scooter->battery_percentage }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $scooter->battery_percentage }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $scooter->trips_count ?? 0 }}</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-600">{{ number_format($scooter->trips_sum_cost ?? 0, 2) }} {{ trans('messages.EGP') }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ number_format($scooter->trips_sum_duration_minutes ?? 0) }} {{ trans('messages.min') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ trans('messages.No data available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

