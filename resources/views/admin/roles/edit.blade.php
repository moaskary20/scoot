<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Edit Role') }}: {{ $role->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تعديل الدور') }}
                </p>
            </div>
            <a href="{{ route('admin.roles.show', $role) }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                                   class="w-full rounded-lg border-gray-300 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Slug') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="slug" value="{{ old('slug', $role->slug) }}" required
                                   class="w-full rounded-lg border-gray-300 @error('slug') border-red-500 @enderror">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Description') }}
                            </label>
                            <textarea name="description" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('description') border-red-500 @enderror">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">{{ trans('messages.Active') }}</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                {{ trans('messages.Permissions') }}
                            </label>
                            <div class="space-y-4">
                                @foreach($permissions as $group => $groupPermissions)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="text-sm font-semibold text-secondary mb-3">{{ $group ?: trans('messages.Other') }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach($groupPermissions as $permission)
                                                <label class="flex items-start">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                           class="mt-1 rounded border-gray-300"
                                                           {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                    <div class="ml-2 flex-1">
                                                        <div class="text-sm font-medium text-gray-900">{{ $permission->name }}</div>
                                                        @if($permission->description)
                                                            <div class="text-xs text-gray-500 mt-0.5">{{ $permission->description }}</div>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.roles.show', $role) }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

