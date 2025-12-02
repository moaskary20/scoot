<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Maintenance') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع عمليات الصيانة والإصلاحات') }}
                </p>
            </div>
            <a href="{{ route('admin.maintenance.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إرسال سكوتر للصيانة') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form method="GET" action="{{ route('admin.maintenance.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Scooters') }}</label>
                        <select name="scooter_id" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            @foreach(\App\Models\Scooter::all() as $s)
                                <option value="{{ $s->id }}" {{ request('scooter_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</label>
                        <select name="status" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ trans('messages.Pending') }}</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>{{ trans('messages.In Progress') }}</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ trans('messages.Completed') }}</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</label>
                        <select name="type" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="scheduled" {{ request('type') === 'scheduled' ? 'selected' : '' }}>{{ trans('messages.Scheduled') }}</option>
                            <option value="repair" {{ request('type') === 'repair' ? 'selected' : '' }}>{{ trans('messages.Repair') }}</option>
                            <option value="battery_replacement" {{ request('type') === 'battery_replacement' ? 'selected' : '' }}>{{ trans('messages.Battery Replacement') }}</option>
                            <option value="firmware_update" {{ request('type') === 'firmware_update' ? 'selected' : '' }}>{{ trans('messages.Firmware Update') }}</option>
                            <option value="inspection" {{ request('type') === 'inspection' ? 'selected' : '' }}>{{ trans('messages.Inspection') }}</option>
                            <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>{{ trans('messages.Other') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Priority') }}</label>
                        <select name="priority" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>{{ trans('messages.Low') }}</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>{{ trans('messages.Medium') }}</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>{{ trans('messages.High') }}</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>{{ trans('messages.Urgent') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date From') }}</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date To') }}</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <div class="md:col-span-6 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg">
                            {{ trans('messages.Filter') }}
                        </button>
                        <a href="{{ route('admin.maintenance.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                            {{ trans('messages.Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Maintenance Records Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.ID') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Scooters') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Title') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Type') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Priority') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Reported At') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($records as $record)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        #{{ $record->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.scooters.show', $record->scooter_id) }}" class="text-sm font-medium text-primary hover:underline">
                                            {{ $record->scooter->code }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $record->title }}</div>
                                        @if($record->description)
                                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($record->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ str_replace('_', ' ', ucwords($record->type, '_')) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($record->priority === 'urgent')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Urgent
                                            </span>
                                        @elseif($record->priority === 'high')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                High
                                            </span>
                                        @elseif($record->priority === 'medium')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Medium
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Low
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($record->status === 'completed')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                Completed
                                            </span>
                                        @elseif($record->status === 'in_progress')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                In Progress
                                            </span>
                                        @elseif($record->status === 'pending')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $record->reported_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.maintenance.show', $record) }}" class="text-primary hover:text-secondary">
                                            {{ trans('messages.View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ trans('messages.No maintenance records found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

