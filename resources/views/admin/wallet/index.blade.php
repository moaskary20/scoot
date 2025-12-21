<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Wallet Transactions') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع معاملات المحفظة') }}
                </p>
            </div>
            <button onclick="openAddTransactionModal()"
                    class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="mr-2">{{ trans('messages.Add Transaction') }}</span>
            </button>
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
                <form method="GET" action="{{ route('admin.wallet.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.User') }}</label>
                        <select name="user_id" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All Users') }}</option>
                            @php
                                $filterUsers = isset($users) ? $users : \App\Models\User::orderBy('name')->get();
                            @endphp
                            @foreach($filterUsers as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                        @if($filterUsers->isEmpty())
                            <p class="mt-1 text-xs text-yellow-500">{{ trans('messages.No users found in database') }}</p>
                        @else
                            <p class="mt-1 text-xs text-gray-400">{{ $filterUsers->count() }} {{ trans('messages.user(s) available') }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Type') }}</label>
                        <select name="type" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="top_up" {{ request('type') === 'top_up' ? 'selected' : '' }}>Top Up</option>
                            <option value="trip_payment" {{ request('type') === 'trip_payment' ? 'selected' : '' }}>Trip Payment</option>
                            <option value="penalty" {{ request('type') === 'penalty' ? 'selected' : '' }}>Penalty</option>
                            <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            <option value="subscription" {{ request('type') === 'subscription' ? 'selected' : '' }}>Subscription</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Transaction Type') }}</label>
                        <select name="transaction_type" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="credit" {{ request('transaction_type') === 'credit' ? 'selected' : '' }}>Credit</option>
                            <option value="debit" {{ request('transaction_type') === 'debit' ? 'selected' : '' }}>Debit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</label>
                        <select name="status" class="w-full text-sm rounded-lg border-gray-300">
                            <option value="">{{ trans('messages.All') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                    <div class="md:col-span-6 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg">
                            {{ trans('messages.Filter') }}
                        </button>
                        <a href="{{ route('admin.wallet.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                            {{ trans('messages.Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Active Filters Info -->
            @if(request()->hasAny(['user_id', 'type', 'transaction_type', 'status', 'date_from', 'date_to']))
                <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span class="font-medium">{{ trans('messages.Active Filters') }}:</span>
                            @if(request('user_id'))
                                <span class="px-2 py-0.5 bg-blue-100 rounded text-xs">User: {{ \App\Models\User::find(request('user_id'))?->name ?? 'N/A' }}</span>
                            @endif
                            @if(request('type'))
                                <span class="px-2 py-0.5 bg-blue-100 rounded text-xs">Type: {{ ucwords(str_replace('_', ' ', request('type'))) }}</span>
                            @endif
                            @if(request('transaction_type'))
                                <span class="px-2 py-0.5 bg-blue-100 rounded text-xs">{{ ucfirst(request('transaction_type')) }}</span>
                            @endif
                            @if(request('status'))
                                <span class="px-2 py-0.5 bg-blue-100 rounded text-xs">Status: {{ ucfirst(request('status')) }}</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.wallet.index') }}" class="text-xs text-blue-600 hover:text-blue-800 underline">
                            {{ trans('messages.Clear All') }}
                        </a>
                    </div>
                </div>
            @endif

            <!-- Transactions Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                @if($transactions->total() > 0)
                    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                        <p class="text-sm text-gray-600">
                            {{ trans('messages.Showing :from to :to of :total transactions', [
                                'from' => $transactions->firstItem(),
                                'to' => $transactions->lastItem(),
                                'total' => $transactions->total()
                            ]) }}
                        </p>
                    </div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.ID') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.User') }}</th>
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
                                        @if($transaction->user)
                                            <a href="{{ route('admin.users.show', $transaction->user_id) }}" class="text-sm font-medium text-primary hover:underline">
                                                {{ $transaction->user->name }}
                                            </a>
                                            <div class="text-xs text-gray-500">{{ $transaction->user->email }}</div>
                                        @else
                                            <span class="text-sm text-gray-400">User #{{ $transaction->user_id }}</span>
                                            <div class="text-xs text-red-500">{{ trans('messages.User not found') }}</div>
                                        @endif
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
                                        @elseif($transaction->status === 'failed')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Failed
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Cancelled
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
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-900 mb-1">{{ trans('messages.No transactions found') }}</p>
                                            <p class="text-xs text-gray-500 mb-4">
                                                @if(request()->hasAny(['user_id', 'type', 'transaction_type', 'status', 'date_from', 'date_to']))
                                                    {{ trans('messages.Try adjusting your filters or') }}
                                                    <a href="{{ route('admin.wallet.index') }}" class="text-primary hover:underline">{{ trans('messages.clear all filters') }}</a>
                                                @else
                                                    {{ trans('messages.No wallet transactions have been created yet.') }}
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div id="add-transaction-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" dir="rtl">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-secondary">{{ trans('messages.Add Transaction') }}</h3>
                <button onclick="document.getElementById('add-transaction-modal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.wallet.create-transaction') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ trans('messages.User') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
                        <option value="">{{ trans('messages.Select User') }}</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ trans('messages.Transaction Type') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="transaction_type" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
                        <option value="credit">{{ trans('messages.Credit (Add)') }}</option>
                        <option value="debit">{{ trans('messages.Debit (Deduct)') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ trans('messages.Type') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
                        <option value="top_up">{{ trans('messages.Top Up') }}</option>
                        <option value="adjustment">{{ trans('messages.Adjustment') }}</option>
                        <option value="refund">{{ trans('messages.Refund') }}</option>
                        <option value="penalty">{{ trans('messages.Penalty') }}</option>
                        <option value="trip_payment">{{ trans('messages.Trip Payment') }}</option>
                        <option value="subscription">{{ trans('messages.Subscription') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ trans('messages.Amount') }} ({{ trans('messages.EGP') }}) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="transaction_amount" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
                </div>

                <!-- Payment Method (only for credit transactions) -->
                <div id="payment_method_container" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ trans('messages.Payment Method') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method" id="payment_method" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
                        <option value="manual">{{ trans('messages.Manual (Direct)') }}</option>
                        <option value="paymob">Paymob</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ trans('messages.Select payment method. If Paymob is selected, a payment link will be generated.') }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ trans('messages.Description') }}
                    </label>
                    <textarea name="description" rows="2"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ trans('messages.Notes') }}
                    </label>
                    <textarea name="notes" rows="2"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('add-transaction-modal').classList.add('hidden')"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                        {{ trans('messages.Cancel') }}
                    </button>
                    <button type="submit" id="submit_btn"
                            class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                        {{ trans('messages.Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Function to toggle payment method visibility
        function togglePaymentMethod() {
            const transactionTypeSelect = document.querySelector('#add-transaction-modal select[name="transaction_type"]');
            const paymentMethodContainer = document.getElementById('payment_method_container');
            const paymentMethodSelect = document.getElementById('payment_method');
            
            if (!transactionTypeSelect || !paymentMethodContainer || !paymentMethodSelect) {
                return;
            }

            if (transactionTypeSelect.value === 'credit') {
                paymentMethodContainer.style.display = 'block';
                paymentMethodSelect.required = true;
            } else {
                paymentMethodContainer.style.display = 'none';
                paymentMethodSelect.required = false;
                paymentMethodSelect.value = 'manual';
            }
        }

        // Function to open modal and setup event listeners
        function openAddTransactionModal() {
            const modal = document.getElementById('add-transaction-modal');
            if (modal) {
                modal.classList.remove('hidden');
                
                // Setup event listener for transaction type change
                setTimeout(function() {
                    const transactionTypeSelect = modal.querySelector('select[name="transaction_type"]');
                    const paymentMethodContainer = document.getElementById('payment_method_container');
                    
                    if (transactionTypeSelect) {
                        // Remove any existing listeners by cloning
                        const newSelect = transactionTypeSelect.cloneNode(true);
                        transactionTypeSelect.parentNode.replaceChild(newSelect, transactionTypeSelect);
                        
                        // Add new listener
                        newSelect.addEventListener('change', togglePaymentMethod);
                        
                        // Check initial state - if credit is selected, show payment method
                        if (newSelect.value === 'credit' && paymentMethodContainer) {
                            paymentMethodContainer.style.display = 'block';
                            const paymentMethodSelect = document.getElementById('payment_method');
                            if (paymentMethodSelect) {
                                paymentMethodSelect.required = true;
                            }
                        }
                    }
                }, 150);
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Setup event listener for transaction type change (in case modal is already visible)
            const transactionTypeSelect = document.querySelector('#add-transaction-modal select[name="transaction_type"]');
            if (transactionTypeSelect) {
                transactionTypeSelect.addEventListener('change', togglePaymentMethod);
                togglePaymentMethod();
            }
        });
    </script>
</x-app-layout>

