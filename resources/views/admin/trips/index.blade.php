<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Trips Management') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع الرحلات في النظام') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.trips.settings') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:bg-gray-200 transition">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ trans('messages.Trip Settings') }}</span>
                </a>
                <a href="{{ route('admin.trips.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                    +
                    <span class="ml-2">{{ trans('messages.بدء رحلة جديدة') }}</span>
                </a>
            </div>
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
                <form method="GET" action="{{ route('admin.trips.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Search') }} ({{ trans('messages.Name') }} / {{ trans('messages.Phone') }} / {{ trans('messages.University ID') }})</label>
                            <input type="text" 
                                   name="user_search" 
                                   value="{{ request('user_search') }}" 
                                   placeholder="{{ trans('messages.Enter name, phone, or university ID') }}"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</label>
                            <select name="status" class="w-full text-sm rounded-lg border-gray-300">
                                <option value="">{{ trans('messages.All') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ trans('messages.Active') }}</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ trans('messages.Completed') }}</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Zone Exit') }}</label>
                            <select name="zone_exit" class="w-full text-sm rounded-lg border-gray-300">
                                <option value="">{{ trans('messages.All') }}</option>
                                <option value="1" {{ request('zone_exit') === '1' ? 'selected' : '' }}>{{ trans('messages.Yes') }}</option>
                                <option value="0" {{ request('zone_exit') === '0' ? 'selected' : '' }}>{{ trans('messages.No') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date From') }}</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date To') }}</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg hover:bg-yellow-400">
                                {{ trans('messages.Filter') }}
                            </button>
                            @if(request()->hasAny(['user_search', 'status', 'zone_exit', 'date_from', 'date_to']))
                                <a href="{{ route('admin.trips.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">
                                    {{ trans('messages.Reset') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm" dir="rtl">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Users') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Scooters') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Start Time') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Duration') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Cost') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Payment Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($trips as $trip)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs text-right">
                                {{ $trip->id }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="font-semibold text-secondary">
                                    <a href="{{ route('admin.users.show', $trip->user) }}" class="hover:text-primary transition">
                                        {{ $trip->user->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500">{{ $trip->user->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-semibold text-secondary">{{ $trip->scooter->code }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 text-right">
                                {{ $trip->start_time->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 text-right">
                                @if($trip->duration_minutes)
                                    {{ $trip->duration_minutes }} {{ trans('messages.Minutes') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $paymentStatus = $trip->payment_status;
                                    $isFullyPaid = $paymentStatus === 'paid';
                                @endphp
                                <span class="font-semibold {{ $isFullyPaid ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ number_format($trip->cost, 2) }} {{ trans('messages.EGP') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $paymentStatus = $trip->payment_status;
                                    $statusLabels = [
                                        'paid' => trans('messages.Paid'),
                                        'partially_paid' => trans('messages.Partially Paid'),
                                        'unpaid' => trans('messages.Unpaid'),
                                    ];
                                    $statusColors = [
                                        'paid' => 'bg-emerald-50 text-emerald-700',
                                        'partially_paid' => 'bg-yellow-50 text-yellow-700',
                                        'unpaid' => 'bg-red-50 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$paymentStatus] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statusLabels[$paymentStatus] ?? $paymentStatus }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-blue-50 text-blue-700',
                                        'completed' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled' => 'bg-red-50 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$trip->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ trans('messages.' . ucfirst($trip->status)) }}
                                </span>
                                @if($trip->zone_exit_detected)
                                    <div class="mt-1">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700">
                                            {{ trans('messages.Zone Exit') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.trips.show', $trip) }}"
                                       class="text-xs text-gray-600 hover:text-secondary">
                                        {{ trans('messages.View') }}
                                    </a>
                                    @if($trip->status === 'active')
                                        <form action="{{ route('admin.trips.cancel', $trip) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-600"
                                                    onclick="return confirm('{{ trans('messages.هل أنت متأكد من إلغاء هذه الرحلة؟') }}')">
                                                {{ trans('messages.Cancel') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500" dir="rtl">
                                {{ trans('messages.No trips found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $trips->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

