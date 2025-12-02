<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Transaction Details') }} #{{ $walletTransaction->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل المعاملة') }}
                </p>
            </div>
            <a href="{{ route('admin.wallet.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع للقائمة') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Transaction Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Amount') }}</div>
                    <div class="text-2xl font-bold {{ $walletTransaction->transaction_type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $walletTransaction->transaction_type === 'credit' ? '+' : '-' }}{{ number_format($walletTransaction->amount, 2) }} EGP
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Balance Before') }}</div>
                    <div class="text-xl font-bold text-gray-700">
                        {{ number_format($walletTransaction->balance_before, 2) }} EGP
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Balance After') }}</div>
                    <div class="text-xl font-bold text-secondary">
                        {{ number_format($walletTransaction->balance_after, 2) }} EGP
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                    @if($walletTransaction->status === 'completed')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                            Completed
                        </span>
                    @elseif($walletTransaction->status === 'pending')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Pending
                        </span>
                    @elseif($walletTransaction->status === 'failed')
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            Failed
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            Cancelled
                        </span>
                    @endif
                </div>
            </div>

            <!-- Transaction Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Transaction Information') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.User') }}</div>
                        <div class="font-semibold text-secondary">
                            <a href="{{ route('admin.users.show', $walletTransaction->user_id) }}" class="hover:text-primary">
                                {{ $walletTransaction->user->name }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-500">{{ $walletTransaction->user->email }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</div>
                        <div class="font-semibold text-secondary">
                            {{ str_replace('_', ' ', ucwords($walletTransaction->type, '_')) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Transaction Type') }}</div>
                        <div>
                            @if($walletTransaction->transaction_type === 'credit')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Credit
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Debit
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Date/Time') }}</div>
                        <div class="text-gray-700">{{ $walletTransaction->processed_at->format('Y-m-d H:i:s') }}</div>
                    </div>

                    @if($walletTransaction->trip)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Trip') }}</div>
                            <div>
                                <a href="{{ route('admin.trips.show', $walletTransaction->trip_id) }}" class="text-sm font-medium text-primary hover:underline">
                                    Trip #{{ $walletTransaction->trip_id }}
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($walletTransaction->reference)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Reference') }}</div>
                            <div class="text-gray-700 font-mono text-xs">{{ $walletTransaction->reference }}</div>
                        </div>
                    @endif

                    @if($walletTransaction->payment_method)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Payment Method') }}</div>
                            <div class="text-gray-700">{{ ucfirst($walletTransaction->payment_method) }}</div>
                        </div>
                    @endif
                </div>

                @if($walletTransaction->description)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Description') }}</div>
                        <div class="text-sm text-gray-700">{{ $walletTransaction->description }}</div>
                    </div>
                @endif

                @if($walletTransaction->notes)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Notes') }}</div>
                        <div class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $walletTransaction->notes }}</div>
                    </div>
                @endif

                @if($walletTransaction->metadata)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Additional Data') }}</div>
                        <pre class="text-xs bg-gray-50 p-3 rounded-lg overflow-x-auto">{{ json_encode($walletTransaction->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

