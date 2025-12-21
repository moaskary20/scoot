<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Trip Settings') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.Manage trip limits and restrictions') }}
                </p>
            </div>
            <a href="{{ route('admin.trips.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.Back to Trips') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" dir="rtl">
                <form action="{{ route('admin.trips.settings.update') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- الإعدادات الأساسية -->
                        <div>
                            <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Basic Trip Limits') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="max_trip_duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Trip Duration (minutes)') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="1" name="max_trip_duration_minutes" id="max_trip_duration_minutes"
                                           value="{{ old('max_trip_duration_minutes', $settings['max_trip_duration_minutes'] ?? 120) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum trip duration in minutes') }}
                                    </p>
                                    @error('max_trip_duration_minutes')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_trip_cost" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Trip Cost (EGP)') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" name="max_trip_cost" id="max_trip_cost"
                                           value="{{ old('max_trip_cost', $settings['max_trip_cost'] ?? 500) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum trip cost in EGP') }}
                                    </p>
                                    @error('max_trip_cost')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_trips_per_day" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Trips Per Day') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="1" name="max_trips_per_day" id="max_trips_per_day"
                                           value="{{ old('max_trips_per_day', $settings['max_trips_per_day'] ?? 10) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum number of trips per day per user') }}
                                    </p>
                                    @error('max_trips_per_day')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_coupon_uses_per_month" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Coupon Uses Per Month') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" name="max_coupon_uses_per_month" id="max_coupon_uses_per_month"
                                           value="{{ old('max_coupon_uses_per_month', $settings['max_coupon_uses_per_month'] ?? 5) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum coupon uses per month per user') }}
                                    </p>
                                    @error('max_coupon_uses_per_month')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="max_penalties_before_account_suspension" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Penalties Before Account Suspension') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" name="max_penalties_before_account_suspension" id="max_penalties_before_account_suspension"
                                           value="{{ old('max_penalties_before_account_suspension', $settings['max_penalties_before_account_suspension'] ?? 3) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum number of penalties before account suspension') }}
                                    </p>
                                    @error('max_penalties_before_account_suspension')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- إعدادات إضافية -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Additional Settings') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="max_trip_distance_km" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Trip Distance (km)') }}
                                    </label>
                                    <input type="number" step="0.1" min="0" name="max_trip_distance_km" id="max_trip_distance_km"
                                           value="{{ old('max_trip_distance_km', $settings['max_trip_distance_km'] ?? 50) }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum trip distance in kilometers') }}
                                    </p>
                                </div>

                                <div>
                                    <label for="min_trip_duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Min Trip Duration (minutes)') }}
                                    </label>
                                    <input type="number" min="0" name="min_trip_duration_minutes" id="min_trip_duration_minutes"
                                           value="{{ old('min_trip_duration_minutes', $settings['min_trip_duration_minutes'] ?? 1) }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Minimum trip duration in minutes') }}
                                    </p>
                                </div>

                                <div>
                                    <label for="min_trip_cost" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Min Trip Cost (EGP)') }}
                                    </label>
                                    <input type="number" step="0.01" min="0" name="min_trip_cost" id="min_trip_cost"
                                           value="{{ old('min_trip_cost', $settings['min_trip_cost'] ?? 5) }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Minimum trip cost in EGP') }}
                                    </p>
                                </div>

                                <div>
                                    <label for="max_discount_percentage" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Discount Percentage (%)') }}
                                    </label>
                                    <input type="number" min="0" max="100" name="max_discount_percentage" id="max_discount_percentage"
                                           value="{{ old('max_discount_percentage', $settings['max_discount_percentage'] ?? 50) }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum discount percentage allowed') }}
                                    </p>
                                </div>

                                <div>
                                    <label for="max_penalty_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Max Penalty Amount (EGP)') }}
                                    </label>
                                    <input type="number" step="0.01" min="0" name="max_penalty_amount" id="max_penalty_amount"
                                           value="{{ old('max_penalty_amount', $settings['max_penalty_amount'] ?? 100) }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Maximum penalty amount in EGP') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- إعدادات التحذيرات -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Warning Settings') }}</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="enable_trip_duration_warning" id="enable_trip_duration_warning" value="1"
                                           {{ old('enable_trip_duration_warning', $settings['enable_trip_duration_warning'] ?? '1') ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                    <label for="enable_trip_duration_warning" class="mr-2 block text-sm text-gray-700">
                                        {{ trans('messages.Enable Trip Duration Warning') }}
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mr-6">
                                    {{ trans('messages.Send notification when trip duration approaches the maximum limit') }}
                                </p>

                                <div>
                                    <label for="trip_duration_warning_threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Trip Duration Warning Threshold (%)') }}
                                    </label>
                                    <input type="number" min="0" max="100" name="trip_duration_warning_threshold" id="trip_duration_warning_threshold"
                                           value="{{ old('trip_duration_warning_threshold', $settings['trip_duration_warning_threshold'] ?? 90) }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Show warning when trip duration reaches this percentage of maximum') }}
                                    </p>
                                </div>

                                <div class="flex items-center mt-4">
                                    <input type="checkbox" name="enable_cost_warning" id="enable_cost_warning" value="1"
                                           {{ old('enable_cost_warning', $settings['enable_cost_warning'] ?? '1') ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                    <label for="enable_cost_warning" class="mr-2 block text-sm text-gray-700">
                                        {{ trans('messages.Enable Cost Warning') }}
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mr-6">
                                    {{ trans('messages.Send notification when trip cost approaches the maximum limit') }}
                                </p>

                                <div>
                                    <label for="cost_warning_threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Cost Warning Threshold (%)') }}
                                    </label>
                                    <input type="number" min="0" max="100" name="cost_warning_threshold" id="cost_warning_threshold"
                                           value="{{ old('cost_warning_threshold', $settings['cost_warning_threshold'] ?? 80) }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Show warning when trip cost reaches this percentage of maximum') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.trips.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

