<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Start New Trip') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.بدء رحلة جديدة') }}
                </p>
            </div>
            <a href="{{ route('admin.trips.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع لقائمة الرحلات') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.trips.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.User') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary user-select">
                                <option value="">{{ trans('messages.Select User') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-phone="{{ $user->phone ?? '' }}"
                                            data-university="{{ $user->university_id ?? '' }}">
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="scooter_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Scooter') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="scooter_id" id="scooter_id" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">{{ trans('messages.Select Scooter') }}</option>
                                @foreach($scooters as $scooter)
                                    <option value="{{ $scooter->id }}" {{ old('scooter_id') == $scooter->id ? 'selected' : '' }}>
                                        {{ $scooter->code }} ({{ $scooter->status }}, Battery: {{ $scooter->battery_percentage }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('scooter_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="geo_zone_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Geo Zone') }} ({{ trans('messages.Optional') }})
                            </label>
                            <select name="geo_zone_id" id="geo_zone_id"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">{{ trans('messages.Select Geo Zone') }}</option>
                                @foreach($geoZones as $geoZone)
                                    <option value="{{ $geoZone->id }}" {{ old('geo_zone_id') == $geoZone->id ? 'selected' : '' }}
                                            data-lat="{{ $geoZone->center_latitude }}"
                                            data-lng="{{ $geoZone->center_longitude }}">
                                        {{ $geoZone->name }} ({{ number_format($geoZone->price_per_minute, 2) }} {{ trans('messages.EGP') }}/{{ trans('messages.min') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ trans('messages.Selecting a geo zone will auto-fill start coordinates') }}</p>
                            @error('geo_zone_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="coupon_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Coupon') }} <span class="text-gray-400 text-xs">({{ trans('messages.Optional') }})</span>
                            </label>
                            <select name="coupon_id" id="coupon_id"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">{{ trans('messages.No Coupon') }}</option>
                                @foreach($coupons as $coupon)
                                    <option value="{{ $coupon->id }}" 
                                            {{ old('coupon_id') == $coupon->id ? 'selected' : '' }}
                                            data-code="{{ $coupon->code }}"
                                            data-name="{{ $coupon->name }}"
                                            data-discount-type="{{ $coupon->discount_type }}"
                                            data-discount-value="{{ $coupon->discount_value }}"
                                            data-max-discount="{{ $coupon->max_discount ?? '' }}"
                                            data-description="{{ $coupon->description ?? '' }}">
                                        {{ $coupon->code }} - {{ $coupon->name }}
                                        @if($coupon->discount_type === 'percentage')
                                            ({{ $coupon->discount_value }}%
                                            @if($coupon->max_discount)
                                                , {{ trans('messages.Max') }}: {{ number_format($coupon->max_discount, 2) }} {{ trans('messages.EGP') }}
                                            @endif
                                            )
                                        @else
                                            ({{ number_format($coupon->discount_value, 2) }} {{ trans('messages.EGP') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="coupon-details" class="mt-2 hidden p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm font-semibold text-emerald-900" id="coupon-name"></div>
                                </div>
                                <div class="text-sm text-emerald-700 font-medium" id="coupon-discount"></div>
                                <div class="text-xs text-emerald-600 mt-2" id="coupon-description"></div>
                            </div>
                            @error('coupon_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_latitude" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Start Latitude') }}
                            </label>
                            <input type="number" step="0.0000001" name="start_latitude" id="start_latitude"
                                   value="{{ old('start_latitude') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('start_latitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_longitude" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Start Longitude') }}
                            </label>
                            <input type="number" step="0.0000001" name="start_longitude" id="start_longitude"
                                   value="{{ old('start_longitude') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('start_longitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Notes') }}
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.trips.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.بدء الرحلة') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Translation variables
                const translations = {
                    coupon: '{{ trans('messages.Coupon') }}',
                    discount: '{{ trans('messages.Discount') }}',
                    max: '{{ trans('messages.Max') }}',
                    egp: '{{ trans('messages.EGP') }}'
                };

                // Initialize Select2 for user search
                $('#user_id').select2({
                    placeholder: '{{ trans('messages.Select User') }}',
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return '{{ trans('messages.No users found') }}';
                        },
                        searching: function() {
                            return '{{ trans('messages.Searching') }}...';
                        }
                    },
                    templateResult: function(user) {
                        if (!user.id) {
                            return user.text;
                        }
                        var $user = $('<span>' + user.text + '</span>');
                        return $user;
                    }
                });

                // Auto-fill coordinates when geo zone is selected
                $('#geo_zone_id').on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const lat = selectedOption.data('lat');
                    const lng = selectedOption.data('lng');
                    
                    if (lat && lng) {
                        $('#start_latitude').val(lat);
                        $('#start_longitude').val(lng);
                    }
                });

                // Show coupon details when coupon is selected
                function updateCouponDetails() {
                    const selectedOption = $('#coupon_id').find('option:selected');
                    const couponDetails = $('#coupon-details');
                    
                    if (selectedOption.val() && selectedOption.val() !== '') {
                        const code = selectedOption.data('code') || '';
                        const name = selectedOption.data('name') || '';
                        const discountType = selectedOption.data('discount-type') || '';
                        const discountValue = selectedOption.data('discount-value') || 0;
                        const maxDiscount = selectedOption.data('max-discount') || '';
                        const description = selectedOption.data('description') || '';
                        
                        // Update coupon name
                        $('#coupon-name').html('<strong>' + translations.coupon + ':</strong> ' + code + ' - ' + name);
                        
                        // Update discount info
                        let discountText = '';
                        if (discountType === 'percentage') {
                            discountText = '<strong>' + translations.discount + ':</strong> ' + parseFloat(discountValue).toFixed(0) + '%';
                            if (maxDiscount && maxDiscount !== '') {
                                discountText += ' (<strong>' + translations.max + ':</strong> ' + parseFloat(maxDiscount).toFixed(2) + ' ' + translations.egp + ')';
                            }
                        } else {
                            discountText = '<strong>' + translations.discount + ':</strong> ' + parseFloat(discountValue).toFixed(2) + ' ' + translations.egp;
                        }
                        $('#coupon-discount').html(discountText);
                        
                        // Update description
                        if (description && description !== '') {
                            $('#coupon-description').html('<em>' + description + '</em>');
                        } else {
                            $('#coupon-description').html('');
                        }
                        
                        couponDetails.removeClass('hidden').slideDown(200);
                    } else {
                        couponDetails.slideUp(200, function() {
                            $(this).addClass('hidden');
                        });
                    }
                }

                // Bind change event
                $('#coupon_id').on('change', updateCouponDetails);

                // Trigger on page load if coupon is already selected (from old input)
                if ($('#coupon_id').val()) {
                    updateCouponDetails();
                }
            });
        </script>
        <style>
            .select2-container {
                width: 100% !important;
            }
            .select2-container--default .select2-selection--single {
                height: 42px;
                border: 1px solid #d1d5db;
                border-radius: 0.5rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 42px;
                padding-left: 12px;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px;
            }
        </style>
    @endpush
</x-app-layout>

