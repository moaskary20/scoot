<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-red-600 leading-tight">
                    {{ trans('messages.Critical Alerts') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تنبيهات حرجة تتطلب تدخل فوري') }}
                </p>
            </div>
            <a href="{{ route('admin.scooter-logs.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.جميع السجلات') }}
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

            <!-- Critical Alerts -->
            <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="text-sm font-semibold text-red-800">
                            {{ $criticalLogs->total() }} {{ trans('messages.Critical Unresolved Alerts') }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Date/Time') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Scooter') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Event') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($criticalLogs as $log)
                                <tr class="hover:bg-red-50">
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
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-600">
                                                {{ str_replace('_', ' ', $log->event_type) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.scooter-logs.show', $log) }}" class="text-red-600 hover:text-red-800 font-semibold">
                                            {{ trans('messages.View & Resolve') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ trans('messages.No critical alerts. All systems operational!') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $criticalLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

