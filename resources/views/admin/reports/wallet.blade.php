<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Wallet Report') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تقارير المحفظة والمعاملات') }}
                </p>
            </div>
            <a href="{{ route('admin.reports.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع للتقارير') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Date Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form method="GET" action="{{ route('admin.reports.wallet') }}" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date From') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Date To') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg">
                        {{ trans('messages.Filter') }}
                    </button>
                </form>
            </div>

            <!-- Wallet Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Top Ups') }}</div>
                    <div class="text-3xl font-bold text-emerald-600">{{ number_format($walletStats['total_top_ups'] ?? 0, 2) }} {{ trans('messages.EGP') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Deductions') }}</div>
                    <div class="text-3xl font-bold text-red-600">{{ number_format($walletStats['total_deductions'] ?? 0, 2) }} {{ trans('messages.EGP') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Total Refunds') }}</div>
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($walletStats['total_refunds'] ?? 0, 2) }} {{ trans('messages.EGP') }}</div>
                </div>
            </div>

            <!-- Revenue by Type -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Transactions by Type') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Type') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Total Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($walletStats['by_type'] as $type)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">{{ trans('messages.' . ucfirst(str_replace('_', ' ', $type->type))) }}</td>
                                    <td class="px-4 py-3 font-semibold {{ $type->type === 'top_up' || $type->type === 'refund' ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ number_format($type->total ?? 0, 2) }} {{ trans('messages.EGP') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">{{ trans('messages.No data available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Daily Flow -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Daily Flow') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Date') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Credits') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Debits') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Net') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($walletStats['daily_flow'] as $flow)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">{{ $flow->date }}</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-600">{{ number_format($flow->credits ?? 0, 2) }} {{ trans('messages.EGP') }}</td>
                                    <td class="px-4 py-3 font-semibold text-red-600">{{ number_format($flow->debits ?? 0, 2) }} {{ trans('messages.EGP') }}</td>
                                    <td class="px-4 py-3 font-semibold {{ ($flow->credits ?? 0) - ($flow->debits ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ number_format(($flow->credits ?? 0) - ($flow->debits ?? 0), 2) }} {{ trans('messages.EGP') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ trans('messages.No data available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Transactions List') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.ID') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.User') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Type') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Amount') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">#{{ $transaction->id }}</td>
                                    <td class="px-4 py-3">
                                        @if($transaction->user)
                                            <a href="{{ route('admin.users.show', $transaction->user_id) }}" class="font-semibold text-secondary hover:text-primary">
                                                {{ $transaction->user->name }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ trans('messages.' . ucfirst(str_replace('_', ' ', $transaction->type))) }}</td>
                                    <td class="px-4 py-3 font-semibold {{ $transaction->transaction_type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $transaction->transaction_type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} {{ trans('messages.EGP') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium 
                                            {{ $transaction->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 
                                               ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ trans('messages.' . ucfirst($transaction->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ trans('messages.No transactions found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

