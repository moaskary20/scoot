@php
    $statuses = [
        'available' => trans('messages.Available'),
        'rented' => trans('messages.Rented'),
        'charging' => trans('messages.Charging'),
        'maintenance' => trans('messages.Maintenance'),
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.Scooter Code') }} *
        </label>
        <input type="text" name="code"
               value="{{ old('code', $scooter->code ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        @error('code')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.QR Code') }}
        </label>
        <input type="text" name="qr_code"
               value="{{ old('qr_code', $scooter->qr_code ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        @error('qr_code')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.Status') }} *
        </label>
        <select name="status"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('status', $scooter->status ?? 'available') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.Battery') }} (%) *
        </label>
        <input type="number" name="battery_percentage" min="0" max="100"
               value="{{ old('battery_percentage', $scooter->battery_percentage ?? 100) }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        @error('battery_percentage')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.Latitude') }}
        </label>
        <input type="text" name="latitude"
               value="{{ old('latitude', $scooter->latitude ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        @error('latitude')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.Longitude') }}
        </label>
        <input type="text" name="longitude"
               value="{{ old('longitude', $scooter->longitude ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        @error('longitude')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.Device IMEI') }}
        </label>
        <input type="text" name="device_imei"
               value="{{ old('device_imei', $scooter->device_imei ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        @error('device_imei')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            {{ trans('messages.Firmware Version') }}
        </label>
        <input type="text" name="firmware_version"
               value="{{ old('firmware_version', $scooter->firmware_version ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        @error('firmware_version')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_locked" value="1"
               @checked(old('is_locked', $scooter->is_locked ?? true))>
        <span>{{ trans('messages.Locked by default') }}</span>
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $scooter->is_active ?? true))>
        <span>{{ trans('messages.Active in system') }}</span>
    </label>
</div>



