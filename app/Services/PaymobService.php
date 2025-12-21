<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    private string $apiKey;
    private int $integrationId;
    private string $hmacKey;
    private string $merchantId;
    private bool $testMode;
    private ?string $authToken = null;

    public function __construct()
    {
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $settings = DB::table('payment_settings')
            ->whereIn('key', [
                'paymob_api_key',
                'paymob_integration_id',
                'paymob_hmac_key',
                'paymob_merchant_id',
                'paymob_test_mode',
            ])
            ->pluck('value', 'key')
            ->toArray();

        $this->apiKey = $settings['paymob_api_key'] ?? '';
        $this->integrationId = (int) ($settings['paymob_integration_id'] ?? 0);
        $this->hmacKey = $settings['paymob_hmac_key'] ?? '';
        $this->merchantId = $settings['paymob_merchant_id'] ?? '';
        $this->testMode = (bool) ($settings['paymob_test_mode'] ?? true);
    }

    /**
     * Get authentication token from Paymob
     */
    public function getAuthToken(): string
    {
        if ($this->authToken) {
            return $this->authToken;
        }

        try {
            $response = Http::timeout(10)->post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to authenticate with Paymob: ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['token'])) {
                throw new \Exception('Invalid response from Paymob API');
            }

            $this->authToken = $data['token'];
            return $this->authToken;
        } catch (\Exception $e) {
            Log::error('Paymob authentication failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create payment order
     */
    public function createOrder(float $amount, array $items = [], array $billingData = []): array
    {
        $token = $this->getAuthToken();

        $orderData = [
            'auth_token' => $token,
            'delivery_needed' => 'false',
            'amount_cents' => (int) ($amount * 100), // Convert to cents
            'currency' => 'EGP',
            'items' => $items,
        ];

        if (!empty($billingData)) {
            $orderData['billing_data'] = $billingData;
        }

        try {
            $response = Http::timeout(10)->post('https://accept.paymob.com/api/ecommerce/orders', $orderData);

            if (!$response->successful()) {
                throw new \Exception('Failed to create order: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Paymob order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get payment key for payment page
     */
    public function getPaymentKey(int $orderId, float $amount, array $billingData = [], ?string $callbackUrl = null): string
    {
        $token = $this->getAuthToken();

        $paymentKeyData = [
            'auth_token' => $token,
            'amount_cents' => (int) ($amount * 100),
            'expiration' => 3600, // 1 hour
            'order_id' => $orderId,
            'billing_data' => $billingData,
            'currency' => 'EGP',
            'integration_id' => $this->integrationId,
        ];

        // Add callback URL if provided
        if ($callbackUrl) {
            $paymentKeyData['lock_order_when_paid'] = 'false';
            // Add return URL for redirect after payment
            $paymentKeyData['return_url'] = $callbackUrl;
        }

        try {
            $response = Http::timeout(10)->post('https://accept.paymob.com/api/acceptance/payment_keys', $paymentKeyData);

            if (!$response->successful()) {
                throw new \Exception('Failed to get payment key: ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['token'])) {
                throw new \Exception('Invalid response from Paymob API');
            }

            return $data['token'];
        } catch (\Exception $e) {
            Log::error('Paymob payment key generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get callback URL from settings
     */
    public function getCallbackUrl(): ?string
    {
        $callbackUrl = DB::table('payment_settings')
            ->where('key', 'paymob_callback_url')
            ->value('value');

        if (empty($callbackUrl)) {
            // Default callback URL
            $callbackUrl = route('admin.wallet.paymob.callback');
        }

        return $callbackUrl;
    }

    /**
     * Verify payment callback HMAC
     */
    public function verifyCallback(array $callbackData): bool
    {
        if (empty($this->hmacKey)) {
            return false;
        }

        $hmac = $callbackData['hmac'] ?? '';
        $amountCents = $callbackData['amount_cents'] ?? 0;
        $createdAt = $callbackData['created_at'] ?? '';
        $currency = $callbackData['currency'] ?? '';
        $errorOccurred = $callbackData['error_occurred'] ?? false;
        $hasParentTransaction = $callbackData['has_parent_transaction'] ?? false;
        $id = $callbackData['id'] ?? 0;
        $integrationId = $callbackData['integration_id'] ?? 0;
        $is3DSecure = $callbackData['is_3d_secure'] ?? false;
        $isAuth = $callbackData['is_auth'] ?? false;
        $isCapture = $callbackData['is_capture'] ?? false;
        $isRefunded = $callbackData['is_refunded'] ?? false;
        $isStandaloneRefund = $callbackData['is_standalone_refund'] ?? false;
        $isVoided = $callbackData['is_voided'] ?? false;
        $orderId = $callbackData['order']['id'] ?? 0;
        $owner = $callbackData['owner'] ?? 0;
        $pending = $callbackData['pending'] ?? false;
        $sourceDataPan = $callbackData['source_data']['pan'] ?? '';
        $sourceDataSubType = $callbackData['source_data']['sub_type'] ?? '';
        $sourceDataType = $callbackData['source_data']['type'] ?? '';
        $success = $callbackData['success'] ?? false;

        $stringToHash = "{$amountCents}{$createdAt}{$currency}{$errorOccurred}{$hasParentTransaction}{$id}{$integrationId}{$is3DSecure}{$isAuth}{$isCapture}{$isRefunded}{$isStandaloneRefund}{$isVoided}{$orderId}{$owner}{$pending}{$sourceDataPan}{$sourceDataSubType}{$sourceDataType}{$success}";

        $calculatedHmac = hash_hmac('sha512', $stringToHash, $this->hmacKey);

        return hash_equals($calculatedHmac, $hmac);
    }

    /**
     * Check if Paymob is enabled
     */
    public function isEnabled(): bool
    {
        $enabled = DB::table('payment_settings')
            ->where('key', 'paymob_enabled')
            ->value('value');

        return (bool) ($enabled ?? false);
    }

    /**
     * Get payment URL
     * Uses the iframe ID configured in payment settings (default: 780724)
     */
    public function getPaymentUrl(string $paymentKey): string
    {
        // Get iframe ID from settings
        $iframeId = DB::table('payment_settings')
            ->where('key', 'paymob_iframe_id')
            ->value('value');

        // If no iframe ID is set, use default (780724 as provided by user)
        if (empty($iframeId)) {
            $iframeId = '780724';
        }

        // Return payment URL with iframe
        // Format: https://accept.paymob.com/api/acceptance/iframes/{iframe_id}?payment_token={payment_key}
        return "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentKey}";
    }
}

