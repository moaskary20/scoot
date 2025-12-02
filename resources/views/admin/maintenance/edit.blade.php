<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Edit Maintenance Record') }} #{{ $maintenance->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تعديل سجل الصيانة') }}
                </p>
            </div>
            <a href="{{ route('admin.maintenance.show', $maintenance) }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.maintenance.update', $maintenance) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Scooter') }}
                            </label>
                            <div class="text-sm text-gray-700 font-semibold">
                                {{ $maintenance->scooter->code }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Maintenance Type') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="type" required class="w-full rounded-lg border-gray-300 @error('type') border-red-500 @enderror">
                                <option value="repair" {{ old('type', $maintenance->type) === 'repair' ? 'selected' : '' }}>{{ trans('messages.Repair') }}</option>
                                <option value="scheduled" {{ old('type', $maintenance->type) === 'scheduled' ? 'selected' : '' }}>{{ trans('messages.Scheduled Maintenance') }}</option>
                                <option value="battery_replacement" {{ old('type', $maintenance->type) === 'battery_replacement' ? 'selected' : '' }}>{{ trans('messages.Battery Replacement') }}</option>
                                <option value="firmware_update" {{ old('type', $maintenance->type) === 'firmware_update' ? 'selected' : '' }}>{{ trans('messages.Firmware Update') }}</option>
                                <option value="inspection" {{ old('type', $maintenance->type) === 'inspection' ? 'selected' : '' }}>{{ trans('messages.Inspection') }}</option>
                                <option value="other" {{ old('type', $maintenance->type) === 'other' ? 'selected' : '' }}>{{ trans('messages.Other') }}</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Title') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title', $maintenance->title) }}" required
                                   class="w-full rounded-lg border-gray-300 @error('title') border-red-500 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Description') }}
                            </label>
                            <textarea name="description" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('description') border-red-500 @enderror">{{ old('description', $maintenance->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Fault Details') }}
                            </label>
                            <textarea name="fault_details" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('fault_details') border-red-500 @enderror">{{ old('fault_details', $maintenance->fault_details) }}</textarea>
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
                                    <option value="low" {{ old('priority', $maintenance->priority) === 'low' ? 'selected' : '' }}>{{ trans('messages.Low') }}</option>
                                    <option value="medium" {{ old('priority', $maintenance->priority) === 'medium' ? 'selected' : '' }}>{{ trans('messages.Medium') }}</option>
                                    <option value="high" {{ old('priority', $maintenance->priority) === 'high' ? 'selected' : '' }}>{{ trans('messages.High') }}</option>
                                    <option value="urgent" {{ old('priority', $maintenance->priority) === 'urgent' ? 'selected' : '' }}>{{ trans('messages.Urgent') }}</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Status') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="status" required class="w-full rounded-lg border-gray-300 @error('status') border-red-500 @enderror">
                                    <option value="pending" {{ old('status', $maintenance->status) === 'pending' ? 'selected' : '' }}>{{ trans('messages.Pending') }}</option>
                                    <option value="in_progress" {{ old('status', $maintenance->status) === 'in_progress' ? 'selected' : '' }}>{{ trans('messages.In Progress') }}</option>
                                    <option value="completed" {{ old('status', $maintenance->status) === 'completed' ? 'selected' : '' }}>{{ trans('messages.Completed') }}</option>
                                    <option value="cancelled" {{ old('status', $maintenance->status) === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Scheduled At') }}
                                </label>
                                <input type="datetime-local" name="scheduled_at" 
                                       value="{{ old('scheduled_at', $maintenance->scheduled_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full rounded-lg border-gray-300 @error('scheduled_at') border-red-500 @enderror">
                                @error('scheduled_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Started At') }}
                                </label>
                                <input type="datetime-local" name="started_at" 
                                       value="{{ old('started_at', $maintenance->started_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full rounded-lg border-gray-300 @error('started_at') border-red-500 @enderror">
                                @error('started_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Completed At') }}
                                </label>
                                <input type="datetime-local" name="completed_at" 
                                       value="{{ old('completed_at', $maintenance->completed_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full rounded-lg border-gray-300 @error('completed_at') border-red-500 @enderror">
                                @error('completed_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Technician Name') }}
                                </label>
                                <input type="text" name="technician_name" value="{{ old('technician_name', $maintenance->technician_name) }}"
                                       class="w-full rounded-lg border-gray-300 @error('technician_name') border-red-500 @enderror">
                                @error('technician_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Technician Phone') }}
                                </label>
                                <input type="text" name="technician_phone" value="{{ old('technician_phone', $maintenance->technician_phone) }}"
                                       class="w-full rounded-lg border-gray-300 @error('technician_phone') border-red-500 @enderror">
                                @error('technician_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Technician Email') }}
                                </label>
                                <input type="email" name="technician_email" value="{{ old('technician_email', $maintenance->technician_email) }}"
                                       class="w-full rounded-lg border-gray-300 @error('technician_email') border-red-500 @enderror">
                                @error('technician_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Estimated Cost') }}
                                </label>
                                <input type="number" name="estimated_cost" value="{{ old('estimated_cost', $maintenance->estimated_cost) }}" step="0.01" min="0"
                                       class="w-full rounded-lg border-gray-300 @error('estimated_cost') border-red-500 @enderror">
                                @error('estimated_cost')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Actual Cost') }}
                                </label>
                                <input type="number" name="actual_cost" value="{{ old('actual_cost', $maintenance->actual_cost) }}" step="0.01" min="0"
                                       class="w-full rounded-lg border-gray-300 @error('actual_cost') border-red-500 @enderror">
                                @error('actual_cost')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Technician Notes') }}
                            </label>
                            <textarea name="technician_notes" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('technician_notes') border-red-500 @enderror">{{ old('technician_notes', $maintenance->technician_notes) }}</textarea>
                            @error('technician_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Resolution Notes') }}
                            </label>
                            <textarea name="resolution_notes" rows="3"
                                      class="w-full rounded-lg border-gray-300 @error('resolution_notes') border-red-500 @enderror">{{ old('resolution_notes', $maintenance->resolution_notes) }}</textarea>
                            @error('resolution_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ trans('messages.Parts Replaced') }}
                            </label>
                            <textarea name="parts_replaced" rows="2"
                                      class="w-full rounded-lg border-gray-300 @error('parts_replaced') border-red-500 @enderror"
                                      placeholder="{{ trans('messages.List parts replaced, separated by commas') }}">{{ old('parts_replaced', $maintenance->parts_replaced) }}</textarea>
                            @error('parts_replaced')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Quality Rating') }}
                                </label>
                                <select name="quality_rating" class="w-full rounded-lg border-gray-300 @error('quality_rating') border-red-500 @enderror">
                                    <option value="">{{ trans('messages.Select Rating') }}</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('quality_rating', $maintenance->quality_rating) == $i ? 'selected' : '' }}>
                                            {{ $i }} {{ trans('messages.Stars') }}
                                        </option>
                                    @endfor
                                </select>
                                @error('quality_rating')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Quality Notes') }}
                                </label>
                                <textarea name="quality_notes" rows="2"
                                          class="w-full rounded-lg border-gray-300 @error('quality_notes') border-red-500 @enderror">{{ old('quality_notes', $maintenance->quality_notes) }}</textarea>
                                @error('quality_notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.maintenance.show', $maintenance) }}"
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

