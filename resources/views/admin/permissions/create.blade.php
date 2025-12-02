<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Create New Permission') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إضافة صلاحية جديدة') }}
                </p>
            </div>
            <a href="{{ route('admin.permissions.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع للقائمة') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.permissions.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full rounded-lg border-gray-300 @error('name') border-red-500 @enderror"
                                   placeholder="{{ trans('messages.e.g., View Scooters') }}">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Slug') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="slug" value="{{ old('slug') }}" required
                                   class="w-full rounded-lg border-gray-300 @error('slug') border-red-500 @enderror"
                                   placeholder="{{ trans('messages.e.g., view-scooters') }}">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ trans('messages.Lowercase, no spaces, use hyphens') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Group') }}
                            </label>
                            <input type="text" name="group" value="{{ old('group') }}"
                                   class="w-full rounded-lg border-gray-300 @error('group') border-red-500 @enderror"
                                   placeholder="{{ trans('messages.e.g., scooters, users, trips') }}">
                            @error('group')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Description') }}
                            </label>
                            <textarea name="description" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('description') border-red-500 @enderror"
                                      placeholder="{{ trans('messages.Permission description...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.permissions.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.إلغاء') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.حفظ') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

