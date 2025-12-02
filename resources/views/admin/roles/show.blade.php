<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Role Details') }}: {{ $role->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل الدور') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.roles.edit', $role) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.تعديل') }}
                </a>
                <a href="{{ route('admin.roles.index') }}"
                   class="text-sm text-gray-600 hover:text-secondary">
                    {{ trans('messages.رجوع') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Role Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Name') }}</div>
                        <div class="text-sm font-semibold text-secondary">{{ $role->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Slug') }}</div>
                        <div class="text-sm font-mono text-gray-700">{{ $role->slug }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Status') }}</div>
                        <div>
                            @if($role->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Users Count') }}</div>
                        <div class="text-sm font-semibold text-secondary">{{ $role->users->count() }}</div>
                    </div>
                </div>

                @if($role->description)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Description') }}</div>
                        <div class="text-sm text-gray-700">{{ $role->description }}</div>
                    </div>
                @endif
            </div>

            <!-- Permissions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Permissions') }}</h3>
                @if($role->permissions->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($role->permissions as $permission)
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="text-sm font-medium text-gray-900">{{ $permission->name }}</div>
                                @if($permission->description)
                                    <div class="text-xs text-gray-500 mt-1">{{ $permission->description }}</div>
                                @endif
                                <div class="text-xs text-gray-400 mt-1 font-mono">{{ $permission->slug }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{ trans('messages.No permissions assigned.') }}</p>
                @endif
            </div>

            <!-- Users with this Role -->
            @if($role->users->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Users with this Role') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Name') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Email') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($role->users as $user)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $user->email }}</td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.users.show', $user) }}" class="text-primary hover:text-secondary">
                                                {{ trans('messages.View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

