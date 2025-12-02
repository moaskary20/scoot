<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Loyalty Points Transactions') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.سجل معاملات نقاط الولاء') }}
                </p>
            </div>
            <a href="{{ route('admin.loyalty.settings') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                {{ trans('messages.Settings') }}
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
                <form method="GET" action="{{ route('admin.loyalty.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</label>
                        <select name="type" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="earned" {{ request('type') === 'earned' ? 'selected' : '' }}>{{ trans('messages.Earned') }}</option>
                            <option value="redeemed" {{ request('type') === 'redeemed' ? 'selected' : '' }}>{{ trans('messages.Redeemed') }}</option>
                            <option value="adjusted" {{ request('type') === 'adjusted' ? 'selected' : '' }}>{{ trans('messages.Adjusted') }}</option>
                            <option value="expired" {{ request('type') === 'expired' ? 'selected' : '' }}>{{ trans('messages.Expired') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.User') }}</label>
                        <input type="number" name="user_id" value="{{ request('user_id') }}" placeholder="User ID"
                               class="w-full text-sm rounded-lg border-gray-300">
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Points') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Balance After') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Description') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Date') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $transaction->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-secondary">
                                    <a href="{{ route('admin.users.show', $transaction->user) }}" class="hover:text-primary">
                                        {{ $transaction->user->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500">{{ $transaction->user->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $typeColors = [
                                        'earned' => 'bg-emerald-50 text-emerald-700',
                                        'redeemed' => 'bg-red-50 text-red-700',
                                        'adjusted' => 'bg-blue-50 text-blue-700',
                                        'expired' => 'bg-gray-100 text-gray-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $typeColors[$transaction->type] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold {{ $transaction->points > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $transaction->points > 0 ? '+' : '' }}{{ $transaction->points }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ number_format($transaction->balance_after) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $transaction->description ?: '-' }}
                                @if($transaction->trip)
                                    <div class="text-gray-500 mt-1">
                                        Trip #{{ $transaction->trip->id }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $transaction->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                {{ trans('messages.لا توجد معاملات حتى الآن.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

