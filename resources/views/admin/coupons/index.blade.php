<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Coupons') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع الكوبونات في النظام') }}
                </p>
            </div>
            <a href="{{ route('admin.coupons.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إضافة كوبون جديد') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</label>
                        <select name="is_active" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ trans('messages.Active') }}</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ trans('messages.Inactive') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Discount Type') }}</label>
                        <select name="discount_type" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>{{ trans('messages.Percentage') }}</option>
                            <option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>{{ trans('messages.Fixed') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Applicable To') }}</label>
                        <select name="applicable_to" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="trips" {{ request('applicable_to') === 'trips' ? 'selected' : '' }}>{{ trans('messages.Trips') }}</option>
                            <option value="subscriptions" {{ request('applicable_to') === 'subscriptions' ? 'selected' : '' }}>{{ trans('messages.Subscriptions') }}</option>
                            <option value="all" {{ request('applicable_to') === 'all' ? 'selected' : '' }}>{{ trans('messages.All') }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg hover:bg-yellow-400">
                            {{ trans('messages.Filter') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Code') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Name') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Discount') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Usage') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Valid Period') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $coupon->id }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono font-semibold text-secondary">{{ $coupon->code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-secondary">{{ $coupon->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    @php
                                        $applicableLabels = [
                                            'trips' => trans('messages.Trips only'),
                                            'subscriptions' => trans('messages.Subscriptions only'),
                                            'all' => trans('messages.All'),
                                        ];
                                    @endphp
                                    {{ $applicableLabels[$coupon->applicable_to] ?? ucfirst($coupon->applicable_to) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($coupon->discount_type === 'percentage')
                                    <span class="font-semibold text-emerald-600">
                                        {{ $coupon->discount_value }}%
                                    </span>
                                    @if($coupon->max_discount)
                                        <div class="text-xs text-gray-500">
                                            Max: {{ number_format($coupon->max_discount, 2) }} {{ trans('messages.EGP') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="font-semibold text-emerald-600">
                                        {{ number_format($coupon->discount_value, 2) }} {{ trans('messages.EGP') }}
                                    </span>
                                @endif
                                @if($coupon->min_amount > 0)
                                    <div class="text-xs text-gray-500">
                                        Min: {{ number_format($coupon->min_amount, 2) }} {{ trans('messages.EGP') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <div>{{ $coupon->usage_count }} / {{ $coupon->usage_limit ?: '∞' }}</div>
                                <div class="text-gray-500 mt-1">
                                    {{ $coupon->user_usage_limit }} {{ trans('messages.per user') }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                @if($coupon->starts_at || $coupon->expires_at)
                                    <div>
                                        @if($coupon->starts_at)
                                            From: {{ $coupon->starts_at->format('Y-m-d') }}
                                        @endif
                                    </div>
                                    <div>
                                        @if($coupon->expires_at)
                                            Until: {{ $coupon->expires_at->format('Y-m-d') }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">{{ trans('messages.No limit') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($coupon->is_active && $coupon->isValid())
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                        {{ trans('messages.Valid') }}
                                    </span>
                                @elseif(!$coupon->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                                        {{ trans('messages.Inactive') }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-700">
                                        {{ trans('messages.Expired') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.coupons.show', $coupon) }}"
                                   class="text-xs text-gray-600 hover:text-secondary">
                                    {{ trans('messages.View') }}
                                </a>
                                <a href="{{ route('admin.coupons.edit', $coupon) }}"
                                   class="text-xs text-primary hover:text-yellow-500">
                                    {{ trans('messages.Edit') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                {{ trans('messages.No coupons found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $coupons->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

