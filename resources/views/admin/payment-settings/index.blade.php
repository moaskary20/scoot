<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Payment Settings') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.Configure Paymob payment gateway settings') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Connection Status -->
            @if(isset($connectionStatus))
                <div class="mb-4 rounded-lg px-4 py-3 text-sm {{ $connectionStatus['success'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    <div class="flex items-center gap-2">
                        @if($connectionStatus['success'])
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        <span><strong>Connection Status:</strong> {{ $connectionStatus['message'] }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" dir="rtl">
                <form action="{{ route('admin.payment-settings.update') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Paymob Enable/Disable -->
                        <div>
                            <h3 class="text-sm font-semibold text-secondary mb-4">Paymob Settings</h3>
                            
                            <div class="flex items-center mb-4">
                                <input type="checkbox" name="paymob_enabled" id="paymob_enabled" value="1"
                                       {{ old('paymob_enabled', $settings['paymob_enabled'] ?? false) ? 'checked' : '' }}
                                       class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                <label for="paymob_enabled" class="ml-2 block text-sm text-gray-700">
                                    Enable Paymob Payment Gateway
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">
                                Enable or disable Paymob payment gateway integration
                            </p>
                        </div>

                        <!-- API Key -->
                        <div>
                            <label for="paymob_api_key" class="block text-sm font-medium text-gray-700 mb-1">
                                Paymob API Key <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="paymob_api_key" id="paymob_api_key"
                                   value="{{ old('paymob_api_key', $settings['paymob_api_key'] ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                   placeholder="Enter your Paymob API Key">
                            <p class="mt-1 text-xs text-gray-500">
                                Your Paymob API Key from the dashboard
                            </p>
                            @error('paymob_api_key')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Integration ID -->
                        <div>
                            <label for="paymob_integration_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Paymob Integration ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="paymob_integration_id" id="paymob_integration_id"
                                   value="{{ old('paymob_integration_id', $settings['paymob_integration_id'] ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                   placeholder="Enter your Paymob Integration ID">
                            <p class="mt-1 text-xs text-gray-500">
                                Your Paymob Integration ID from the dashboard
                            </p>
                            @error('paymob_integration_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- HMAC Key -->
                        <div>
                            <label for="paymob_hmac_key" class="block text-sm font-medium text-gray-700 mb-1">
                                Paymob HMAC Key <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="paymob_hmac_key" id="paymob_hmac_key"
                                   value="{{ old('paymob_hmac_key', $settings['paymob_hmac_key'] ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                   placeholder="Enter your Paymob HMAC Key">
                            <p class="mt-1 text-xs text-gray-500">
                                Your Paymob HMAC Key for secure callback verification
                            </p>
                            @error('paymob_hmac_key')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Merchant ID -->
                        <div>
                            <label for="paymob_merchant_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Paymob Merchant ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="paymob_merchant_id" id="paymob_merchant_id"
                                   value="{{ old('paymob_merchant_id', $settings['paymob_merchant_id'] ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                   placeholder="Enter your Paymob Merchant ID">
                            <p class="mt-1 text-xs text-gray-500">
                                Your Paymob Merchant ID from the dashboard
                            </p>
                            @error('paymob_merchant_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Test Mode -->
                        <div class="flex items-center">
                            <input type="checkbox" name="paymob_test_mode" id="paymob_test_mode" value="1"
                                   {{ old('paymob_test_mode', $settings['paymob_test_mode'] ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="paymob_test_mode" class="ml-2 block text-sm text-gray-700">
                                Test Mode
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mb-4">
                            Enable test mode for testing payments without real transactions
                        </p>

                        <!-- Iframe ID -->
                        <div>
                            <label for="paymob_iframe_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Paymob Iframe ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="paymob_iframe_id" id="paymob_iframe_id"
                                   value="{{ old('paymob_iframe_id', $settings['paymob_iframe_id'] ?? '780724') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                   placeholder="780724" required>
                            <p class="mt-1 text-xs text-gray-500">
                                Your Paymob Iframe ID from the dashboard (e.g., 780724)
                            </p>
                            @error('paymob_iframe_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Callback URL -->
                        <div>
                            <label for="paymob_callback_url" class="block text-sm font-medium text-gray-700 mb-1">
                                Callback URL
                            </label>
                            <input type="url" name="paymob_callback_url" id="paymob_callback_url"
                                   value="{{ old('paymob_callback_url', $settings['paymob_callback_url'] ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                   placeholder="https://yourdomain.com/payment/callback">
                            <p class="mt-1 text-xs text-gray-500">
                                URL where Paymob will send payment callbacks (optional)
                            </p>
                            @error('paymob_callback_url')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-xs text-blue-700">
                                    <strong>Note:</strong> After saving, the system will test the connection to Paymob API. Make sure all credentials are correct.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-start gap-3">
                        <button type="button" id="testConnectionBtn"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Test Connection
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Test Connection Result -->
            <div id="testResult" class="mt-4 hidden"></div>
        </div>
    </div>

    <script>
        document.getElementById('testConnectionBtn').addEventListener('click', function() {
            const btn = this;
            const resultDiv = document.getElementById('testResult');
            
            btn.disabled = true;
            btn.textContent = 'Testing...';
            resultDiv.classList.add('hidden');
            
            fetch('{{ route('admin.payment-settings.test-connection') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'Test Connection';
                
                resultDiv.classList.remove('hidden');
                
                if (data.success) {
                    resultDiv.className = 'mt-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm';
                    let html = '<div class="flex items-start gap-2"><svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg><div><strong>✓ Connection Successful!</strong><p class="mt-1">' + data.message + '</p>';
                    
                    if (data.details) {
                        html += '<div class="mt-2 text-xs space-y-1"><p><strong>API Key Length:</strong> ' + data.details.api_key_length + ' characters</p>';
                        html += '<p><strong>Integration ID:</strong> ' + data.details.integration_id + '</p>';
                        html += '<p><strong>Merchant ID:</strong> ' + data.details.merchant_id + '</p>';
                        html += '<p><strong>Test Mode:</strong> ' + data.details.test_mode + '</p>';
                        html += '<p><strong>Token Received:</strong> ' + (data.details.token_received ? 'Yes' : 'No') + '</p></div>';
                    }
                    
                    html += '</div></div>';
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.className = 'mt-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm';
                    resultDiv.innerHTML = '<div class="flex items-start gap-2"><svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg><div><strong>✗ Connection Failed</strong><p class="mt-1">' + data.message + '</p></div></div>';
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.textContent = 'Test Connection';
                resultDiv.classList.remove('hidden');
                resultDiv.className = 'mt-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm';
                resultDiv.innerHTML = '<div class="flex items-start gap-2"><svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg><div><strong>✗ Error</strong><p class="mt-1">An error occurred: ' + error.message + '</p></div></div>';
            });
        });
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

