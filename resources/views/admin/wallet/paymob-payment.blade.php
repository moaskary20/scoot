<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('messages.Paymob Payment') }}</title>
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f3f4f6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .header h2 {
            margin: 0 0 10px 0;
            color: #1f2937;
        }
        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .payment-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .payment-iframe {
            width: 100%;
            height: 800px;
            border: none;
            display: block;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        .cancel-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .cancel-btn:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ trans('messages.Paymob Payment') }}</h2>
            <p>{{ trans('messages.Complete your payment via Paymob') }}</p>
        </div>
        
        <div class="payment-container">
            <div id="loading" class="loading">
                {{ trans('messages.Loading payment form...') }}
            </div>
            <iframe 
                id="paymob-iframe"
                class="payment-iframe"
                src="{{ $paymentUrl }}" 
                frameborder="0"
                allow="payment *"
                allowfullscreen
                onload="document.getElementById('loading').style.display='none'">
            </iframe>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ route('admin.wallet.index') }}" class="cancel-btn">
                {{ trans('messages.Cancel Payment') }}
            </a>
        </div>
    </div>

    <script>
        // Redirect handling - Paymob will redirect back after payment
        // We'll handle this via the callback URL
        
        // Listen for messages from Paymob iframe (if supported)
        window.addEventListener('message', function(event) {
            // Only accept messages from Paymob domain
            if (event.origin.includes('paymob.com') || event.origin.includes('accept.paymob.com')) {
                if (event.data && typeof event.data === 'object') {
                    if (event.data.success || event.data.status === 'success') {
                        window.location.href = '{{ route('admin.wallet.paymob.return') }}?transaction_id={{ $transactionId }}';
                    } else if (event.data.error || event.data.status === 'failed') {
                        alert('{{ trans('messages.Payment failed. Please try again.') }}');
                    }
                }
            }
        });

        // Handle iframe load errors
        document.getElementById('paymob-iframe').addEventListener('error', function() {
            document.getElementById('loading').innerHTML = '{{ trans('messages.Failed to load payment form. Please try again.') }}';
        });

        // Timeout check
        setTimeout(function() {
            const iframe = document.getElementById('paymob-iframe');
            if (iframe && !iframe.contentWindow) {
                document.getElementById('loading').innerHTML = '{{ trans('messages.Failed to load payment form. Please try again.') }}';
            }
        }, 10000);
    </script>
</body>
</html>

