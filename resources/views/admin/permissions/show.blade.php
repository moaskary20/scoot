<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Permission Details') }}: {{ $permission->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تفاصيل الصلاحية') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.permissions.edit', $permission) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.تعديل') }}
                </a>
                <a href="{{ route('admin.permissions.index') }}"
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

            <!-- Permission Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Name') }}</div>
                        <div class="text-sm font-semibold text-secondary">{{ $permission->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Slug') }}</div>
                        <div class="text-sm font-mono text-gray-700">{{ $permission->slug }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Group') }}</div>
                        <div class="text-sm text-gray-700">{{ $permission->group ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Roles Count') }}</div>
                        <div class="text-sm font-semibold text-secondary">{{ $permission->roles->count() }}</div>
                    </div>
                </div>

                @if($permission->description)
                    <div>
                        <div class="text-xs text-gray-500 mb-1">{{ trans('messages.Description') }}</div>
                        <div class="text-sm text-gray-700">{{ $permission->description }}</div>
                    </div>
                @endif
            </div>

            <!-- Roles with this Permission -->
            @if($permission->roles->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Roles with this Permission') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($permission->roles as $role)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                @if($role->description)
                                    <div class="text-xs text-gray-500 mt-1">{{ $role->description }}</div>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('admin.roles.show', $role) }}" class="text-xs text-primary hover:text-secondary">
                                        {{ trans('messages.View Role') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

