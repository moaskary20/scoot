<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Subscription Details') }} #{{ $subscription->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل الاشتراك') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.subscriptions.edit', $subscription) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.تعديل') }}
                </a>
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="text-sm text-gray-600 hover:text-secondary">
                    {{ trans('messages.رجوع') }}
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

            <!-- Subscription Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                    @php
                        $statusColors = [
                            'active' => 'bg-emerald-50 text-emerald-700',
                            'expired' => 'bg-red-50 text-red-700',
                            'cancelled' => 'bg-gray-100 text-gray-700',
                            'suspended' => 'bg-amber-50 text-amber-700',
                        ];
                    @endphp
                    <div>
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Usage') }}</div>
                    @if($subscription->type === 'unlimited')
                        <div class="text-2xl font-bold text-secondary">
                            {{ $subscription->trips_count }} {{ trans('messages.trips') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ $subscription->minutes_used }} {{ trans('messages.min used') }}</div>
                    @else
                        <div class="text-2xl font-bold text-secondary">
                            {{ $subscription->minutes_used }} / {{ $subscription->minutes_included }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $subscription->getRemainingMinutes() }} {{ trans('messages.remaining') }}
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Price') }}</div>
                    <div class="text-2xl font-bold text-emerald-600">
                        {{ number_format($subscription->price, 2) }} {{ trans('messages.EGP') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ ucfirst($subscription->billing_period) }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Expires At') }}</div>
                    <div class="text-sm font-semibold text-secondary">
                        {{ $subscription->expires_at->format('Y-m-d H:i') }}
                    </div>
                    @if($subscription->auto_renew)
                        <div class="mt-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700">
                                {{ trans('messages.Auto Renew') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Subscription Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.User') }}</div>
                        <div class="font-semibold text-secondary">
                            <a href="{{ route('admin.users.show', $subscription->user) }}" class="hover:text-primary">
                                {{ $subscription->user->name }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-500">{{ $subscription->user->email }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Package Name') }}</div>
                        <div class="font-semibold text-secondary">{{ $subscription->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</div>
                        <div class="text-gray-700">
                            @if($subscription->type === 'unlimited')
                                {{ trans('messages.Unlimited') }}
                            @else
                                {{ $subscription->minutes_included }} {{ trans('messages.Minutes') }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Billing Period') }}</div>
                        <div class="text-gray-700">{{ ucfirst($subscription->billing_period) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Starts At') }}</div>
                        <div class="text-gray-700">{{ $subscription->starts_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Expires At') }}</div>
                        <div class="text-gray-700">{{ $subscription->expires_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    @if($subscription->renewed_at)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Last Renewed At') }}</div>
                            <div class="text-gray-700">{{ $subscription->renewed_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    @endif
                    @if($subscription->coupon)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Coupon Used') }}</div>
                            <div class="font-semibold text-secondary">
                                <a href="{{ route('admin.coupons.show', $subscription->coupon) }}" class="hover:text-primary">
                                    {{ $subscription->coupon->code }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Actions') }}</h3>
                <div class="flex items-center gap-3 flex-wrap">
                    @if($subscription->status === 'active')
                        <form action="{{ route('admin.subscriptions.renew', $subscription) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600"
                                    onclick="return confirm('{{ trans('messages.هل أنت متأكد من تجديد هذا الاشتراك؟') }}')">
                                {{ trans('messages.Renew') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.subscriptions.suspend', $subscription) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600"
                                    onclick="return confirm('{{ trans('messages.هل أنت متأكد من تعليق هذا الاشتراك؟') }}')">
                                {{ trans('messages.Suspend') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.subscriptions.cancel', $subscription) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-lg hover:bg-red-600"
                                    onclick="return confirm('{{ trans('messages.هل أنت متأكد من إلغاء هذا الاشتراك؟') }}')">
                                {{ trans('messages.Cancel') }}
                            </button>
                        </form>
                    @elseif($subscription->status === 'suspended')
                        <form action="{{ route('admin.subscriptions.activate', $subscription) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600">
                                {{ trans('messages.Activate') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($subscription->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-2">{{ trans('messages.Notes') }}</h3>
                    <div class="text-sm text-gray-700">{{ $subscription->notes }}</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

