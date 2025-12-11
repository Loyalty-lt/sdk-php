# Loyalty.lt PHP SDK

Official PHP SDK for [Loyalty.lt](https://loyalty.lt) Partner API.

[![Latest Version](https://img.shields.io/packagist/v/loyaltylt/sdk.svg)](https://packagist.org/packages/loyaltylt/sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/loyaltylt/sdk.svg)](https://packagist.org/packages/loyaltylt/sdk)
[![License](https://img.shields.io/packagist/l/loyaltylt/sdk.svg)](https://packagist.org/packages/loyaltylt/sdk)

## Installation

```bash
composer require loyaltylt/sdk
```

## Requirements

- PHP 8.1 or higher
- ext-json
- Guzzle HTTP client

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use LoyaltyLt\SDK\LoyaltySDK;

$sdk = new LoyaltySDK([
    'apiKey' => 'lty_your_api_key',
    'apiSecret' => 'your_api_secret',
    'environment' => 'production', // or 'staging'
    'locale' => 'lt',
]);

// Get shops
$shops = $sdk->getShops();
print_r($shops['data']);
```

## Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `apiKey` | string | required | Partner API Key |
| `apiSecret` | string | required | Partner API Secret |
| `environment` | string | `production` | `production` or `staging` |
| `locale` | string | `lt` | API locale (`lt`, `en`) |
| `timeout` | int | `30` | Request timeout in seconds |
| `retries` | int | `3` | Number of retry attempts |
| `debug` | bool | `false` | Enable debug logging |

## Features

### QR Login

Generate QR codes for customer authentication:

```php
// Generate QR login session
$session = $sdk->generateQrLogin('POS Terminal #1', $shopId);

echo $session['session_id'];
echo $session['qr_code']; // Deep link for QR code
echo $session['expires_at'];

// Poll for status (or use Ably real-time)
$status = $sdk->pollQrLogin($session['session_id']);

if ($status['status'] === 'authenticated') {
    $user = $status['user'];
    echo "Welcome, " . $user['name'];
}
```

### QR Card Scan (POS Customer Identification)

Identify customers via QR code:

```php
// Generate QR card scan session
$session = $sdk->generateQrCardSession('POS Terminal', $shopId);

// Display QR code to customer
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" 
    . urlencode($session['qr_code']);

// Poll for customer identification
$result = $sdk->pollQrCardStatus($session['session_id']);

if ($result['status'] === 'completed') {
    $card = $result['card_data'];
    echo "Customer: " . $card['user']['name'];
    echo "Points: " . $card['points_balance'];
}
```

### Real-time Updates with Ably

```php
// Get Ably token
$ablyToken = $sdk->getAblyToken($session['session_id']);

// Use with Ably client
echo $ablyToken['token'];
echo $ablyToken['channel'];
```

### Shops

```php
// Get all shops
$shops = $sdk->getShops();

// Filter shops
$shops = $sdk->getShops([
    'is_active' => true,
    'is_virtual' => false,
]);
```

### Loyalty Cards

```php
// Get cards
$cards = $sdk->getLoyaltyCards();

// Get single card
$card = $sdk->getLoyaltyCard(123);

// Get card by number
$cardInfo = $sdk->getLoyaltyCardInfo([
    'card_number' => '123-456-789'
]);

// Get points balance
$balance = $sdk->getPointsBalance([
    'card_id' => 123
]);
```

### Transactions & Points

```php
// Award points
$transaction = $sdk->createTransaction([
    'card_id' => 123,
    'amount' => 50.00,
    'points' => 50,
    'type' => 'earn',
    'description' => 'Purchase reward',
    'reference' => 'ORDER-12345',
]);

// Get transactions
$transactions = $sdk->getTransactions([
    'card_id' => 123,
    'type' => 'earn',
]);
```

### Offers

```php
// Get offers
$offers = $sdk->getOffers(['is_active' => true]);

// Create offer
$offer = $sdk->createOffer([
    'title' => 'Summer Sale',
    'description' => '20% off all items',
    'discount_type' => 'percentage',
    'discount_value' => 20,
    'start_date' => '2024-06-01',
    'end_date' => '2024-08-31',
]);

// Get categories
$categories = $sdk->getCategories();
```

### XML Import

```php
// Import offers from XML
$result = $sdk->importFromUrl('https://example.com/offers.xml', [
    'auto_publish' => true,
]);

// Validate XML
$validation = $sdk->validateXml('https://example.com/offers.xml');

// Get import stats
$stats = $sdk->getImportStats();
```

## Error Handling

```php
use LoyaltyLt\SDK\Exceptions\LoyaltySDKException;

try {
    $result = $sdk->getLoyaltyCardInfo(['card_number' => 'INVALID']);
} catch (LoyaltySDKException $e) {
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->getErrorCode();
    echo "HTTP Status: " . $e->getHttpStatus();
}
```

## Laravel Integration

### Service Provider

```php
// config/services.php
'loyalty' => [
    'api_key' => env('LOYALTY_API_KEY'),
    'api_secret' => env('LOYALTY_API_SECRET'),
    'environment' => env('LOYALTY_ENVIRONMENT', 'production'),
],

// app/Providers/AppServiceProvider.php
use LoyaltyLt\SDK\LoyaltySDK;

public function register()
{
    $this->app->singleton(LoyaltySDK::class, function ($app) {
        return new LoyaltySDK([
            'apiKey' => config('services.loyalty.api_key'),
            'apiSecret' => config('services.loyalty.api_secret'),
            'environment' => config('services.loyalty.environment'),
        ]);
    });
}

// Usage in controller
public function __construct(private LoyaltySDK $loyalty) {}

public function awardPoints(Request $request)
{
    return $this->loyalty->createTransaction([
        'card_id' => $request->card_id,
        'amount' => $request->amount,
        'points' => $request->points,
    ]);
}
```

## API Documentation

Full API documentation: [docs.loyalty.lt](https://docs.loyalty.lt)

## Support

- Email: developers@loyalty.lt
- Documentation: https://docs.loyalty.lt
- Issues: https://github.com/Loyalty-lt/sdk-php/issues

## License

MIT License - see [LICENSE](LICENSE) for details.
