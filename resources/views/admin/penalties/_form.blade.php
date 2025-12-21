@php
    $types = [
        'zone_exit' => trans('messages.Zone Exit'),
        'forbidden_parking' => trans('messages.Forbidden Parking'),
        'unlocked_scooter' => trans('messages.Unlocked Scooter'),
        'other' => trans('messages.Other'),
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.User') }} <span class="text-red-500">*</span>
        </label>
        <select name="user_id" id="user_id" required
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary user-select">
            <option value="">{{ trans('messages.Select User') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $penalty->user_id ?? '') == $user->id ? 'selected' : '' }}
                        data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                        data-phone="{{ $user->phone ?? '' }}"
                        data-university="{{ $user->university_id ?? '' }}">
                    {{ $user->name }} ({{ $user->email }})
                    @if($user->phone)
                        - {{ $user->phone }}
                    @endif
                    @if($user->university_id)
                        - {{ trans('messages.University ID') }}: {{ $user->university_id }}
                    @endif
                </option>
            @endforeach
        </select>
        @error('user_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Type') }} <span class="text-red-500">*</span>
        </label>
        <select name="type" id="type" required
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @foreach($types as $value => $label)
                <option value="{{ $value }}" {{ old('type', $penalty->type ?? 'other') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="trip_id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Trip (Optional)') }}
        </label>
        <select name="trip_id" id="trip_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            <option value="">{{ trans('messages.No Trip') }}</option>
            @foreach($trips as $trip)
                <option value="{{ $trip->id }}" {{ old('trip_id', $penalty->trip_id ?? '') == $trip->id ? 'selected' : '' }}>
                    Trip #{{ $trip->id }} - {{ $trip->user->name }} - {{ $trip->start_time->format('Y-m-d H:i') }}
                </option>
            @endforeach
        </select>
        @error('trip_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="scooter_id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Scooter (Optional)') }}
        </label>
        <select name="scooter_id" id="scooter_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            <option value="">{{ trans('messages.No Scooter') }}</option>
            @foreach($scooters as $scooter)
                <option value="{{ $scooter->id }}" {{ old('scooter_id', $penalty->scooter_id ?? '') == $scooter->id ? 'selected' : '' }}>
                    {{ $scooter->code }}
                </option>
            @endforeach
        </select>
        @error('scooter_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Title') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="title" id="title" value="{{ old('title', $penalty->title ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('title')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Description') }}
        </label>
        <textarea name="description" id="description" rows="3"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('description', $penalty->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Amount (EGP)') }} <span class="text-red-500">*</span>
        </label>
        <input type="number" step="0.01" min="0" name="amount" id="amount"
               value="{{ old('amount', $penalty->amount ?? '0') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('amount')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    @if(isset($penalty))
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                {{ trans('messages.Status') }} <span class="text-red-500">*</span>
            </label>
            <select name="status" id="status" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                <option value="pending" {{ old('status', $penalty->status) === 'pending' ? 'selected' : '' }}>{{ trans('messages.Pending') }}</option>
                <option value="paid" {{ old('status', $penalty->status) === 'paid' ? 'selected' : '' }}>{{ trans('messages.Paid') }}</option>
                <option value="waived" {{ old('status', $penalty->status) === 'waived' ? 'selected' : '' }}>{{ trans('messages.Waived') }}</option>
                <option value="cancelled" {{ old('status', $penalty->status) === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
            </select>
            @error('status')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>

