<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Edit Trip') }} #{{ $trip->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تعديل بيانات الرحلة') }}
                </p>
            </div>
            <a href="{{ route('admin.trips.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.trips.update', $trip) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.User') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $trip->user_id) == $user->id ? 'selected' : '' }}>
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
                                @foreach($scooters as $scooter)
                                    <option value="{{ $scooter->id }}" {{ old('scooter_id', $trip->scooter_id) == $scooter->id ? 'selected' : '' }}>
                                        {{ $scooter->code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scooter_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Start Time') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="start_time" id="start_time"
                                   value="{{ old('start_time', $trip->start_time->format('Y-m-d\TH:i')) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('start_time')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.End Time') }}
                            </label>
                            <input type="datetime-local" name="end_time" id="end_time"
                                   value="{{ old('end_time', $trip->end_time?->format('Y-m-d\TH:i')) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('end_time')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Duration (minutes)') }}
                            </label>
                            <input type="number" min="0" name="duration_minutes" id="duration_minutes"
                                   value="{{ old('duration_minutes', $trip->duration_minutes) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('duration_minutes')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Status') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <option value="active" {{ old('status', $trip->status) === 'active' ? 'selected' : '' }}>{{ trans('messages.Active') }}</option>
                                <option value="completed" {{ old('status', $trip->status) === 'completed' ? 'selected' : '' }}>{{ trans('messages.Completed') }}</option>
                                <option value="cancelled" {{ old('status', $trip->status) === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Total Cost (EGP)') }}
                            </label>
                            <input type="number" step="0.01" min="0" name="cost" id="cost"
                                   value="{{ old('cost', $trip->cost) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('cost')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="base_cost" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Base Cost (EGP)') }}
                            </label>
                            <input type="number" step="0.01" min="0" name="base_cost" id="base_cost"
                                   value="{{ old('base_cost', $trip->base_cost) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('base_cost')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Discount Amount (EGP)') }}
                            </label>
                            <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount"
                                   value="{{ old('discount_amount', $trip->discount_amount) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('discount_amount')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="penalty_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Penalty Amount (EGP)') }}
                            </label>
                            <input type="number" step="0.01" min="0" name="penalty_amount" id="penalty_amount"
                                   value="{{ old('penalty_amount', $trip->penalty_amount) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('penalty_amount')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_latitude" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Start Latitude') }}
                            </label>
                            <input type="number" step="0.0000001" name="start_latitude" id="start_latitude"
                                   value="{{ old('start_latitude', $trip->start_latitude) }}"
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
                                   value="{{ old('start_longitude', $trip->start_longitude) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('start_longitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_latitude" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.End Latitude') }}
                            </label>
                            <input type="number" step="0.0000001" name="end_latitude" id="end_latitude"
                                   value="{{ old('end_latitude', $trip->end_latitude) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('end_latitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_longitude" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.End Longitude') }}
                            </label>
                            <input type="number" step="0.0000001" name="end_longitude" id="end_longitude"
                                   value="{{ old('end_longitude', $trip->end_longitude) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('end_longitude')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="zone_exit_detected" value="1"
                                       @checked(old('zone_exit_detected', $trip->zone_exit_detected))>
                                <span>{{ trans('messages.Zone Exit Detected') }}</span>
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <label for="zone_exit_details" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Zone Exit Details') }}
                            </label>
                            <textarea name="zone_exit_details" id="zone_exit_details" rows="2"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('zone_exit_details', $trip->zone_exit_details) }}</textarea>
                            @error('zone_exit_details')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Notes') }}
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('notes', $trip->notes) }}</textarea>
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
                            {{ trans('messages.حفظ التغييرات') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

