<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Scooter Admin Dashboard') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.لوحة تحكم نظام LINER SCOOT') }}
                </p>
            </div>
            <span class="inline-flex items-center px-4 py-1 rounded-full text-xs font-semibold bg-primary text-secondary shadow-sm">
                {{ trans('messages.Live Operations') }}
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Total Scooters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            {{ trans('messages.Total Scooters') }}
                        </span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary/20 text-secondary text-xs font-semibold">
                            {{ trans('messages.S') }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-secondary">
                            {{ $stats['total_scooters'] }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ trans('messages.سكوتر') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ trans('messages.السكوترات النشطة حالياً') }}</span>
                        <span class="text-emerald-500 font-semibold">{{ $stats['active_scooters'] }} {{ trans('messages.Online') }}</span>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            {{ trans('messages.Total Users') }}
                        </span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-secondary text-primary text-xs font-semibold">
                            {{ trans('messages.U') }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-secondary">
                            {{ $stats['total_users'] }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ trans('messages.مستخدم') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ trans('messages.مستخدمون موثَّقون') }}</span>
                        <span class="text-primary font-semibold">{{ $stats['active_users'] }} {{ trans('messages.Active') }}</span>
                    </div>
                </div>

                <!-- Trips Today -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            {{ trans('messages.Trips Today') }}
                        </span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary text-secondary text-xs font-semibold">
                            {{ trans('messages.T') }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-secondary">
                            {{ $stats['trips_today'] }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ trans('messages.رحلة') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ trans('messages.متوسط مدة الرحلة') }}</span>
                        <span class="font-semibold text-gray-700">- {{ trans('messages.دقيقة') }}</span>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="bg-secondary rounded-xl shadow-sm p-5 flex flex-col gap-3 text-primary">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wide">
                            {{ trans('messages.Today Revenue') }}
                        </span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary text-secondary text-xs font-semibold">
                            {{ trans('messages.₤') }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold">
                            {{ number_format($stats['revenue_today'], 2) }}
                        </span>
                        <span class="text-xs opacity-80">
                            {{ trans('messages.EGP') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs opacity-80">
                        <span>{{ trans('messages.إجمالي الشهر الحالي') }}</span>
                        <span class="font-semibold">{{ number_format($stats['revenue_month'], 2) }} {{ trans('messages.EGP') }}</span>
                    </div>
                </div>
            </div>

            <!-- Map + Live Stats -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Map Placeholder -->
                <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-secondary">
                                {{ trans('messages.Scooters Live Map') }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ trans('messages.خريطة مباشرة توضح مواقع السكوترات (سيتم ربطها بـ GPS لاحقاً)') }}
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-medium bg-primary/20 text-secondary">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ trans('messages.Live GPS') }}
                        </span>
                    </div>
                    <div class="relative mt-1">
                        <div id="scooters-map" class="aspect-[16/9] w-full rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden"></div>
                    </div>
                </div>

                <!-- Live operational stats -->
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">
                        <h3 class="text-sm font-semibold text-secondary">
                            {{ trans('messages.Live Operations Snapshot') }}
                        </h3>
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">{{ trans('messages.Active Trips') }}</span>
                                <span class="font-semibold text-secondary">{{ $stats['active_trips'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">{{ trans('messages.Scooters Charging') }}</span>
                                <span class="font-semibold text-amber-500">{{ $stats['charging_scooters'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">{{ trans('messages.In Maintenance') }}</span>
                                <span class="font-semibold text-red-500">{{ $stats['maintenance_scooters'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">{{ trans('messages.Zone Exit Alerts (Today)') }}</span>
                                <span class="font-semibold text-rose-500">{{ $stats['zone_exit_alerts'] }}</span>
                            </div>
                        </div>
                    </div>

                    @if($stats['critical_alerts'] > 0)
                        <a href="{{ route('admin.scooter-logs.critical') }}" class="block bg-red-50 border-2 border-red-200 rounded-2xl shadow-sm p-4 space-y-3 hover:bg-red-100 transition">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-red-800">
                                    {{ trans('messages.Critical Alerts') }}
                                </h3>
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-red-700">{{ trans('messages.Unresolved Critical Issues') }}</span>
                                <span class="text-2xl font-bold text-red-600">{{ $stats['critical_alerts'] }}</span>
                            </div>
                            <div class="text-xs text-red-600 font-medium">
                                {{ trans('messages.Click to view →') }}
                            </div>
                        </a>
                    @endif

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">
                        <h3 class="text-sm font-semibold text-secondary">
                            {{ trans('messages.Quick Actions') }}
                        </h3>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <a href="{{ route('admin.scooters.create') }}" class="flex flex-col items-center justify-center gap-1 px-3 py-3 rounded-xl border border-gray-200 hover:border-primary hover:bg-primary/5 transition">
                                <span class="h-8 w-8 rounded-full bg-primary/20 text-secondary flex items-center justify-center text-sm font-semibold">
                                    +
                                </span>
                                <span class="font-medium text-secondary">{{ trans('messages.إضافة سكوتر') }}</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center justify-center gap-1 px-3 py-3 rounded-xl border border-gray-200 hover:border-primary hover:bg-primary/5 transition">
                                <span class="h-8 w-8 rounded-full bg-secondary text-primary flex items-center justify-center text-sm font-semibold">
                                    ₤
                                </span>
                                <span class="font-medium text-secondary">{{ trans('messages.إدارة المستخدمين') }}</span>
                            </a>
                        </div>
                        <div class="pt-2 border-t border-gray-200">
                            <a href="{{ route('admin.reports.index') }}" class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-primary/10 text-secondary hover:bg-primary/20 transition text-xs font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                {{ trans('messages.التقارير والإحصائيات') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', 'YOUR_API_KEY') }}&libraries=geometry"></script>
        <script>
            let map;
            let markers = [];

            function initMap() {
                // Default center (Cairo, Egypt) - يمكن تغييره حسب منطقتك
                const defaultCenter = { lat: 30.0444, lng: 31.2357 };
                
                map = new google.maps.Map(document.getElementById('scooters-map'), {
                    zoom: 12,
                    center: defaultCenter,
                    mapTypeId: 'roadmap',
                    styles: [
                        {
                            featureType: 'poi',
                            elementType: 'labels',
                            stylers: [{ visibility: 'off' }]
                        }
                    ]
                });

                loadScooters();
                
                // تحديث الخريطة كل 30 ثانية
                setInterval(loadScooters, 30000);
            }

            function loadScooters() {
                fetch('{{ route('admin.scooters.map-data') }}')
                    .then(response => response.json())
                    .then(data => {
                        // حذف العلامات القديمة
                        markers.forEach(marker => marker.setMap(null));
                        markers = [];

                        if (data.scooters && data.scooters.length > 0) {
                            const bounds = new google.maps.LatLngBounds();

                            data.scooters.forEach(scooter => {
                                const position = { lat: scooter.latitude, lng: scooter.longitude };
                                
                                // تحديد لون العلامة حسب الحالة
                                let iconColor = '#10b981'; // emerald (available)
                                if (scooter.status === 'rented') iconColor = '#3b82f6'; // blue
                                else if (scooter.status === 'charging') iconColor = '#f59e0b'; // amber
                                else if (scooter.status === 'maintenance') iconColor = '#ef4444'; // red

                                // إنشاء أيقونة سكوتر مخصصة باستخدام SVG
                                // شكل سكوتر بسيط: عجلة أمامية، مقبض، جسم، عجلة خلفية
                                const scooterIcon = {
                                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="20" cy="20" r="18" fill="${iconColor}" stroke="#ffffff" stroke-width="2.5"/>
                                            <!-- عجلة أمامية -->
                                            <circle cx="12" cy="15" r="3" fill="#ffffff"/>
                                            <!-- مقبض -->
                                            <line x1="12" y1="15" x2="8" y2="10" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                                            <!-- جسم السكوتر -->
                                            <rect x="12" y="15" width="12" height="4" rx="2" fill="#ffffff"/>
                                            <!-- عجلة خلفية -->
                                            <circle cx="24" cy="19" r="3" fill="#ffffff"/>
                                        </svg>
                                    `),
                                    scaledSize: new google.maps.Size(40, 40),
                                    anchor: new google.maps.Point(20, 20),
                                };

                                // إنشاء علامة مخصصة
                                const marker = new google.maps.Marker({
                                    position: position,
                                    map: map,
                                    title: `Scooter ${scooter.code} - ${scooter.status}`,
                                    icon: scooterIcon,
                                    animation: google.maps.Animation.DROP
                                });

                                // إنشاء Label أسفل العلامة
                                class ScooterLabel extends google.maps.OverlayView {
                                    constructor(position, text, map) {
                                        super();
                                        this.position = position;
                                        this.text = text;
                                        this.setMap(map);
                                    }

                                    onAdd() {
                                        this.div = document.createElement('div');
                                        this.div.className = 'scooter-label';
                                        this.div.style.cssText = `
                                            position: absolute;
                                            background-color: rgba(255, 255, 255, 0.95);
                                            padding: 3px 8px;
                                            border-radius: 4px;
                                            border: 1px solid #ddd;
                                            font-family: 'Tajawal', sans-serif;
                                            font-size: 11px;
                                            font-weight: bold;
                                            color: #000;
                                            white-space: nowrap;
                                            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                                            pointer-events: none;
                                        `;
                                        this.div.textContent = this.text;
                                        
                                        const panes = this.getPanes();
                                        panes.overlayMouseTarget.appendChild(this.div);
                                    }

                                    draw() {
                                        const overlayProjection = this.getProjection();
                                        const position = overlayProjection.fromLatLngToDivPixel(this.position);
                                        
                                        this.div.style.left = (position.x - this.div.offsetWidth / 2) + 'px';
                                        this.div.style.top = (position.y + 20) + 'px';
                                    }

                                    onRemove() {
                                        if (this.div && this.div.parentNode) {
                                            this.div.parentNode.removeChild(this.div);
                                        }
                                    }
                                }

                                const label = new ScooterLabel(position, scooter.code, map);

                                // Info Window
                                const infoContent = `
                                    <div class="p-2 text-xs">
                                        <div class="font-semibold text-secondary mb-1">${scooter.code}</div>
                                        <div class="text-gray-600">Status: <span class="font-medium">${scooter.status}</span></div>
                                        <div class="text-gray-600">Battery: <span class="font-medium">${scooter.battery_percentage}%</span></div>
                                        <div class="text-gray-600">Locked: <span class="font-medium">${scooter.is_locked ? 'Yes' : 'No'}</span></div>
                                    </div>
                                `;
                                
                                const infoWindow = new google.maps.InfoWindow({
                                    content: infoContent
                                });

                                marker.addListener('click', () => {
                                    infoWindow.open(map, marker);
                                });

                                markers.push(marker);
                                bounds.extend(position);
                            });

                            // ضبط الخريطة لتشمل جميع السكوترات
                            if (data.scooters.length === 1) {
                                map.setCenter(position);
                                map.setZoom(15);
                            } else {
                                map.fitBounds(bounds);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading scooters:', error);
                    });
            }

            // تهيئة الخريطة عند تحميل الصفحة
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMap);
            } else {
                initMap();
            }
        </script>
    @endpush
</x-app-layout>
