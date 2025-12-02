<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Subscriptions') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع الاشتراكات في النظام') }}
                </p>
            </div>
            <a href="{{ route('admin.subscriptions.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إضافة اشتراك جديد') }}</span>
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
                <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</label>
                        <select name="status" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ trans('messages.Active') }}</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ trans('messages.Expired') }}</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>{{ trans('messages.Suspended') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</label>
                        <select name="type" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="minutes" {{ request('type') === 'minutes' ? 'selected' : '' }}>{{ trans('messages.Minutes') }}</option>
                            <option value="unlimited" {{ request('type') === 'unlimited' ? 'selected' : '' }}>{{ trans('messages.Unlimited') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Auto Renew') }}</label>
                        <select name="auto_renew" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="1" {{ request('auto_renew') === '1' ? 'selected' : '' }}>{{ trans('messages.Yes') }}</option>
                            <option value="0" {{ request('auto_renew') === '0' ? 'selected' : '' }}>{{ trans('messages.No') }}</option>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.User') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Name') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Usage') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Expires At') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $subscription->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-secondary">
                                    <a href="{{ route('admin.users.show', $subscription->user) }}" class="hover:text-primary">
                                        {{ $subscription->user->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500">{{ $subscription->user->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-secondary">{{ $subscription->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ number_format($subscription->price, 2) }} {{ trans('messages.EGP') }} / {{ ucfirst($subscription->billing_period) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($subscription->type === 'unlimited')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-primary/20 text-secondary">
                                        {{ trans('messages.Unlimited') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-600">
                                        {{ $subscription->minutes_included }} {{ trans('messages.minutes') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                @if($subscription->type === 'unlimited')
                                    <div>{{ $subscription->trips_count }} {{ trans('messages.trips') }}</div>
                                    <div class="text-gray-500">{{ $subscription->minutes_used }} {{ trans('messages.min used') }}</div>
                                @else
                                    <div>{{ $subscription->minutes_used }} / {{ $subscription->minutes_included }} {{ trans('messages.min') }}</div>
                                    <div class="text-gray-500">{{ $subscription->trips_count }} {{ trans('messages.trips') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $subscription->expires_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-emerald-50 text-emerald-700',
                                        'expired' => 'bg-red-50 text-red-700',
                                        'cancelled' => 'bg-gray-100 text-gray-700',
                                        'suspended' => 'bg-amber-50 text-amber-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ trans('messages.' . ucfirst($subscription->status)) }}
                                </span>
                                @if($subscription->auto_renew)
                                    <div class="mt-1">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700">
                                            {{ trans('messages.Auto Renew') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}"
                                   class="text-xs text-gray-600 hover:text-secondary">
                                    {{ trans('messages.View') }}
                                </a>
                                <a href="{{ route('admin.subscriptions.edit', $subscription) }}"
                                   class="text-xs text-primary hover:text-yellow-500">
                                    {{ trans('messages.Edit') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                {{ trans('messages.No subscriptions found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $subscriptions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

