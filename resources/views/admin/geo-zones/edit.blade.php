<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Edit Geo Zone') }}: {{ $zone->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تعديل بيانات المنطقة الجغرافية') }}
                </p>
            </div>
            <a href="{{ route('admin.geo-zones.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع لقائمة المناطق') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <form action="{{ route('admin.geo-zones.update', $zone) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('admin.geo-zones._form', ['zone' => $zone])

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-xs text-gray-500">
                            {{ trans('messages.آخر تحديث:') }}
                            <span class="font-semibold text-secondary">
                                {{ $zone->updated_at?->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.geo-zones.show', $zone) }}"
                               class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                                {{ trans('messages.عرض على الخريطة') }}
                            </a>
                            <button type="submit"
                                    class="px-5 py-2 rounded-lg bg-primary text-secondary text-sm font-semibold shadow-sm hover:bg-yellow-400 transition">
                                {{ trans('messages.حفظ التغييرات') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', 'YOUR_API_KEY') }}&libraries=drawing"></script>
        <script>
            let map;
            let drawingManager;
            let activePolygon = null;

            function initGeoZoneMap() {
                const savedPolygon = document.getElementById('polygon-input').value;
                let defaultCenter = { lat: 30.0444, lng: 31.2357 };

                try {
                    if (savedPolygon) {
                        const points = JSON.parse(savedPolygon);
                        if (points.length > 0) {
                            defaultCenter = { lat: points[0].lat, lng: points[0].lng };
                        }
                    }
                } catch (e) {
                    console.error('Invalid polygon JSON', e);
                }

                map = new google.maps.Map(document.getElementById('geo-zone-map'), {
                    zoom: 13,
                    center: defaultCenter,
                    mapTypeId: 'roadmap',
                });

                if (savedPolygon) {
                    try {
                        const points = JSON.parse(savedPolygon);
                        const path = points.map(p => ({ lat: p.lat, lng: p.lng }));
                        activePolygon = new google.maps.Polygon({
                            paths: path,
                            strokeColor: '#000000',
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: '#FFD600',
                            fillOpacity: 0.25,
                            editable: true,
                            map: map,
                        });
                        const bounds = new google.maps.LatLngBounds();
                        path.forEach(p => bounds.extend(p));
                        map.fitBounds(bounds);
                        attachPolygonEvents(activePolygon);
                    } catch (e) {
                        console.error('Invalid polygon JSON', e);
                    }
                }

                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: null,
                    drawingControl: true,
                    drawingControlOptions: {
                        position: google.maps.ControlPosition.TOP_CENTER,
                        drawingModes: ['polygon'],
                    },
                    polygonOptions: {
                        strokeColor: '#000000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#FFD600',
                        fillOpacity: 0.25,
                        editable: true,
                    },
                });

                drawingManager.setMap(map);

                google.maps.event.addListener(drawingManager, 'overlaycomplete', function (event) {
                    if (event.type === google.maps.drawing.OverlayType.POLYGON) {
                        if (activePolygon) {
                            activePolygon.setMap(null);
                        }
                        activePolygon = event.overlay;
                        drawingManager.setDrawingMode(null);
                        savePolygon(activePolygon);
                        attachPolygonEvents(activePolygon);
                    }
                });
            }

            function attachPolygonEvents(polygon) {
                const path = polygon.getPath();
                google.maps.event.addListener(path, 'set_at', () => savePolygon(polygon));
                google.maps.event.addListener(path, 'insert_at', () => savePolygon(polygon));
                google.maps.event.addListener(path, 'remove_at', () => savePolygon(polygon));
            }

            function savePolygon(polygon) {
                const path = polygon.getPath();
                const points = [];
                const bounds = new google.maps.LatLngBounds();

                for (let i = 0; i < path.getLength(); i++) {
                    const point = path.getAt(i);
                    points.push({ lat: point.lat(), lng: point.lng() });
                    bounds.extend(point);
                }

                document.getElementById('polygon-input').value = JSON.stringify(points);

                const center = bounds.getCenter();
                document.getElementById('center-lat-input').value = center.lat();
                document.getElementById('center-lng-input').value = center.lng();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initGeoZoneMap);
            } else {
                initGeoZoneMap();
            }
        </script>
    @endpush
</x-app-layout>


