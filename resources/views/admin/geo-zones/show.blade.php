<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Geo Zone Details') }}: {{ $zone->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.View geo zone details on map') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.geo-zones.edit', $zone) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.Edit') }}
                </a>
                <a href="{{ route('admin.geo-zones.index') }}"
                   class="text-sm text-gray-600 hover:text-secondary">
                    {{ trans('messages.Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Name') }}</div>
                    <div class="font-semibold text-secondary">{{ $zone->name }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</div>
                    @php
                        $typeLabels = [
                            'allowed' => trans('messages.Allowed Zone'),
                            'forbidden' => trans('messages.Forbidden Zone'),
                            'parking' => trans('messages.Parking Zone'),
                        ];
                    @endphp
                    <div class="font-semibold text-secondary">
                        {{ $typeLabels[$zone->type] ?? $zone->type }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Color') }}</div>
                    <div class="flex items-center gap-2">
                        <span class="h-4 w-4 rounded-full border" style="background-color: {{ $zone->color }}"></span>
                        <span class="text-xs text-gray-700">{{ $zone->color }}</span>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                    <div>
                        @if($zone->is_active)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                {{ trans('messages.Active') }}
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">
                                {{ trans('messages.Inactive') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Center Coordinates') }}</div>
                    <div class="text-xs text-gray-700 font-mono">
                        @if($zone->center_latitude && $zone->center_longitude)
                            {{ $zone->center_latitude }}, {{ $zone->center_longitude }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Last Updated') }}</div>
                    <div class="text-xs text-gray-700">
                        {{ $zone->updated_at?->format('Y-m-d H:i') }}
                    </div>
                </div>
            </div>

            @if($zone->description)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-2">{{ trans('messages.Description') }}</h3>
                    <div class="text-sm text-gray-700">
                        {{ $zone->description }}
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-secondary">
                            {{ trans('messages.Zone on Map') }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ trans('messages.View zone boundaries as drawn on Google Maps') }}
                        </p>
                    </div>
                </div>

                <div id="geo-zone-map" class="w-full aspect-[16/9] rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden"></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', 'YOUR_API_KEY') }}"></script>
        <script>
            function initGeoZoneShowMap() {
                const polygonData = @json($zone->polygon ?? []);
                let defaultCenter = { lat: 30.0444, lng: 31.2357 };

                if (polygonData.length > 0) {
                    defaultCenter = { lat: polygonData[0].lat, lng: polygonData[0].lng };
                }

                const map = new google.maps.Map(document.getElementById('geo-zone-map'), {
                    zoom: 13,
                    center: defaultCenter,
                    mapTypeId: 'roadmap',
                });

                if (polygonData.length > 0) {
                    const path = polygonData.map(p => ({ lat: p.lat, lng: p.lng }));
                    const polygon = new google.maps.Polygon({
                        paths: path,
                        strokeColor: '{{ $zone->color }}',
                        strokeOpacity: 0.9,
                        strokeWeight: 2,
                        fillColor: '{{ $zone->color }}',
                        fillOpacity: 0.25,
                        map: map,
                    });

                    const bounds = new google.maps.LatLngBounds();
                    path.forEach(p => bounds.extend(p));
                    map.fitBounds(bounds);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initGeoZoneShowMap);
            } else {
                initGeoZoneShowMap();
            }
        </script>
    @endpush
</x-app-layout>


