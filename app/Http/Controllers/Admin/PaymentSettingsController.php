<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        $settings = $this->getSettings();
        $connectionStatus = null;
        
        // Check connection status if enabled
        if ($settings['paymob_enabled'] && !empty($settings['paymob_api_key'])) {
            try {
                $this->testPaymobConnection($settings);
                $connectionStatus = [
                    'success' => true,
                    'message' => 'Connection successful! Paymob API is accessible.',
                ];
            } catch (\Exception $e) {
                $connectionStatus = [
                    'success' => false,
                    'message' => 'Connection failed: ' . $e->getMessage(),
                ];
            }
        }

        return view('admin.payment-settings.index', compact('settings', 'connectionStatus'));
    }

    public function testConnection(Request $request)
    {
        $settings = $this->getSettings();

        if (!$settings['paymob_enabled']) {
            return response()->json([
                'success' => false,
                'message' => 'Paymob is not enabled. Please enable it first.',
            ], 400);
        }

        if (empty($settings['paymob_api_key'])) {
            return response()->json([
                'success' => false,
                'message' => 'API Key is missing. Please enter your Paymob API Key.',
            ], 400);
        }

        try {
            $this->testPaymobConnection($settings);
            
            // Get additional info if connection successful
            $token = $this->getAuthToken($settings['paymob_api_key']);
            
            return response()->json([
                'success' => true,
                'message' => 'Connection successful! Paymob API is accessible.',
                'details' => [
                    'api_key_length' => strlen($settings['paymob_api_key']),
                    'integration_id' => $settings['paymob_integration_id'] ?? 'Not set',
                    'merchant_id' => $settings['paymob_merchant_id'] ?? 'Not set',
                    'test_mode' => $settings['paymob_test_mode'] ? 'Enabled' : 'Disabled',
                    'token_received' => !empty($token),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    private function getAuthToken(string $apiKey): ?string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post('https://accept.paymob.com/api/auth/tokens', [
                    'api_key' => $apiKey,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $result = $response->json();
            return $result['token'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'paymob_enabled' => ['sometimes', 'boolean'],
            'paymob_api_key' => ['required_if:paymob_enabled,1', 'string', 'max:2000'], // Increased to support longer API keys
            'paymob_integration_id' => ['required_if:paymob_enabled,1', 'string', 'max:255'],
            'paymob_hmac_key' => ['required_if:paymob_enabled,1', 'string', 'max:2000'], // Increased to support longer HMAC keys
            'paymob_merchant_id' => ['required_if:paymob_enabled,1', 'string', 'max:255'],
            'paymob_iframe_id' => ['required_if:paymob_enabled,1', 'string', 'max:50'],
            'paymob_test_mode' => ['sometimes', 'boolean'],
            'paymob_callback_url' => ['nullable', 'url', 'max:500'],
        ]);

        // Update settings
        foreach ($data as $key => $value) {
            DB::table('payment_settings')
                ->where('key', $key)
                ->update([
                    'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'updated_at' => now(),
                ]);
        }

        // Test connection if enabled
        if ($request->boolean('paymob_enabled')) {
            try {
                $this->testPaymobConnection($data);
                return redirect()
                    ->route('admin.payment-settings.index')
                    ->with('status', trans('messages.Payment settings updated successfully. Paymob connection test passed.'));
            } catch (\Exception $e) {
                return redirect()
                    ->route('admin.payment-settings.index')
                    ->with('error', trans('messages.Payment settings updated, but Paymob connection test failed: :error', ['error' => $e->getMessage()]));
            }
        }

        return redirect()
            ->route('admin.payment-settings.index')
            ->with('status', trans('messages.Payment settings updated successfully.'));
    }

    private function getSettings(): array
    {
        $settings = DB::table('payment_settings')
            ->pluck('value', 'key')
            ->toArray();

        return [
            'paymob_enabled' => (bool) ($settings['paymob_enabled'] ?? false),
            'paymob_api_key' => $settings['paymob_api_key'] ?? '',
            'paymob_integration_id' => $settings['paymob_integration_id'] ?? '',
            'paymob_hmac_key' => $settings['paymob_hmac_key'] ?? '',
            'paymob_merchant_id' => $settings['paymob_merchant_id'] ?? '',
            'paymob_iframe_id' => $settings['paymob_iframe_id'] ?? '780724',
            'paymob_test_mode' => (bool) ($settings['paymob_test_mode'] ?? true),
            'paymob_callback_url' => $settings['paymob_callback_url'] ?? '',
        ];
    }

    private function testPaymobConnection(array $data): void
    {
        // Test Paymob API connection
        $apiKey = $data['paymob_api_key'] ?? '';
        
        if (empty($apiKey)) {
            throw new \Exception('API Key is required');
        }

        // Make a test API call to Paymob using Laravel HTTP client
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->post('https://accept.paymob.com/api/auth/tokens', [
                    'api_key' => $apiKey,
                ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                if ($statusCode === 401) {
                    throw new \Exception('Invalid API Key. Please check your credentials.');
                } elseif ($statusCode === 400) {
                    throw new \Exception('Bad request. API Key format may be incorrect.');
                } else {
                    throw new \Exception("HTTP {$statusCode}: " . ($errorBody ?: 'Unknown error'));
                }
            }

            $result = $response->json();

            if (!isset($result['token'])) {
                throw new \Exception('Invalid response from Paymob API. Token not received.');
            }

            // Verify token is not empty
            if (empty($result['token'])) {
                throw new \Exception('Received empty token from Paymob API.');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Unable to connect to Paymob servers. Please check your internet connection.');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'API Key')) {
                throw $e;
            }
            throw new \Exception('Failed to connect to Paymob API: ' . $e->getMessage());
        }
    }
}
