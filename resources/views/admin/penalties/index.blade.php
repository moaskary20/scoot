<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Penalties') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع الغرامات في النظام') }}
                </p>
            </div>
            <a href="{{ route('admin.penalties.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إضافة غرامة جديدة') }}</span>
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
                <form method="GET" action="{{ route('admin.penalties.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ trans('messages.Pending') }}</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ trans('messages.Paid') }}</option>
                                <option value="waived" {{ request('status') === 'waived' ? 'selected' : '' }}>{{ trans('messages.Waived') }}</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</label>
                            <select name="type" class="w-full text-sm rounded-lg border-gray-300">
                                <option value="">{{ trans('messages.All') }}</option>
                                <option value="zone_exit" {{ request('type') === 'zone_exit' ? 'selected' : '' }}>{{ trans('messages.Zone Exit') }}</option>
                                <option value="forbidden_parking" {{ request('type') === 'forbidden_parking' ? 'selected' : '' }}>{{ trans('messages.Forbidden Parking') }}</option>
                                <option value="unlocked_scooter" {{ request('type') === 'unlocked_scooter' ? 'selected' : '' }}>{{ trans('messages.Unlocked Scooter') }}</option>
                                <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>{{ trans('messages.Other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Auto Applied') }}</label>
                            <select name="auto_applied" class="w-full text-sm rounded-lg border-gray-300">
                                <option value="">{{ trans('messages.All') }}</option>
                                <option value="1" {{ request('auto_applied') === '1' ? 'selected' : '' }}>{{ trans('messages.Yes') }}</option>
                                <option value="0" {{ request('auto_applied') === '0' ? 'selected' : '' }}>{{ trans('messages.No') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg hover:bg-yellow-400">
                                {{ trans('messages.Filter') }}
                            </button>
                            @if(request()->hasAny(['user_search', 'status', 'type', 'auto_applied']))
                                <a href="{{ route('admin.penalties.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">
                                    {{ trans('messages.Reset') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.User') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Title') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Amount') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Applied At') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($penalties as $penalty)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $penalty->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-secondary">
                                    <a href="{{ route('admin.users.show', $penalty->user) }}" class="hover:text-primary">
                                        {{ $penalty->user->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500">{{ $penalty->user->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $typeLabels = [
                                        'zone_exit' => trans('messages.Zone Exit'),
                                        'forbidden_parking' => trans('messages.Forbidden Parking'),
                                        'unlocked_scooter' => trans('messages.Unlocked Scooter'),
                                        'other' => trans('messages.Other'),
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                                    {{ $typeLabels[$penalty->type] ?? ucfirst($penalty->type) }}
                                </span>
                                @if($penalty->is_auto_applied)
                                    <div class="mt-1">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700">
                                            {{ trans('messages.Auto') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $penalty->title }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-red-600">
                                    {{ number_format($penalty->amount, 2) }} {{ trans('messages.EGP') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'paid' => 'bg-emerald-50 text-emerald-700',
                                        'waived' => 'bg-blue-50 text-blue-700',
                                        'cancelled' => 'bg-red-50 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$penalty->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ trans('messages.' . ucfirst($penalty->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $penalty->applied_at?->format('Y-m-d H:i') ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.penalties.show', $penalty) }}"
                                   class="text-xs text-gray-600 hover:text-secondary">
                                    {{ trans('messages.View') }}
                                </a>
                                <a href="{{ route('admin.penalties.edit', $penalty) }}"
                                   class="text-xs text-primary hover:text-yellow-500">
                                    {{ trans('messages.Edit') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                {{ trans('messages.No penalties found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $penalties->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

