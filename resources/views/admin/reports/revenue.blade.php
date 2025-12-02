<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Revenue Report') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تقارير الإيرادات والمدفوعات') }}
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
                <form method="GET" action="{{ route('admin.reports.revenue') }}" class="flex items-end gap-4">
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

            <!-- Revenue Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Current Month') }}</div>
                    <div class="text-3xl font-bold text-emerald-600">{{ number_format($currentMonth, 2) }} EGP</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Last Month') }}</div>
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($lastMonth, 2) }} EGP</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Growth') }}</div>
                    @php
                        $growth = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
                    @endphp
                    <div class="text-3xl font-bold {{ $growth >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                    </div>
                </div>
            </div>

            <!-- Revenue by Type -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Revenue by Type') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.From Trips') }}</div>
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($revenueByType['trips'] ?? 0, 2) }} EGP</div>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.From Penalties') }}</div>
                        <div class="text-2xl font-bold text-red-600">{{ number_format($revenueByType['penalties'] ?? 0, 2) }} EGP</div>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Wallet Top Ups') }}</div>
                        <div class="text-2xl font-bold text-yellow-600">{{ number_format($revenueByType['wallet_top_ups'] ?? 0, 2) }} EGP</div>
                    </div>
                </div>
            </div>

            <!-- Daily Revenue -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Daily Revenue') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Date') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Revenue') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Trips Count') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Avg per Trip') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($dailyRevenue as $revenue)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">{{ $revenue->date }}</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-600">{{ number_format($revenue->revenue ?? 0, 2) }} EGP</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $revenue->trip_count }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $revenue->trip_count > 0 ? number_format(($revenue->revenue ?? 0) / $revenue->trip_count, 2) : '0.00' }} EGP
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ trans('messages.No data available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

