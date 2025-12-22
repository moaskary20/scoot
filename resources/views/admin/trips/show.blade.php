<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Trip Details') }} #{{ $trip->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل الرحلة') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if($trip->status === 'active')
                    <form action="{{ route('admin.trips.complete', $trip) }}" method="POST" class="inline-block" id="complete-trip-form">
                        @csrf
                        <input type="hidden" name="end_latitude" id="complete-end-latitude">
                        <input type="hidden" name="end_longitude" id="complete-end-longitude">
                        <button type="submit" class="px-4 py-2 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600"
                                onclick="return confirm('{{ trans('messages.هل أنت متأكد من إنهاء هذه الرحلة؟') }}')">
                            {{ trans('messages.Complete Trip') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.trips.edit', $trip) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.Edit') }}
                </a>
                <a href="{{ route('admin.trips.index') }}"
                   class="text-sm text-gray-600 hover:text-secondary">
                    {{ trans('messages.Back') }}
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

            @if (session('error'))
                <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Trip Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                    @php
                        $statusColors = [
                            'active' => 'bg-blue-50 text-blue-700',
                            'completed' => 'bg-emerald-50 text-emerald-700',
                            'cancelled' => 'bg-red-50 text-red-700',
                        ];
                    @endphp
                    <div>
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$trip->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($trip->status) }}
                        </span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Duration') }}</div>
                    <div class="text-2xl font-bold text-secondary">
                        @if($trip->status === 'active')
                            <div id="trip-timer" class="text-blue-600">
                                <span id="trip-duration-hours">00</span>:<span id="trip-duration-minutes">00</span>:<span id="trip-duration-seconds">00</span>
                            </div>
                        @elseif($trip->duration_minutes)
                            {{ $trip->duration_minutes }} {{ trans('messages.min') }}
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Cost') }}</div>
                    @php
                        $paymentStatus = $trip->payment_status;
                        $isFullyPaid = $paymentStatus === 'paid';
                        $costColor = $isFullyPaid ? 'text-emerald-600' : 'text-red-600';
                    @endphp
                    <div class="text-2xl font-bold {{ $costColor }}">
                        @if($trip->status === 'active')
                            <div id="trip-cost">{{ number_format($trip->cost, 2) }}</div>
                        @else
                            {{ number_format($trip->cost, 2) }}
                        @endif
                        {{ trans('messages.EGP') }}
                    </div>
                    @if(!$isFullyPaid && $trip->status === 'completed')
                        <div class="text-xs text-red-600 mt-1">
                            @if($paymentStatus === 'partially_paid')
                                {{ trans('messages.Partially Paid') }}: {{ number_format($trip->paid_amount, 2) }} {{ trans('messages.EGP') }}
                            @else
                                {{ trans('messages.Unpaid') }}
                            @endif
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Zone Exit') }}</div>
                    <div>
                        @if($trip->zone_exit_detected)
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-rose-50 text-rose-700">
                                {{ trans('messages.Detected') }}
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-emerald-50 text-emerald-700">
                                {{ trans('messages.No') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Trip Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Trip Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.User') }}</div>
                        <div class="font-semibold text-secondary">
                            <a href="{{ route('admin.users.show', $trip->user) }}" class="hover:text-primary">
                                {{ $trip->user->name }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-500">{{ $trip->user->email }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Scooter') }}</div>
                        <div class="font-semibold text-secondary">
                            <a href="{{ route('admin.scooters.show', $trip->scooter) }}" class="hover:text-primary">
                                {{ $trip->scooter->code }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-500">Battery: {{ $trip->scooter->battery_percentage }}%</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Start Time') }}</div>
                        <div class="text-gray-700">{{ $trip->start_time->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.End Time') }}</div>
                        <div class="text-gray-700">
                            {{ $trip->end_time ? $trip->end_time->format('Y-m-d H:i:s') : '-' }}
                        </div>
                    </div>
                    @if($trip->start_latitude && $trip->start_longitude)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Start Location') }}</div>
                            <div class="text-gray-700 font-mono text-xs">
                                {{ $trip->start_latitude }}, {{ $trip->start_longitude }}
                            </div>
                        </div>
                    @endif
                    @if($trip->end_latitude && $trip->end_longitude)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.End Location') }}</div>
                            <div class="text-gray-700 font-mono text-xs">
                                {{ $trip->end_latitude }}, {{ $trip->end_longitude }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- End Image -->
            @if($trip->end_image)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.End Image') }} / صورة نهاية الرحلة</h3>
                    <div class="relative">
                        <img src="{{ asset('storage/' . $trip->end_image) }}" 
                             alt="Trip End Image" 
                             class="rounded-lg w-full max-w-md h-auto shadow-md cursor-pointer"
                             onclick="window.open('{{ asset('storage/' . $trip->end_image) }}', '_blank')">
                        <div class="mt-2 text-xs text-gray-500">
                            اضغط على الصورة لعرضها بحجم كامل
                        </div>
                    </div>
                </div>
            @endif

            <!-- Cost Breakdown -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Cost Breakdown') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Base Cost') }}</div>
                        <div class="font-semibold text-secondary">{{ number_format($trip->base_cost, 2) }} {{ trans('messages.EGP') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Discount') }}</div>
                        <div class="font-semibold text-emerald-600">-{{ number_format($trip->discount_amount, 2) }} {{ trans('messages.EGP') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Penalty') }}</div>
                        <div class="font-semibold text-red-600">+{{ number_format($trip->penalty_amount, 2) }} {{ trans('messages.EGP') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total') }}</div>
                        @php
                            $paymentStatus = $trip->payment_status;
                            $isFullyPaid = $paymentStatus === 'paid';
                            $totalColor = $isFullyPaid ? 'text-secondary' : 'text-red-600';
                        @endphp
                        <div class="text-lg font-bold {{ $totalColor }}">{{ number_format($trip->cost, 2) }} {{ trans('messages.EGP') }}</div>
                        @if(!$isFullyPaid && $trip->status === 'completed')
                            <div class="text-xs {{ $totalColor }} mt-1">
                                @if($paymentStatus === 'partially_paid')
                                    ({{ trans('messages.Paid') }}: {{ number_format($trip->paid_amount, 2) }} {{ trans('messages.EGP') }})
                                @else
                                    ({{ trans('messages.Unpaid') }})
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Zone Exit Details -->
            @if($trip->zone_exit_detected)
                <div class="bg-rose-50 rounded-xl shadow-sm border border-rose-200 p-6">
                    <h3 class="text-sm font-semibold text-rose-700 mb-2">{{ trans('messages.Zone Exit Detected') }}</h3>
                    <div class="text-xs text-rose-600">
                        @if($trip->zone_exit_details)
                            {{ $trip->zone_exit_details }}
                        @else
                            {{ trans('messages.User exited the allowed zone during this trip.') }}
                        @endif
                    </div>
                </div>
            @endif

            <!-- Coupon & Penalty -->
            @if($trip->coupon || $trip->penalty)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Related Items') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        @if($trip->coupon)
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Coupon Used') }}</div>
                                <div class="font-semibold text-secondary">{{ $trip->coupon->code ?? 'N/A' }}</div>
                            </div>
                        @endif
                        @if($trip->penalty)
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Penalty Applied') }}</div>
                                <div class="font-semibold text-red-600">
                                    @php
                                        $penaltyTypes = [
                                            'zone_exit' => trans('messages.Zone Exit'),
                                            'forbidden_parking' => trans('messages.Forbidden Parking'),
                                            'unlocked_scooter' => trans('messages.Unlocked Scooter'),
                                            'other' => trans('messages.Other'),
                                        ];
                                    @endphp
                                    {{ $penaltyTypes[$trip->penalty->type] ?? $trip->penalty->type ?? 'N/A' }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($trip->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-2">{{ trans('messages.Notes') }}</h3>
                    <div class="text-sm text-gray-700">{{ $trip->notes }}</div>
                </div>
            @endif
        </div>
    </div>

    @if($trip->status === 'active')
        @push('scripts')
            <script>
                (function() {
                    const startTime = new Date('{{ $trip->start_time->toIso8601String() }}');
                    @if($geoZone)
                    const pricePerMinute = {{ $geoZone->price_per_minute }};
                    const tripStartFee = {{ $geoZone->trip_start_fee }};
                    const hasPricing = true;
                    @else
                    const pricePerMinute = 0;
                    const tripStartFee = 0;
                    const hasPricing = false;
                    @endif
                    
                    function updateTimer() {
                        const now = new Date();
                        const diff = Math.floor((now - startTime) / 1000); // difference in seconds
                        
                        // Update duration timer
                        const hours = Math.floor(diff / 3600);
                        const minutes = Math.floor((diff % 3600) / 60);
                        const seconds = diff % 60;
                        
                        const hoursElement = document.getElementById('trip-duration-hours');
                        const minutesElement = document.getElementById('trip-duration-minutes');
                        const secondsElement = document.getElementById('trip-duration-seconds');
                        
                        if (hoursElement) {
                            hoursElement.textContent = String(hours).padStart(2, '0');
                        }
                        if (minutesElement) {
                            minutesElement.textContent = String(minutes).padStart(2, '0');
                        }
                        if (secondsElement) {
                            secondsElement.textContent = String(seconds).padStart(2, '0');
                        }
                        
                        // Calculate and update cost based on duration and pricing
                        const costElement = document.getElementById('trip-cost');
                        if (costElement) {
                            if (hasPricing && pricePerMinute > 0) {
                                const totalMinutes = diff / 60;
                                const calculatedCost = tripStartFee + (totalMinutes * pricePerMinute);
                                costElement.textContent = calculatedCost.toFixed(2);
                            } else {
                                // If no pricing, keep showing the base cost
                                costElement.textContent = {{ number_format($trip->cost, 2) }};
                            }
                        }
                    }
                    
                    // Update immediately when page loads
                    updateTimer();
                    
                    // Update every second continuously
                    setInterval(updateTimer, 1000);
                    
                    // Handle form submission - try to get current location
                    const completeForm = document.getElementById('complete-trip-form');
                    if (completeForm) {
                        completeForm.addEventListener('submit', function(e) {
                            // Try to get current location if available
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    function(position) {
                                        document.getElementById('complete-end-latitude').value = position.coords.latitude;
                                        document.getElementById('complete-end-longitude').value = position.coords.longitude;
                                        completeForm.submit();
                                    },
                                    function(error) {
                                        // If geolocation fails, submit without coordinates
                                        completeForm.submit();
                                    },
                                    { timeout: 2000 }
                                );
                                e.preventDefault();
                            }
                        });
                    }
                })();
            </script>
        @endpush
    @endif
</x-app-layout>

