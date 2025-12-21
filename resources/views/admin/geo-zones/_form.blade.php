@php
    $types = [
        'allowed' => trans('messages.Allowed Zone'),
        'forbidden' => trans('messages.Forbidden Zone'),
        'parking' => trans('messages.Parking Zone'),
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name"
               value="{{ old('name', $zone->name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm"
               required>
        @error('name')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Type') }} <span class="text-red-500">*</span>
        </label>
        <select name="type"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm"
                required>
            @foreach($types as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('type', $zone->type ?? 'allowed') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Color') }} <span class="text-red-500">*</span>
        </label>
        <div class="flex items-center gap-3">
            <input type="color" name="color"
                   value="{{ old('color', $zone->color ?? '#FFD600') }}"
                   class="h-10 w-16 rounded border border-gray-300">
            <input type="text"
                   value="{{ old('color', $zone->color ?? '#FFD600') }}"
                   oninput="this.previousElementSibling.value = this.value"
                   onchange="this.previousElementSibling.value = this.value"
                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
        </div>
        @error('color')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-6">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $zone->is_active ?? true))>
            <span>{{ trans('messages.Active zone') }}</span>
        </label>
    </div>

    <div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-6">
            <input type="checkbox" name="allow_trip_start" value="1"
                   @checked(old('allow_trip_start', $zone->allow_trip_start ?? true))>
            <span>{{ trans('messages.Allow trip start') }}</span>
        </label>
        <p class="text-xs text-gray-500 mt-1">{{ trans('messages.Allow users to start trips in this zone') }}</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Price per minute') }} ({{ trans('messages.EGP') }}) <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               name="price_per_minute" 
               step="0.01" 
               min="0"
               value="{{ old('price_per_minute', $zone->price_per_minute ?? 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm"
               required>
        <p class="text-xs text-gray-500 mt-1">{{ trans('messages.The price charged per minute for trips in this zone') }}</p>
        @error('price_per_minute')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Trip start fee') }} ({{ trans('messages.EGP') }}) <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               name="trip_start_fee" 
               step="0.01" 
               min="0"
               value="{{ old('trip_start_fee', $zone->trip_start_fee ?? 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm"
               required>
        <p class="text-xs text-gray-500 mt-1">{{ trans('messages.The fee charged when starting a trip in this zone') }}</p>
        @error('trip_start_fee')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Description') }}
        </label>
        <textarea name="description" rows="2"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">{{ old('description', $zone->description ?? '') }}</textarea>
        @error('description')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ trans('messages.Draw Zone on Map') }} <span class="text-red-500">*</span>
    </label>
    <p class="text-xs text-gray-500 mb-2">
        {{ trans('messages.استخدم الأدوات على الخريطة لرسم Polygon يحدد حدود المنطقة. سيتم حفظ النقاط كـ JSON.') }}
    </p>
    <div id="geo-zone-map" class="w-full aspect-[16/9] rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden"></div>
    @error('polygon')
    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<input type="hidden" name="polygon" id="polygon-input"
       value="{{ old('polygon', isset($zone) ? json_encode($zone->polygon) : '') }}">

<input type="hidden" name="center_latitude" id="center-lat-input"
       value="{{ old('center_latitude', $zone->center_latitude ?? '') }}">
<input type="hidden" name="center_longitude" id="center-lng-input"
       value="{{ old('center_longitude', $zone->center_longitude ?? '') }}">


