<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Maintenance Report') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تقارير الصيانة والإصلاحات') }}
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
                <form method="GET" action="{{ route('admin.reports.maintenance') }}" class="flex items-end gap-4">
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

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Maintenance by Type') }}</h3>
                    <div class="space-y-2">
                        @forelse($maintenanceStats['by_type'] as $stat)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ trans('messages.' . ucfirst(str_replace('_', ' ', $stat->type))) }}</span>
                                <span class="text-sm font-semibold text-secondary">{{ $stat->count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ trans('messages.No data available') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Maintenance by Status') }}</h3>
                    <div class="space-y-2">
                        @forelse($maintenanceStats['by_status'] as $stat)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ trans('messages.' . ucfirst(str_replace('_', ' ', $stat->status))) }}</span>
                                <span class="text-sm font-semibold text-secondary">{{ $stat->count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ trans('messages.No data available') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Maintenance by Priority') }}</h3>
                    <div class="space-y-2">
                        @forelse($maintenanceStats['by_priority'] as $stat)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ trans('messages.' . ucfirst($stat->priority)) }}</span>
                                <span class="text-sm font-semibold text-secondary">{{ $stat->count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ trans('messages.No data available') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Cost Statistics') }}</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">{{ trans('messages.Total Cost') }}</span>
                            <span class="text-sm font-semibold text-red-600">{{ number_format($maintenanceStats['total_cost'] ?? 0, 2) }} {{ trans('messages.EGP') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">{{ trans('messages.Average Cost') }}</span>
                            <span class="text-sm font-semibold text-secondary">{{ number_format($maintenanceStats['avg_cost'] ?? 0, 2) }} {{ trans('messages.EGP') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Records -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Maintenance Records') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.ID') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Scooter') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Title') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Type') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Priority') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Reported At') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($maintenanceRecords as $record)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">#{{ $record->id }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.scooters.show', $record->scooter_id) }}" class="font-semibold text-secondary hover:text-primary">
                                            {{ $record->scooter->code }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $record->title }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ trans('messages.' . ucfirst(str_replace('_', ' ', $record->type))) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium 
                                            {{ $record->priority === 'urgent' ? 'bg-red-100 text-red-800' : 
                                               ($record->priority === 'high' ? 'bg-orange-100 text-orange-800' : 
                                               ($record->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                            {{ trans('messages.' . ucfirst($record->priority)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium 
                                            {{ $record->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 
                                               ($record->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                               ($record->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ trans('messages.' . ucfirst(str_replace('_', ' ', $record->status))) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $record->reported_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">{{ trans('messages.No data available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $maintenanceRecords->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

