<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Scooter Details') }} #{{ $scooter->code }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.Review scooter status and location') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if($scooter->status !== 'maintenance')
                    <a href="{{ route('admin.maintenance.create', ['scooter_id' => $scooter->id]) }}"
                       class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                        {{ trans('messages.Send to Maintenance') }}
                    </a>
                @endif
                @if($scooter->is_locked)
                    <form method="POST" action="{{ route('admin.scooters.unlock', $scooter) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-secondary text-sm font-medium hover:bg-primary/90">
                            {{ trans('messages.Unlock') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.scooters.lock', $scooter) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-primary text-sm font-medium hover:bg-secondary/90">
                            {{ trans('messages.Lock') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.scooters.edit', $scooter) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.Edit') }}
                </a>
                <a href="{{ route('admin.scooters.index') }}"
                   class="text-sm text-gray-600 hover:text-secondary">
                    {{ trans('messages.Back to Scooters') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div class="space-y-2">
                    <div class="text-xs text-gray-500">{{ trans('messages.Code') }}</div>
                    <div class="font-semibold text-secondary">{{ $scooter->code }}</div>

                    <div class="mt-4 text-xs text-gray-500">{{ trans('messages.Status') }}</div>
                    <div>
                        @php
                            $statusColors = [
                                'available' => 'bg-emerald-50 text-emerald-700',
                                'rented' => 'bg-blue-50 text-blue-700',
                                'charging' => 'bg-amber-50 text-amber-700',
                                'maintenance' => 'bg-red-50 text-red-700',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$scooter->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ trans('messages.' . ucfirst($scooter->status)) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-xs text-gray-500">{{ trans('messages.Battery') }}</div>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $scooter->battery_percentage }}%" data-battery-percentage="{{ $scooter->battery_percentage }}"></div>
                        </div>
                        <span class="text-xs text-gray-700" data-battery-text>
                            {{ $scooter->battery_percentage }}%
                        </span>
                    </div>

                    <div class="mt-4 text-xs text-gray-500">{{ trans('messages.Lock') }}</div>
                    <div>
                        @if($scooter->is_locked)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-secondary text-primary" data-lock-status="true">
                                {{ trans('messages.Locked') }}
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-primary/20 text-secondary" data-lock-status="false">
                                {{ trans('messages.Unlocked') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-xs text-gray-500">{{ trans('messages.Device IMEI') }}</div>
                    <div class="font-mono text-xs text-gray-700">
                        {{ $scooter->device_imei ?: '-' }}
                    </div>

                    <div class="mt-4 text-xs text-gray-500">{{ trans('messages.Firmware Version') }}</div>
                    <div class="text-xs text-gray-700">
                        {{ $scooter->firmware_version ?: '-' }}
                    </div>
                </div>
            </div>

            <!-- ESP32 Connection Status -->
            @if($scooter->device_imei)
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 text-sm">
                            <div class="font-semibold text-blue-900 mb-1">{{ trans('messages.ESP32 Connection') }}</div>
                            <p class="text-blue-700 text-xs leading-relaxed">
                                {{ trans('messages.ESP32 should call /api/v1/scooter/get-commands every 5-10 seconds to receive lock/unlock commands. When you click Lock/Unlock button, the command will be sent to ESP32 on the next API call.') }}
                            </p>
                            <p class="text-blue-600 text-xs mt-2 font-mono">
                                IMEI: {{ $scooter->device_imei }}
                            </p>
                            <p class="text-blue-600 text-xs mt-1">
                                {{ trans('messages.API Endpoint') }}: <code class="bg-blue-100 px-1 rounded">POST /api/v1/scooter/get-commands</code>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1 text-sm">
                            <div class="font-semibold text-amber-900 mb-1">{{ trans('messages.No ESP32 Connection') }}</div>
                            <p class="text-amber-700 text-xs leading-relaxed">
                                {{ trans('messages.This scooter does not have a device IMEI configured. Please add IMEI in the edit page to enable ESP32 remote control.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Simple Map placeholder for this scooter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-secondary">
                            {{ trans('messages.Scooter Location (GPS)') }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ trans('messages.Map will be connected later to Google Maps API to display scooter location') }}
                        </p>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ trans('messages.Last seen:') }}
                        <span class="font-semibold text-secondary">
                            {{ $scooter->last_seen_at?->format('Y-m-d H:i') ?: '-' }}
                        </span>
                    </div>
                </div>

                <div class="mt-2">
                    @if($scooter->latitude && $scooter->longitude)
                        <div id="scooter-detail-map" class="aspect-[16/6] w-full rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden"></div>
                    @else
                        <div class="aspect-[16/6] w-full rounded-2xl bg-gray-100 border border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs">
                            <span>{{ trans('messages.No GPS coordinates yet') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($scooter->latitude && $scooter->longitude)
        @push('scripts')
            <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', 'YOUR_API_KEY') }}"></script>
            <script>
                function initScooterMap() {
                    const position = { 
                        lat: {{ $scooter->latitude }}, 
                        lng: {{ $scooter->longitude }} 
                    };

                    const map = new google.maps.Map(document.getElementById('scooter-detail-map'), {
                        zoom: 15,
                        center: position,
                        mapTypeId: 'roadmap',
                    });

                    // تحديد لون العلامة حسب الحالة
                    let iconColor = '#10b981'; // emerald (available)
                    @if($scooter->status === 'rented')
                        iconColor = '#3b82f6'; // blue
                    @elseif($scooter->status === 'charging')
                        iconColor = '#f59e0b'; // amber
                    @elseif($scooter->status === 'maintenance')
                        iconColor = '#ef4444'; // red
                    @endif

                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: '{{ $scooter->code }}',
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 12,
                            fillColor: iconColor,
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 3,
                        },
                        animation: google.maps.Animation.DROP
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="p-2 text-xs" dir="rtl">
                                <div class="font-semibold text-secondary mb-1">{{ $scooter->code }}</div>
                                <div class="text-gray-600">{{ trans('messages.Status') }}: <span class="font-medium">{{ trans('messages.' . ucfirst($scooter->status)) }}</span></div>
                                <div class="text-gray-600">{{ trans('messages.Battery') }}: <span class="font-medium">{{ $scooter->battery_percentage }}%</span></div>
                                <div class="text-gray-600">{{ trans('messages.Coordinates') }}: <span class="font-mono text-[10px]">{{ $scooter->latitude }}, {{ $scooter->longitude }}</span></div>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });

                    infoWindow.open(map, marker);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initScooterMap);
                } else {
                    initScooterMap();
                }
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            // Auto-update lock status every 3 seconds to sync with ESP32 updates
            let autoUpdateInterval;
            const scooterId = {{ $scooter->id }};
            
            function updateLockStatus() {
                // Only update if page is visible
                if (document.hidden) return;
                
                fetch(`/admin/scooters/${scooterId}/lock-status`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.is_locked !== undefined) {
                        // Update lock status display
                        const lockStatusElement = document.querySelector('[data-lock-status]');
                        if (lockStatusElement) {
                            const currentStatus = lockStatusElement.dataset.lockStatus === 'true';
                            if (currentStatus !== data.is_locked) {
                                // Status changed, reload page to show updated state
                                location.reload();
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating lock status:', error);
                });
            }
            
            function startAutoUpdate() {
                // Update every 3 seconds
                autoUpdateInterval = setInterval(updateLockStatus, 3000);
            }
            
            // Start auto-update when page loads
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startAutoUpdate);
            } else {
                startAutoUpdate();
            }
            
            // Stop auto-update when page is hidden
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(autoUpdateInterval);
                } else {
                    startAutoUpdate();
                }
            });
        </script>
    @endpush
</x-app-layout>



