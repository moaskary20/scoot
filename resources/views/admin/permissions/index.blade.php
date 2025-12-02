<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Permissions') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدارة الصلاحيات') }}
                </p>
            </div>
            <a href="{{ route('admin.permissions.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                +
                <span class="ml-2">{{ trans('messages.إضافة صلاحية جديدة') }}</span>
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

            <div class="space-y-6">
                @foreach($permissions as $group => $groupPermissions)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-secondary mb-4">{{ $group ?: trans('messages.Other') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($groupPermissions as $permission)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">{{ $permission->name }}</div>
                                            @if($permission->description)
                                                <div class="text-xs text-gray-500 mt-1">{{ $permission->description }}</div>
                                            @endif
                                            <div class="text-xs text-gray-400 mt-1 font-mono">{{ $permission->slug }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.permissions.show', $permission) }}" class="text-primary hover:text-secondary text-xs">
                                                {{ trans('messages.View') }}
                                            </a>
                                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="text-blue-600 hover:text-blue-800 text-xs">
                                                {{ trans('messages.Edit') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

