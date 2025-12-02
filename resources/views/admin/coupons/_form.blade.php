@php
    $discountTypes = [
        'percentage' => trans('messages.Percentage (%)'),
        'fixed' => trans('messages.Fixed Amount (EGP)'),
    ];
    $applicableTo = [
        'trips' => trans('messages.Trips Only'),
        'subscriptions' => trans('messages.Subscriptions Only'),
        'all' => trans('messages.All'),
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Code') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="code" id="code" value="{{ old('code', $coupon->code ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary font-mono"
               placeholder="{{ trans('messages.Leave empty to auto-generate') }}" required>
        @error('code')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $coupon->name ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('name')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Discount Type') }} <span class="text-red-500">*</span>
        </label>
        <select name="discount_type" id="discount_type" required
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @foreach($discountTypes as $value => $label)
                <option value="{{ $value }}" {{ old('discount_type', $coupon->discount_type ?? 'percentage') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('discount_type')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-1">
            <span id="discount_value_label">{{ trans('messages.Discount Value') }}</span> <span class="text-red-500">*</span>
        </label>
        <input type="number" step="0.01" min="0" name="discount_value" id="discount_value"
               value="{{ old('discount_value', $coupon->discount_value ?? '0') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('discount_value')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div id="max_discount_field" style="display: none;">
        <label for="max_discount" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Max Discount (EGP)') }}
        </label>
        <input type="number" step="0.01" min="0" name="max_discount" id="max_discount"
               value="{{ old('max_discount', $coupon->max_discount ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('max_discount')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Min Amount (EGP)') }}
        </label>
        <input type="number" step="0.01" min="0" name="min_amount" id="min_amount"
               value="{{ old('min_amount', $coupon->min_amount ?? '0') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('min_amount')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="usage_limit" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Usage Limit (Total)') }}
        </label>
        <input type="number" min="1" name="usage_limit" id="usage_limit"
               value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
               placeholder="{{ trans('messages.Leave empty for unlimited') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('usage_limit')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="user_usage_limit" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.User Usage Limit') }}
        </label>
        <input type="number" min="1" name="user_usage_limit" id="user_usage_limit"
               value="{{ old('user_usage_limit', $coupon->user_usage_limit ?? '1') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('user_usage_limit')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="applicable_to" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Applicable To') }} <span class="text-red-500">*</span>
        </label>
        <select name="applicable_to" id="applicable_to" required
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @foreach($applicableTo as $value => $label)
                <option value="{{ $value }}" {{ old('applicable_to', $coupon->applicable_to ?? 'all') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('applicable_to')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Starts At') }}
        </label>
        <input type="datetime-local" name="starts_at" id="starts_at"
               value="{{ old('starts_at', isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('starts_at')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Expires At') }}
        </label>
        <input type="datetime-local" name="expires_at" id="expires_at"
               value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('expires_at')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Description') }}
        </label>
        <textarea name="description" id="description" rows="3"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('description', $coupon->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $coupon->is_active ?? true))>
        <span>{{ trans('messages.Active') }}</span>
    </label>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const discountType = document.getElementById('discount_type');
        const maxDiscountField = document.getElementById('max_discount_field');
        const discountValueLabel = document.getElementById('discount_value_label');

        function updateFields() {
            if (discountType.value === 'percentage') {
                maxDiscountField.style.display = 'block';
                discountValueLabel.textContent = '{{ trans('messages.Discount Percentage (%)') }}';
            } else {
                maxDiscountField.style.display = 'none';
                discountValueLabel.textContent = '{{ trans('messages.Discount Amount (EGP)') }}';
            }
        }

        discountType.addEventListener('change', updateFields);
        updateFields();
    });
</script>

