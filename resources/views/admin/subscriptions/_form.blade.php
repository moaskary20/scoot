@php
    $types = [
        'minutes' => trans('messages.Minutes Package'),
        'unlimited' => trans('messages.Unlimited'),
    ];
    $billingPeriods = [
        'daily' => trans('messages.Daily'),
        'weekly' => trans('messages.Weekly'),
        'monthly' => trans('messages.Monthly'),
        'yearly' => trans('messages.Yearly'),
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.User') }} <span class="text-red-500">*</span>
        </label>
        <select name="user_id" id="user_id" required
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            <option value="">{{ trans('messages.Select User') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $subscription->user_id ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        @error('user_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Package Name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $subscription->name ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
               placeholder="{{ trans('messages.e.g., 30 Minutes Package') }}">
        @error('name')
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
                <option value="{{ $value }}" {{ old('type', $subscription->type ?? 'minutes') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div id="minutes_included_field">
        <label for="minutes_included" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Minutes Included') }} <span class="text-red-500">*</span>
        </label>
        <input type="number" min="1" name="minutes_included" id="minutes_included"
               value="{{ old('minutes_included', $subscription->minutes_included ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('minutes_included')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Price (EGP)') }} <span class="text-red-500">*</span>
        </label>
        <input type="number" step="0.01" min="0" name="price" id="price"
               value="{{ old('price', $subscription->price ?? '0') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('price')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="billing_period" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Billing Period') }} <span class="text-red-500">*</span>
        </label>
        <select name="billing_period" id="billing_period" required
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @foreach($billingPeriods as $value => $label)
                <option value="{{ $value }}" {{ old('billing_period', $subscription->billing_period ?? 'monthly') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('billing_period')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Starts At') }} <span class="text-red-500">*</span>
        </label>
        <input type="datetime-local" name="starts_at" id="starts_at"
               value="{{ old('starts_at', isset($subscription) && $subscription->starts_at ? $subscription->starts_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('starts_at')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Expires At') }} <span class="text-red-500">*</span>
        </label>
        <input type="datetime-local" name="expires_at" id="expires_at"
               value="{{ old('expires_at', isset($subscription) && $subscription->expires_at ? $subscription->expires_at->format('Y-m-d\TH:i') : now()->addMonth()->format('Y-m-d\TH:i')) }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('expires_at')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="coupon_id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Coupon (Optional)') }}
        </label>
        <select name="coupon_id" id="coupon_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            <option value="">{{ trans('messages.No Coupon') }}</option>
            @foreach($coupons as $coupon)
                <option value="{{ $coupon->id }}" {{ old('coupon_id', $subscription->coupon_id ?? '') == $coupon->id ? 'selected' : '' }}>
                    {{ $coupon->code }} - {{ $coupon->name }}
                </option>
            @endforeach
        </select>
        @error('coupon_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    @if(isset($subscription))
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                {{ trans('messages.Status') }} <span class="text-red-500">*</span>
            </label>
            <select name="status" id="status" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>{{ trans('messages.Active') }}</option>
                <option value="expired" {{ old('status', $subscription->status) === 'expired' ? 'selected' : '' }}>{{ trans('messages.Expired') }}</option>
                <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>{{ trans('messages.Cancelled') }}</option>
                <option value="suspended" {{ old('status', $subscription->status) === 'suspended' ? 'selected' : '' }}>{{ trans('messages.Suspended') }}</option>
            </select>
            @error('status')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Notes') }}
        </label>
        <textarea name="notes" id="notes" rows="3"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('notes', $subscription->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="auto_renew" value="1"
               @checked(old('auto_renew', $subscription->auto_renew ?? false))>
        <span>{{ trans('messages.Auto Renew') }}</span>
    </label>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeField = document.getElementById('type');
        const minutesField = document.getElementById('minutes_included_field');

        function updateFields() {
            if (typeField.value === 'unlimited') {
                minutesField.style.display = 'none';
                document.getElementById('minutes_included').removeAttribute('required');
            } else {
                minutesField.style.display = 'block';
                document.getElementById('minutes_included').setAttribute('required', 'required');
            }
        }

        typeField.addEventListener('change', updateFields);
        updateFields();
    });
</script>

