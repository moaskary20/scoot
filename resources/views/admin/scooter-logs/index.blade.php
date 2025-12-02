<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Anti-Theft Logs') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.سجل جميع أحداث السكوترات والتنبيهات') }}
                </p>
            </div>
            <a href="{{ route('admin.scooter-logs.critical') }}"
               class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-red-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                {{ trans('messages.Critical Alerts') }}
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
                <form method="GET" action="{{ route('admin.scooter-logs.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Event Type') }}</label>
                        <select name="event_type" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="battery_drop" {{ request('event_type') === 'battery_drop' ? 'selected' : '' }}>Battery Drop</option>
                            <option value="zone_exit" {{ request('event_type') === 'zone_exit' ? 'selected' : '' }}>Zone Exit</option>
                            <option value="forced_movement" {{ request('event_type') === 'forced_movement' ? 'selected' : '' }}>Forced Movement</option>
                            <option value="manual_lock" {{ request('event_type') === 'manual_lock' ? 'selected' : '' }}>Manual Lock</option>
                            <option value="manual_unlock" {{ request('event_type') === 'manual_unlock' ? 'selected' : '' }}>Manual Unlock</option>
                            <option value="auto_lock" {{ request('event_type') === 'auto_lock' ? 'selected' : '' }}>Auto Lock</option>
                            <option value="auto_unlock" {{ request('event_type') === 'auto_unlock' ? 'selected' : '' }}>Auto Unlock</option>
                            <option value="gps_update" {{ request('event_type') === 'gps_update' ? 'selected' : '' }}>GPS Update</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Severity') }}</label>
                        <select name="severity" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Resolved') }}</label>
                        <select name="is_resolved" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="0" {{ request('is_resolved') === '0' ? 'selected' : '' }}>{{ trans('messages.Unresolved') }}</option>
                            <option value="1" {{ request('is_resolved') === '1' ? 'selected' : '' }}>{{ trans('messages.Resolved') }}</option>
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
                        <a href="{{ route('admin.scooter-logs.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                            {{ trans('messages.Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Logs Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Date') }}/{{ trans('messages.Time') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Scooters') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Event') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Severity') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>{{ $log->created_at->format('Y-m-d') }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.scooters.show', $log->scooter_id) }}" class="text-sm font-medium text-primary hover:underline">
                                            {{ $log->scooter->code }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->title }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $log->description }}</div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                                {{ str_replace('_', ' ', $log->event_type) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->severity === 'critical')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Critical
                                            </span>
                                        @elseif($log->severity === 'warning')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Warning
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Info
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->is_resolved)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                Resolved
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Unresolved
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.scooter-logs.show', $log) }}" class="text-primary hover:text-secondary">
                                            {{ trans('messages.View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ trans('messages.No logs found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

