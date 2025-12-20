<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Loyalty Points Settings') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.Loyalty Points System Settings') }}
                </p>
            </div>
            <a href="{{ route('admin.loyalty.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.Back to Transactions') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.loyalty.settings.update') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Points Calculation') }}</h3>
                            <div>
                                <label for="points_per_minute" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ trans('messages.Points Per Minute') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="number" min="0" name="points_per_minute" id="points_per_minute"
                                       value="{{ old('points_per_minute', $pointsPerMinute) }}" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ trans('messages.Number of points the user earns per minute of trip') }}
                                </p>
                                @error('points_per_minute')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-secondary mb-4">{{ trans('messages.Loyalty Level Thresholds') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="bronze_threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Bronze Threshold') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" name="bronze_threshold" id="bronze_threshold"
                                           value="{{ old('bronze_threshold', $thresholds['bronze']) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Minimum points threshold for Bronze level') }}
                                    </p>
                                    @error('bronze_threshold')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="silver_threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Silver Threshold') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" name="silver_threshold" id="silver_threshold"
                                           value="{{ old('silver_threshold', $thresholds['silver']) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Minimum points threshold for Silver level') }}
                                    </p>
                                    @error('silver_threshold')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="gold_threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ trans('messages.Gold Threshold') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" name="gold_threshold" id="gold_threshold"
                                           value="{{ old('gold_threshold', $thresholds['gold']) }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans('messages.Minimum points threshold for Gold level') }}
                                    </p>
                                    @error('gold_threshold')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-xs text-amber-700">
                                    <strong>{{ trans('messages.Note:') }}</strong> {{ trans('messages.Make sure thresholds are in ascending order (Bronze ≤ Silver ≤ Gold). All user levels will be updated automatically upon save.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.loyalty.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

