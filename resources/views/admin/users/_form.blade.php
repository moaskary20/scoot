@php
    $loyaltyLevels = [
        'bronze' => trans('messages.Bronze'),
        'silver' => trans('messages.Silver'),
        'gold' => trans('messages.Gold'),
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
               required>
        @error('name')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Email') }} <span class="text-red-500">*</span>
        </label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
               required>
        @error('email')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Phone') }}
        </label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('phone')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            {{ isset($user) ? trans('messages.New Password (leave blank to keep current)') : trans('messages.Password') }} <span class="text-red-500">*</span>
        </label>
        <input type="password" name="password" id="password"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
               {{ !isset($user) ? 'required' : '' }}>
        @error('password')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    @if(isset($user))
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                {{ trans('messages.Confirm Password') }}
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        </div>
    @else
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                {{ trans('messages.Confirm Password') }} <span class="text-red-500">*</span>
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                   required>
        </div>
    @endif

    <div>
        <label for="wallet_balance" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Wallet Balance') }} ({{ trans('messages.EGP') }})
        </label>
        <input type="number" step="0.01" min="0" name="wallet_balance" id="wallet_balance"
               value="{{ old('wallet_balance', $user->wallet_balance ?? 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('wallet_balance')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="loyalty_points" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Loyalty Points') }}
        </label>
        <input type="number" min="0" name="loyalty_points" id="loyalty_points"
               value="{{ old('loyalty_points', $user->loyalty_points ?? 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
        @error('loyalty_points')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="loyalty_level" class="block text-sm font-medium text-gray-700 mb-1">
            {{ trans('messages.Loyalty Level') }}
        </label>
        <select name="loyalty_level" id="loyalty_level"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @foreach($loyaltyLevels as $value => $label)
                <option value="{{ $value }}" {{ old('loyalty_level', $user->loyalty_level ?? 'bronze') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('loyalty_level')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $user->is_active ?? true))>
        <span>{{ trans('messages.Active in system') }}</span>
    </label>
</div>

