<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Geo-Zones') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة مناطق التشغيل والمناطق الممنوعة ومناطق الركن') }}
                </p>
            </div>
            <a href="{{ route('admin.geo-zones.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إنشاء منطقة جديدة') }}</span>
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
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Name') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Type') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Color') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($zones as $zone)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs text-right">
                                {{ $zone->id }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-secondary text-right">
                                {{ $zone->name }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $typeLabels = [
                                        'allowed' => trans('messages.Allowed Zone'),
                                        'forbidden' => trans('messages.Forbidden Zone'),
                                        'parking' => trans('messages.Parking Zone'),
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                                    {{ $typeLabels[$zone->type] ?? $zone->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <span class="h-4 w-4 rounded-full border border-gray-300" style="background-color: {{ $zone->color }}"></span>
                                    <span class="text-xs text-gray-600">{{ $zone->color }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($zone->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                        {{ trans('messages.Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">
                                        {{ trans('messages.Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.geo-zones.show', $zone) }}"
                                       class="text-xs text-gray-600 hover:text-secondary transition">
                                        {{ trans('messages.View') }}
                                    </a>
                                    <a href="{{ route('admin.geo-zones.edit', $zone) }}"
                                       class="text-xs text-primary hover:text-yellow-500 transition">
                                        {{ trans('messages.Edit') }}
                                    </a>
                                    <form action="{{ route('admin.geo-zones.destroy', $zone) }}"
                                          method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('{{ trans('messages.Are you sure you want to delete this zone?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-600 transition">
                                            {{ trans('messages.Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500" dir="rtl">
                                {{ trans('messages.No geo zones found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $zones->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


