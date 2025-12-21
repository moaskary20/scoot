<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Coupon Details') }}: {{ $coupon->code }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.View coupon details and usage statistics') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.coupons.edit', $coupon) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.Edit') }}
                </a>
                <a href="{{ route('admin.coupons.index') }}"
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

            <!-- Coupon Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Code') }}</div>
                    <div class="text-2xl font-bold font-mono text-secondary">
                        {{ $coupon->code }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Discount') }}</div>
                    <div class="text-2xl font-bold text-emerald-600">
                        @if($coupon->discount_type === 'percentage')
                            {{ $coupon->discount_value }}%
                        @else
                            {{ number_format($coupon->discount_value, 2) }} {{ trans('messages.EGP') }}
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Usage') }}</div>
                    <div class="text-2xl font-bold text-secondary">
                        {{ $coupon->usage_count }} / {{ $coupon->usage_limit ?: '∞' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $coupon->user_usage_limit }} {{ trans('messages.per user') }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                    <div>
                        @if($coupon->is_active && $coupon->isValid())
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-emerald-50 text-emerald-700">
                                {{ trans('messages.Valid') }}
                            </span>
                        @elseif(!$coupon->is_active)
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                                {{ trans('messages.Inactive') }}
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-red-50 text-red-700">
                                {{ trans('messages.Expired') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Coupon Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Coupon Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Name') }}</div>
                        <div class="font-semibold text-secondary">{{ $coupon->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Discount Type') }}</div>
                        <div class="text-gray-700">
                            {{ $coupon->discount_type === 'percentage' ? trans('messages.Percentage') : trans('messages.Fixed Amount') }}
                        </div>
                    </div>
                    @if($coupon->discount_type === 'percentage' && $coupon->max_discount)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Max Discount') }}</div>
                            <div class="text-gray-700">{{ number_format($coupon->max_discount, 2) }} {{ trans('messages.EGP') }}</div>
                        </div>
                    @endif
                    @if($coupon->min_amount > 0)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Min Amount') }}</div>
                            <div class="text-gray-700">{{ number_format($coupon->min_amount, 2) }} {{ trans('messages.EGP') }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Applicable To') }}</div>
                        <div class="text-gray-700">
                            @php
                                $applicableLabels = [
                                    'trips' => trans('messages.Trips Only'),
                                    'subscriptions' => trans('messages.Subscriptions Only'),
                                    'all' => trans('messages.All'),
                                ];
                            @endphp
                            {{ $applicableLabels[$coupon->applicable_to] ?? ucfirst($coupon->applicable_to) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Starts At') }}</div>
                        <div class="text-gray-700">
                            {{ $coupon->starts_at ? $coupon->starts_at->format('Y-m-d H:i') : '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Expires At') }}</div>
                        <div class="text-gray-700">
                            {{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d H:i') : '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Created At') }}</div>
                        <div class="text-gray-700">{{ $coupon->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>

            @if($coupon->description)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-2">{{ trans('messages.Description') }}</h3>
                    <div class="text-sm text-gray-700">{{ $coupon->description }}</div>
                </div>
            @endif

            <!-- Recent Usage -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Recent Usage') }} ({{ $coupon->trips_count }} {{ trans('messages.total') }})</h3>
                @if($recentTrips->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Trip ID') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.User') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Scooter') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Discount') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Date') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($recentTrips as $trip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-500">#{{ $trip->id }}</td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('admin.users.show', $trip->user) }}" class="text-secondary hover:text-primary">
                                            {{ $trip->user->name }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2">{{ $trip->scooter->code }}</td>
                                    <td class="px-3 py-2 font-semibold text-emerald-600">
                                        {{ number_format($trip->discount_amount, 2) }} {{ trans('messages.EGP') }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">{{ $trip->start_time->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('admin.trips.show', $trip) }}" class="text-primary hover:text-yellow-500">
                                            {{ trans('messages.View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-sm text-gray-500">
                        {{ trans('messages.No usage yet.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

