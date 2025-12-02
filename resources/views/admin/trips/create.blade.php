<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Start New Trip') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.بدء رحلة جديدة') }}
                </p>
            </div>
            <a href="{{ route('admin.trips.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع لقائمة الرحلات') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.trips.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.User') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">{{ trans('messages.Select User') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="scooter_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Scooter') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="scooter_id" id="scooter_id" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">{{ trans('messages.Select Scooter') }}</option>
                                @foreach($scooters as $scooter)
                                    <option value="{{ $scooter->id }}" {{ old('scooter_id') == $scooter->id ? 'selected' : '' }}>
                                        {{ $scooter->code }} ({{ $scooter->status }}, Battery: {{ $scooter->battery_percentage }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('scooter_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_latitude" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Start Latitude') }}
                            </label>
                            <input type="number" step="0.0000001" name="start_latitude" id="start_latitude"
                                   value="{{ old('start_latitude') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('start_latitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_longitude" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Start Longitude') }}
                            </label>
                            <input type="number" step="0.0000001" name="start_longitude" id="start_longitude"
                                   value="{{ old('start_longitude') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('start_longitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Notes') }}
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.trips.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.إلغاء') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.بدء الرحلة') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

