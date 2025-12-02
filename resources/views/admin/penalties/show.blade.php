<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Penalty Details') }} #{{ $penalty->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل الغرامة') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.penalties.edit', $penalty) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.تعديل') }}
                </a>
                <a href="{{ route('admin.penalties.index') }}"
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

            <!-- Penalty Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Amount') }}</div>
                    <div class="text-2xl font-bold text-red-600">
                        {{ number_format($penalty->amount, 2) }} {{ trans('messages.EGP') }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                    @php
                        $statusColors = [
                            'pending' => 'bg-amber-50 text-amber-700',
                            'paid' => 'bg-emerald-50 text-emerald-700',
                            'waived' => 'bg-blue-50 text-blue-700',
                            'cancelled' => 'bg-red-50 text-red-700',
                        ];
                    @endphp
                    <div>
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$penalty->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($penalty->status) }}
                        </span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</div>
                    @php
                        $typeLabels = [
                            'zone_exit' => trans('messages.Zone Exit'),
                            'forbidden_parking' => trans('messages.Forbidden Parking'),
                            'unlocked_scooter' => trans('messages.Unlocked Scooter'),
                            'other' => trans('messages.Other'),
                        ];
                    @endphp
                    <div class="font-semibold text-secondary">
                        {{ $typeLabels[$penalty->type] ?? ucfirst($penalty->type) }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Applied At') }}</div>
                    <div class="text-sm text-gray-700">
                        {{ $penalty->applied_at?->format('Y-m-d H:i') ?: '-' }}
                    </div>
                </div>
            </div>

            <!-- Penalty Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Penalty Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.User') }}</div>
                        <div class="font-semibold text-secondary">
                            <a href="{{ route('admin.users.show', $penalty->user) }}" class="hover:text-primary">
                                {{ $penalty->user->name }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-500">{{ $penalty->user->email }}</div>
                    </div>
                    @if($penalty->trip)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Trip') }}</div>
                            <div class="font-semibold text-secondary">
                                <a href="{{ route('admin.trips.show', $penalty->trip) }}" class="hover:text-primary">
                                    Trip #{{ $penalty->trip->id }}
                                </a>
                            </div>
                            <div class="text-xs text-gray-500">{{ $penalty->trip->start_time->format('Y-m-d H:i') }}</div>
                        </div>
                    @endif
                    @if($penalty->scooter)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Scooter') }}</div>
                            <div class="font-semibold text-secondary">
                                <a href="{{ route('admin.scooters.show', $penalty->scooter) }}" class="hover:text-primary">
                                    {{ $penalty->scooter->code }}
                                </a>
                            </div>
                        </div>
                    @endif
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Auto Applied') }}</div>
                        <div>
                            @if($penalty->is_auto_applied)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700">
                                    {{ trans('messages.Yes') }}
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                                    {{ trans('messages.No') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Created At') }}</div>
                        <div class="text-gray-700">{{ $penalty->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    @if($penalty->paid_at)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Paid At') }}</div>
                            <div class="text-gray-700">{{ $penalty->paid_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Title & Description -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-2">{{ trans('messages.Title') }}</h3>
                <div class="text-sm text-gray-700 font-semibold mb-4">{{ $penalty->title }}</div>
                
                @if($penalty->description)
                    <h3 class="text-sm font-semibold text-secondary mb-2">{{ trans('messages.Description') }}</h3>
                    <div class="text-sm text-gray-700">{{ $penalty->description }}</div>
                @endif
            </div>

            <!-- Actions -->
            @if($penalty->status === 'pending')
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Actions') }}</h3>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('admin.penalties.mark-as-paid', $penalty) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600"
                                    onclick="return confirm('{{ trans('messages.هل أنت متأكد من تحديد هذه الغرامة كمدفوعة؟') }}')">
                                {{ trans('messages.Mark as Paid') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.penalties.waive', $penalty) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white text-sm font-semibold rounded-lg hover:bg-blue-600"
                                    onclick="return confirm('{{ trans('messages.هل أنت متأكد من إعفاء هذه الغرامة؟') }}')">
                                {{ trans('messages.Waive Penalty') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

