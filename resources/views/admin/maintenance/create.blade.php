<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Send Scooter to Maintenance') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إرسال سكوتر للصيانة') }}
                </p>
            </div>
            <a href="{{ route('admin.maintenance.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع للقائمة') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.maintenance.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Scooter') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="scooter_id" required class="w-full rounded-lg border-gray-300 @error('scooter_id') border-red-500 @enderror">
                                <option value="">{{ trans('messages.Select Scooter') }}</option>
                                @foreach($scooters as $s)
                                    <option value="{{ $s->id }}" {{ (old('scooter_id') ?? ($scooter?->id ?? '')) == $s->id ? 'selected' : '' }}>
                                        {{ $s->code }} - {{ ucfirst($s->status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scooter_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(isset($scooterLogId))
                            <input type="hidden" name="scooter_log_id" value="{{ $scooterLogId }}">
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Maintenance Type') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="type" required class="w-full rounded-lg border-gray-300 @error('type') border-red-500 @enderror">
                                <option value="repair" {{ old('type') === 'repair' ? 'selected' : '' }}>{{ trans('messages.Repair') }}</option>
                                <option value="scheduled" {{ old('type') === 'scheduled' ? 'selected' : '' }}>{{ trans('messages.Scheduled Maintenance') }}</option>
                                <option value="battery_replacement" {{ old('type') === 'battery_replacement' ? 'selected' : '' }}>{{ trans('messages.Battery Replacement') }}</option>
                                <option value="firmware_update" {{ old('type') === 'firmware_update' ? 'selected' : '' }}>{{ trans('messages.Firmware Update') }}</option>
                                <option value="inspection" {{ old('type') === 'inspection' ? 'selected' : '' }}>{{ trans('messages.Inspection') }}</option>
                                <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>{{ trans('messages.Other') }}</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Title') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                   class="w-full rounded-lg border-gray-300 @error('title') border-red-500 @enderror"
                                   placeholder="{{ trans('messages.e.g., Battery replacement needed') }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Description') }}
                            </label>
                            <textarea name="description" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('description') border-red-500 @enderror"
                                      placeholder="{{ trans('messages.Detailed description of the maintenance needed...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Fault Details') }}
                            </label>
                            <textarea name="fault_details" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('fault_details') border-red-500 @enderror"
                                      placeholder="{{ trans('messages.Details about the fault or issue...') }}">{{ old('fault_details') }}</textarea>
                            @error('fault_details')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Priority') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="priority" required class="w-full rounded-lg border-gray-300 @error('priority') border-red-500 @enderror">
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ trans('messages.Low') }}</option>
                                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>{{ trans('messages.Medium') }}</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ trans('messages.High') }}</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>{{ trans('messages.Urgent') }}</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Scheduled At') }}
                                </label>
                                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                                       class="w-full rounded-lg border-gray-300 @error('scheduled_at') border-red-500 @enderror">
                                @error('scheduled_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Estimated Cost') }}
                            </label>
                            <input type="number" name="estimated_cost" value="{{ old('estimated_cost') }}" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 @error('estimated_cost') border-red-500 @enderror"
                                   placeholder="0.00">
                            @error('estimated_cost')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.maintenance.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.Send to Maintenance') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

