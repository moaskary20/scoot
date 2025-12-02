<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Scooters Management') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع السكوترات في النظام') }}
                </p>
            </div>
            <a href="{{ route('admin.scooters.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إضافة سكوتر جديد') }}</span>
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

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm" dir="rtl">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Code') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Battery') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Location') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Lock') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($scooters as $scooter)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs text-right">
                                {{ $scooter->id }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-secondary text-right">
                                {{ $scooter->code }}
                                @if (! $scooter->is_active)
                                    <span class="mr-2 inline-flex px-2 py-0.5 rounded-full text-[10px] bg-gray-200 text-gray-700">
                                        {{ trans('messages.Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-emerald-50 text-emerald-700',
                                        'rented' => 'bg-blue-50 text-blue-700',
                                        'charging' => 'bg-amber-50 text-amber-700',
                                        'maintenance' => 'bg-red-50 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$scooter->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ trans('messages.' . ucfirst($scooter->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <div class="w-20 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $scooter->battery_percentage }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600">
                                        {{ $scooter->battery_percentage }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 text-right">
                                @if($scooter->latitude && $scooter->longitude)
                                    <span>{{ $scooter->latitude }}, {{ $scooter->longitude }}</span>
                                @else
                                    <span class="text-gray-400">{{ trans('messages.No location data') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($scooter->is_locked)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-secondary text-primary">
                                        {{ trans('messages.Locked') }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-primary/20 text-secondary">
                                        {{ trans('messages.Unlocked') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.scooters.show', $scooter) }}"
                                       class="text-xs text-gray-600 hover:text-secondary">
                                        {{ trans('messages.View') }}
                                    </a>
                                    <a href="{{ route('admin.scooters.edit', $scooter) }}"
                                       class="text-xs text-primary hover:text-yellow-500">
                                        {{ trans('messages.Edit') }}
                                    </a>
                                    <form action="{{ route('admin.scooters.destroy', $scooter) }}"
                                          method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('{{ trans('messages.هل أنت متأكد من حذف هذا السكوتر؟') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-600">
                                            {{ trans('messages.Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500" dir="rtl">
                                {{ trans('messages.No scooters found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $scooters->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



