<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Maintenance Record') }} #{{ $maintenance->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل عملية الصيانة') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.maintenance.edit', $maintenance) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.تعديل') }}
                </a>
                <a href="{{ route('admin.maintenance.index') }}"
                   class="text-sm text-gray-600 hover:text-secondary">
                    {{ trans('messages.رجوع') }}
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

            <!-- Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                    @if($maintenance->status === 'completed')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                            Completed
                        </span>
                    @elseif($maintenance->status === 'in_progress')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            In Progress
                        </span>
                    @elseif($maintenance->status === 'pending')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Pending
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            Cancelled
                        </span>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Priority') }}</div>
                    @if($maintenance->priority === 'urgent')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            Urgent
                        </span>
                    @elseif($maintenance->priority === 'high')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                            High
                        </span>
                    @elseif($maintenance->priority === 'medium')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Medium
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            Low
                        </span>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Estimated Cost') }}</div>
                    <div class="text-2xl font-bold text-secondary">
                        {{ $maintenance->estimated_cost ? number_format($maintenance->estimated_cost, 2) . ' EGP' : '-' }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Actual Cost') }}</div>
                    <div class="text-2xl font-bold text-emerald-600">
                        {{ $maintenance->actual_cost ? number_format($maintenance->actual_cost, 2) . ' EGP' : '-' }}
                    </div>
                </div>
            </div>

            <!-- Main Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Maintenance Information') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Scooter') }}</div>
                        <div class="font-semibold text-secondary">
                            <a href="{{ route('admin.scooters.show', $maintenance->scooter) }}" class="hover:text-primary">
                                {{ $maintenance->scooter->code }}
                            </a>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</div>
                        <div class="font-semibold text-secondary">
                            {{ str_replace('_', ' ', ucwords($maintenance->type, '_')) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Title') }}</div>
                        <div class="font-semibold text-secondary">{{ $maintenance->title }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Reported At') }}</div>
                        <div class="text-gray-700">{{ $maintenance->reported_at->format('Y-m-d H:i:s') }}</div>
                    </div>

                    @if($maintenance->scheduled_at)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Scheduled At') }}</div>
                            <div class="text-gray-700">{{ $maintenance->scheduled_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    @endif

                    @if($maintenance->started_at)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Started At') }}</div>
                            <div class="text-gray-700">{{ $maintenance->started_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    @endif

                    @if($maintenance->completed_at)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Completed At') }}</div>
                            <div class="text-gray-700">{{ $maintenance->completed_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    @endif
                </div>

                @if($maintenance->description)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Description') }}</div>
                        <div class="text-sm text-gray-700">{{ $maintenance->description }}</div>
                    </div>
                @endif

                @if($maintenance->fault_details)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Fault Details') }}</div>
                        <div class="text-sm text-gray-700 bg-red-50 p-3 rounded-lg">{{ $maintenance->fault_details }}</div>
                    </div>
                @endif

                @if($maintenance->scooterLog)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Related Event') }}</div>
                        <div>
                            <a href="{{ route('admin.scooter-logs.show', $maintenance->scooterLog) }}" class="text-sm text-primary hover:underline">
                                View Event: {{ $maintenance->scooterLog->title }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Technician Information -->
            @if($maintenance->technician_name || $maintenance->status === 'in_progress')
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Technician Information') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        @if($maintenance->technician_name)
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Name') }}</div>
                                <div class="font-semibold text-secondary">{{ $maintenance->technician_name }}</div>
                            </div>
                        @endif

                        @if($maintenance->technician_phone)
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Phone') }}</div>
                                <div class="text-gray-700">{{ $maintenance->technician_phone }}</div>
                            </div>
                        @endif

                        @if($maintenance->technician_email)
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Email') }}</div>
                                <div class="text-gray-700">{{ $maintenance->technician_email }}</div>
                            </div>
                        @endif
                    </div>

                    @if($maintenance->technician_notes)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Technician Notes') }}</div>
                            <div class="text-sm text-gray-700 bg-blue-50 p-3 rounded-lg">{{ $maintenance->technician_notes }}</div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Resolution Information -->
            @if($maintenance->status === 'completed')
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Resolution Information') }}</h3>
                    
                    @if($maintenance->resolution_notes)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Resolution Notes') }}</div>
                            <div class="text-sm text-gray-700 bg-emerald-50 p-3 rounded-lg">{{ $maintenance->resolution_notes }}</div>
                        </div>
                    @endif

                    @if($maintenance->parts_replaced)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Parts Replaced') }}</div>
                            <div class="text-sm text-gray-700">{{ $maintenance->parts_replaced }}</div>
                        </div>
                    @endif

                    @if($maintenance->quality_rating)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Quality Rating') }}</div>
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $maintenance->quality_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                                <span class="text-sm text-gray-600 ml-2">({{ $maintenance->quality_rating }}/5)</span>
                            </div>
                        </div>
                    @endif

                    @if($maintenance->quality_notes)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Quality Notes') }}</div>
                            <div class="text-sm text-gray-700">{{ $maintenance->quality_notes }}</div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Actions') }}</h3>
                
                <div class="flex flex-wrap gap-3">
                    @if($maintenance->status === 'pending')
                        <form method="POST" action="{{ route('admin.maintenance.start', $maintenance) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                {{ trans('messages.Start Maintenance') }}
                            </button>
                        </form>
                    @endif

                    @if($maintenance->status === 'in_progress')
                        <form method="POST" action="{{ route('admin.maintenance.complete', $maintenance) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                                {{ trans('messages.Complete Maintenance') }}
                            </button>
                        </form>
                    @endif

                    @if(in_array($maintenance->status, ['pending', 'in_progress']))
                        <form method="POST" action="{{ route('admin.maintenance.cancel', $maintenance) }}" class="inline" onsubmit="return confirm('{{ trans('messages.Are you sure you want to cancel this maintenance?') }}');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                                {{ trans('messages.Cancel Maintenance') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

