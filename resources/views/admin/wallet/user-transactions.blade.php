<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Wallet Transactions') }}: {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.معاملات محفظة المستخدم') }}
                </p>
            </div>
            <a href="{{ route('admin.users.show', $user) }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.Back to User') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Current Balance') }}</div>
                    <div class="text-2xl font-bold text-emerald-600">
                        {{ number_format($user->wallet_balance, 2) }} {{ trans('messages.EGP') }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Top Ups') }}</div>
                    <div class="text-2xl font-bold text-secondary">
                        {{ number_format($statistics['total_top_ups'], 2) }} {{ trans('messages.EGP') }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Spent') }}</div>
                    <div class="text-2xl font-bold text-red-600">
                        {{ number_format($statistics['total_spent'], 2) }} {{ trans('messages.EGP') }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Refunds') }}</div>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ number_format($statistics['total_refunds'], 2) }} {{ trans('messages.EGP') }}
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-secondary">{{ trans('messages.Transaction History') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.ID') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Type') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Amount') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Balance') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Date') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        #{{ $transaction->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ str_replace('_', ' ', ucwords($transaction->type, '_')) }}
                                        </div>
                                        @if($transaction->trip)
                                            <div class="text-xs text-gray-500">
                                                <a href="{{ route('admin.trips.show', $transaction->trip_id) }}" class="hover:underline">
                                                    Trip #{{ $transaction->trip_id }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold {{ $transaction->transaction_type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $transaction->transaction_type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} EGP
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ number_format($transaction->balance_before, 2) }} → {{ number_format($transaction->balance_after, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transaction->status === 'completed')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                Completed
                                            </span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $transaction->created_at->format('Y-m-d') }}</div>
                                        <div class="text-xs">{{ $transaction->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.wallet.show', $transaction) }}" class="text-primary hover:text-secondary">
                                            {{ trans('messages.View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ trans('messages.No transactions found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

