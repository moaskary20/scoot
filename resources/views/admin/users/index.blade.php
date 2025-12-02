<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Users Management') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة جميع المستخدمين في النظام') }}
                </p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إضافة مستخدم جديد') }}</span>
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
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Email') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Phone') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Wallet Balance') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Loyalty') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ trans('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs text-right">
                                {{ $user->id }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="font-semibold text-secondary">{{ $user->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-right">
                                {{ $user->email }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs text-right">
                                {{ $user->phone ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-semibold text-emerald-600">
                                    {{ number_format($user->wallet_balance, 2) }} {{ trans('messages.EGP') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <span class="text-xs text-gray-600">{{ $user->loyalty_points }} {{ trans('messages.Points') }}</span>
                                    @php
                                        $levelColors = [
                                            'bronze' => 'bg-amber-100 text-amber-700',
                                            'silver' => 'bg-gray-100 text-gray-700',
                                            'gold' => 'bg-yellow-100 text-yellow-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $levelColors[$user->loyalty_level] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ trans('messages.' . ucfirst($user->loyalty_level)) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($user->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                        {{ trans('messages.Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-700">
                                        {{ trans('messages.Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="text-xs text-gray-600 hover:text-secondary">
                                        {{ trans('messages.View') }}
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="text-xs text-primary hover:text-yellow-500">
                                        {{ trans('messages.Edit') }}
                                    </a>
                                    <form action="{{ route('admin.users.toggle-active', $user) }}"
                                          method="POST"
                                          class="inline-block">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs {{ $user->is_active ? 'text-red-500 hover:text-red-600' : 'text-emerald-500 hover:text-emerald-600' }}">
                                            {{ $user->is_active ? trans('messages.Deactivate') : trans('messages.Activate') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500" dir="rtl">
                                {{ trans('messages.No users found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

