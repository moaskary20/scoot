<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Event Details') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل الحدث') }}
                </p>
            </div>
            <a href="{{ route('admin.scooter-logs.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع للقائمة') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Event Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Event Type') }}</div>
                            <div class="text-sm font-semibold text-secondary">
                                {{ str_replace('_', ' ', ucwords($scooterLog->event_type, '_')) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Title') }}</div>
                            <div class="text-sm font-medium text-gray-900">{{ $scooterLog->title }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Description') }}</div>
                            <div class="text-sm text-gray-700">{{ $scooterLog->description ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Severity') }}</div>
                            <div>
                                @if($scooterLog->severity === 'critical')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Critical
                                    </span>
                                @elseif($scooterLog->severity === 'warning')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Warning
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Info
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Date/Time') }}</div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ $scooterLog->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Scooter') }}</div>
                            <div>
                                <a href="{{ route('admin.scooters.show', $scooterLog->scooter_id) }}" class="text-sm font-medium text-primary hover:underline">
                                    {{ $scooterLog->scooter->code }}
                                </a>
                            </div>
                        </div>

                        @if($scooterLog->trip)
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Trip') }}</div>
                                <div>
                                    <a href="{{ route('admin.trips.show', $scooterLog->trip_id) }}" class="text-sm font-medium text-primary hover:underline">
                                        Trip #{{ $scooterLog->trip_id }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($scooterLog->user)
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ trans('messages.User') }}</div>
                                <div>
                                    <a href="{{ route('admin.users.show', $scooterLog->user_id) }}" class="text-sm font-medium text-primary hover:underline">
                                        {{ $scooterLog->user->name }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                            <div>
                                @if($scooterLog->is_resolved)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Resolved
                                    </span>
                                    @if($scooterLog->resolved_at)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $scooterLog->resolved_at->format('Y-m-d H:i:s') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Unresolved
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                @if($scooterLog->latitude && $scooterLog->longitude)
                    <div class="pt-6 border-t border-gray-200">
                        <div class="text-xs text-gray-500 mb-2">{{ trans('messages.Location') }}</div>
                        <div class="text-sm text-gray-700 font-mono">
                            {{ $scooterLog->latitude }}, {{ $scooterLog->longitude }}
                        </div>
                        <div id="log-map" class="mt-3 aspect-[16/6] w-full rounded-lg bg-gray-100 border border-gray-200"></div>
                    </div>
                @endif

                <!-- Additional Data -->
                @if($scooterLog->data)
                    <div class="pt-6 border-t border-gray-200">
                        <div class="text-xs text-gray-500 mb-2">{{ trans('messages.Additional Data') }}</div>
                        <pre class="text-xs bg-gray-50 p-3 rounded-lg overflow-x-auto">{{ json_encode($scooterLog->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                <!-- Resolution Notes -->
                @if($scooterLog->is_resolved && $scooterLog->resolution_notes)
                    <div class="pt-6 border-t border-gray-200">
                        <div class="text-xs text-gray-500 mb-2">{{ trans('messages.Resolution Notes') }}</div>
                        <div class="text-sm text-gray-700 bg-emerald-50 p-3 rounded-lg">
                            {{ $scooterLog->resolution_notes }}
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                @if(!$scooterLog->is_resolved)
                    <div class="pt-6 border-t border-gray-200 space-y-4">
                        <form method="POST" action="{{ route('admin.scooter-logs.resolve', $scooterLog) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Resolution Notes') }}</label>
                                <textarea name="resolution_notes" rows="3" class="w-full text-sm rounded-lg border-gray-300" placeholder="{{ trans('messages.Optional notes about how this issue was resolved...') }}"></textarea>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                                {{ trans('messages.Mark as Resolved') }}
                            </button>
                        </form>

                        @if($scooterLog->scooter->status !== 'maintenance')
                            <div class="pt-4 border-t border-gray-200">
                                <a href="{{ route('admin.maintenance.create', ['scooter_id' => $scooterLog->scooter_id, 'scooter_log_id' => $scooterLog->id]) }}"
                                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ trans('messages.Send Scooter to Maintenance') }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($scooterLog->latitude && $scooterLog->longitude)
        @push('scripts')
            <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', 'YOUR_API_KEY') }}"></script>
            <script>
                function initLogMap() {
                    const position = { 
                        lat: {{ $scooterLog->latitude }}, 
                        lng: {{ $scooterLog->longitude }} 
                    };

                    const map = new google.maps.Map(document.getElementById('log-map'), {
                        zoom: 15,
                        center: position,
                        mapTypeId: 'roadmap',
                    });

                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: '{{ $scooterLog->title }}',
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 10,
                            fillColor: '#ef4444',
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 2,
                        },
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="p-2 text-xs">
                                <div class="font-semibold mb-1">{{ $scooterLog->title }}</div>
                                <div class="text-gray-600">{{ $scooterLog->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initLogMap);
                } else {
                    initLogMap();
                }
            </script>
        @endpush
    @endif
</x-app-layout>

