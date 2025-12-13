<?php

namespace LoyaltyLt\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LoyaltyLt\SDK\Exceptions\LoyaltySDKException;

class LoyaltySDK
{
    private const VERSION = '1.0.4';
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_RETRIES = 3;

    private Client $httpClient;
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private string $locale;
    private int $timeout;
    private int $retries;
    private bool $debug;

    public function __construct(array $config)
    {
        if (empty($config['apiKey'])) {
            throw new LoyaltySDKException('API Key is required', 'INVALID_CONFIG');
        }
        if (empty($config['apiSecret'])) {
            throw new LoyaltySDKException('API Secret is required', 'INVALID_CONFIG');
        }

        $this->apiKey = $config['apiKey'];
        $this->apiSecret = $config['apiSecret'];
        $this->locale = $config['locale'] ?? 'lt';
        $this->timeout = $config['timeout'] ?? self::DEFAULT_TIMEOUT;
        $this->retries = $config['retries'] ?? self::DEFAULT_RETRIES;
        $this->debug = $config['debug'] ?? false;

        $environment = $config['environment'] ?? 'production';
        $this->baseUrl = $environment === 'staging'
            ? 'https://staging-api.loyalty.lt'
            : 'https://api.loyalty.lt';

        if (!empty($config['baseUrl'])) {
            $this->baseUrl = rtrim($config['baseUrl'], '/');
        }

        $this->httpClient = new Client([
            'timeout' => $this->timeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'LoyaltyLt-PHP-SDK/' . self::VERSION,
                'X-API-Key' => $this->apiKey,
                'X-API-Secret' => $this->apiSecret,
            ]
        ]);
    }

    // ===================
    // QR LOGIN (Shop API)
    // ===================

    /**
     * Generate QR Login session for shop
     * POST /shop/auth/qr-login/generate
     */
    public function generateQrLogin(?string $deviceName = null, ?int $shopId = null): array
    {
        return $this->request('POST', '/shop/auth/qr-login/generate', [
            'device_name' => $deviceName ?? php_uname('n'),
            'shop_id' => $shopId,
        ]);
    }

    /**
     * Poll QR Login status
     * POST /shop/auth/qr-login/poll/{session_id}
     */
    public function pollQrLogin(string $sessionId): array
    {
        return $this->request('POST', "/shop/auth/qr-login/poll/{$sessionId}");
    }

    /**
     * Send app download link via SMS
     * POST /shop/auth/send-app-link
     */
    public function sendAppLink(string $phone, int $shopId, ?string $customerName = null, string $language = 'lt'): array
    {
        return $this->request('POST', '/shop/auth/send-app-link', [
            'phone' => $phone,
            'shop_id' => $shopId,
            'customer_name' => $customerName,
            'language' => $language,
        ]);
    }

    // ===================
    // QR CARD SCAN (Shop API)
    // ===================

    /**
     * Generate QR Card Scan session for POS
     * POST /shop/qr-card/generate
     */
    public function generateQrCardSession(?string $deviceName = null, ?int $shopId = null): array
    {
        return $this->request('POST', '/shop/qr-card/generate', [
            'device_name' => $deviceName ?? 'POS Terminal',
            'shop_id' => $shopId,
        ]);
    }

    /**
     * Poll QR Card Scan status
     * GET /shop/qr-card/status/{sessionId}
     */
    public function pollQrCardStatus(string $sessionId): array
    {
        return $this->request('GET', "/shop/qr-card/status/{$sessionId}");
    }

    // ===================
    // ABLY REAL-TIME (Shop API)
    // ===================

    /**
     * Get Ably token for real-time updates
     * POST /shop/ably/token
     */
    public function getAblyToken(string $sessionId): array
    {
        return $this->request('POST', '/shop/ably/token', [
            'session_id' => $sessionId,
        ]);
    }

    // ===================
    // SHOPS (Shop API)
    // ===================

    /**
     * Get partner shops
     * GET /shop/shops
     */
    public function getShops(array $filters = []): array
    {
        return $this->request('GET', '/shop/shops', $filters);
    }

    // ===================
    // LOYALTY CARDS (Shop API)
    // ===================

    /**
     * Get loyalty cards
     * GET /shop/loyalty-cards
     */
    public function getLoyaltyCards(array $filters = []): array
    {
        return $this->request('GET', '/shop/loyalty-cards', $filters);
    }

    /**
     * Get single loyalty card
     * GET /shop/loyalty-cards/{id}
     */
    public function getLoyaltyCard(int $id): array
    {
        return $this->request('GET', "/shop/loyalty-cards/{$id}");
    }

    /**
     * Get loyalty card info by various identifiers
     * GET /shop/loyalty-cards/info
     */
    public function getLoyaltyCardInfo(array $params): array
    {
        return $this->request('GET', '/shop/loyalty-cards/info', $params);
    }

    /**
     * Get points balance for card
     * GET /shop/loyalty-cards/balance
     */
    public function getPointsBalance(array $params): array
    {
        return $this->request('GET', '/shop/loyalty-cards/balance', $params);
    }

    // ===================
    // TRANSACTIONS (Shop API)
    // ===================

    /**
     * Create transaction (award points)
     * POST /shop/transactions/create
     */
    public function createTransaction(array $data): array
    {
        return $this->request('POST', '/shop/transactions/create', $data);
    }

    /**
     * Get partner transactions
     * GET /shop/transactions
     */
    public function getTransactions(array $filters = []): array
    {
        return $this->request('GET', '/shop/transactions', $filters);
    }

    // ===================
    // OFFERS (Shop API)
    // ===================

    /**
     * Get offers
     * GET /shop/offers
     */
    public function getOffers(array $filters = []): array
    {
        return $this->request('GET', '/shop/offers', $filters);
    }

    /**
     * Get single offer
     * GET /shop/offers/{id}
     */
    public function getOffer(int $id): array
    {
        return $this->request('GET', "/shop/offers/{$id}");
    }

    /**
     * Create offer
     * POST /shop/offers
     */
    public function createOffer(array $data): array
    {
        return $this->request('POST', '/shop/offers', $data);
    }

    /**
     * Update offer
     * PUT /shop/offers/{id}
     */
    public function updateOffer(int $id, array $data): array
    {
        return $this->request('PUT', "/shop/offers/{$id}", $data);
    }

    /**
     * Delete offer
     * DELETE /shop/offers/{id}
     */
    public function deleteOffer(int $id): void
    {
        $this->request('DELETE', "/shop/offers/{$id}");
    }

    /**
     * Get offer categories
     * GET /shop/categories
     */
    public function getCategories(): array
    {
        return $this->request('GET', '/shop/categories');
    }

    // ===================
    // XML IMPORT (Shop API)
    // ===================

    /**
     * Import offers from XML URL
     * POST /shop/xml-import/from-url
     */
    public function importFromUrl(string $url, array $options = []): array
    {
        return $this->request('POST', '/shop/xml-import/from-url', array_merge([
            'url' => $url,
        ], $options));
    }

    /**
     * Validate XML
     * POST /shop/xml-import/validate
     */
    public function validateXml(string $url): array
    {
        return $this->request('POST', '/shop/xml-import/validate', [
            'url' => $url,
        ]);
    }

    /**
     * Get import statistics
     * GET /shop/xml-import/stats
     */
    public function getImportStats(): array
    {
        return $this->request('GET', '/shop/xml-import/stats');
    }

    // ===================
    // GAMES (Shop API)
    // ===================

    /**
     * List games for a shop
     * GET /shop/games
     */
    public function listGames(int $shopId, array $filters = []): array
    {
        return $this->request('GET', '/shop/games', array_merge(['shop_id' => $shopId], $filters));
    }

    /**
     * Get single game
     * GET /shop/games/{id}
     */
    public function getGame(int $gameId, int $cardId): array
    {
        return $this->request('GET', "/shop/games/{$gameId}", ['card_id' => $cardId]);
    }

    /**
     * Get games for a loyalty card
     * GET /shop/cards/{cardId}/games
     */
    public function getCardGames(int $cardId, ?int $shopId = null): array
    {
        $params = $shopId ? ['shop_id' => $shopId] : [];
        return $this->request('GET', "/shop/cards/{$cardId}/games", $params);
    }

    /**
     * Get game progress
     * GET /shop/games/{id}/progress
     */
    public function getGameProgress(int $gameId, int $cardId): array
    {
        return $this->request('GET', "/shop/games/{$gameId}/progress", ['card_id' => $cardId]);
    }

    /**
     * Add stamps to a stamp card game
     * POST /shop/games/{id}/add-stamps
     */
    public function addStamps(int $gameId, int $cardId, int $shopId, int $stampsCount = 1): array
    {
        return $this->request('POST', "/shop/games/{$gameId}/add-stamps", [
            'card_id' => $cardId,
            'shop_id' => $shopId,
            'stamps_count' => $stampsCount,
        ]);
    }

    /**
     * Complete a stamp card game
     * POST /shop/games/{id}/complete
     */
    public function completeGame(int $gameId, int $cardId): array
    {
        return $this->request('POST', "/shop/games/{$gameId}/complete", ['card_id' => $cardId]);
    }

    /**
     * Restart a game
     * POST /shop/games/{id}/restart
     */
    public function restartGame(int $gameId, int $cardId, int $shopId): array
    {
        return $this->request('POST', "/shop/games/{$gameId}/restart", [
            'card_id' => $cardId,
            'shop_id' => $shopId,
        ]);
    }

    /**
     * Get customer game history
     * GET /shop/cards/{cardId}/games/history
     */
    public function getGameHistory(int $cardId, ?int $shopId = null): array
    {
        $params = $shopId ? ['shop_id' => $shopId] : [];
        return $this->request('GET', "/shop/cards/{$cardId}/games/history", $params);
    }

    // ===================
    // COUPONS (Shop API)
    // ===================

    /**
     * List coupons
     * GET /shop/coupons
     */
    public function listCoupons(array $filters = []): array
    {
        return $this->request('GET', '/shop/coupons', $filters);
    }

    /**
     * Verify a coupon
     * POST /shop/coupons/verify
     */
    public function verifyCoupon(string $couponCode, int $shopId): array
    {
        return $this->request('POST', '/shop/coupons/verify', [
            'code' => $couponCode,
            'shop_id' => $shopId,
        ]);
    }

    /**
     * Redeem a coupon
     * POST /shop/coupons/redeem
     */
    public function redeemCoupon(int $couponId, int $shopId, ?int $selectedProductId = null, ?string $notes = null): array
    {
        return $this->request('POST', '/shop/coupons/redeem', [
            'coupon_id' => $couponId,
            'shop_id' => $shopId,
            'selected_product_id' => $selectedProductId,
            'notes' => $notes,
        ]);
    }

    /**
     * Set coupon to pending status
     * POST /shop/coupons/pending
     */
    public function setCouponPending(int $couponId, int $shopId, ?int $selectedProductId = null): array
    {
        return $this->request('POST', '/shop/coupons/pending', [
            'coupon_id' => $couponId,
            'shop_id' => $shopId,
            'selected_product_id' => $selectedProductId,
        ]);
    }

    /**
     * Cancel pending coupon
     * POST /shop/coupons/cancel-pending
     */
    public function cancelPendingCoupon(int $couponId, int $shopId): array
    {
        return $this->request('POST', '/shop/coupons/cancel-pending', [
            'coupon_id' => $couponId,
            'shop_id' => $shopId,
        ]);
    }

    /**
     * Get coupons for a card
     * GET /shop/coupons/card
     */
    public function getCardCoupons(int $shopId, ?int $cardId = null, ?int $userId = null): array
    {
        return $this->request('GET', '/shop/coupons/card', [
            'shop_id' => $shopId,
            'card_id' => $cardId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get coupon products
     * GET /shop/coupons/products
     */
    public function getCouponProducts(int $couponId): array
    {
        return $this->request('GET', '/shop/coupons/products', ['coupon_id' => $couponId]);
    }

    /**
     * Calculate coupon discount
     * POST /shop/coupons/calculate-discount
     */
    public function calculateCouponDiscount(string $couponCode, float $purchaseAmount, ?array $cartItems = null): array
    {
        return $this->request('POST', '/shop/coupons/calculate-discount', [
            'coupon_code' => $couponCode,
            'purchase_amount' => $purchaseAmount,
            'cart_items' => $cartItems,
        ]);
    }

    // ===================
    // SYSTEM (Shop API)
    // ===================

    /**
     * Validate API credentials
     * POST /shop/validate-credentials
     */
    public function validateCredentials(): array
    {
        return $this->request('POST', '/shop/validate-credentials');
    }

    /**
     * Health check
     * GET /shop/system/health
     */
    public function healthCheck(): array
    {
        return $this->request('GET', '/shop/system/health');
    }

    // ===================
    // UTILITY METHODS
    // ===================

    public function getVersion(): string
    {
        return self::VERSION;
    }

    public function getApiUrl(): string
    {
        return "{$this->baseUrl}/{$this->locale}/shop";
    }

    // ===================
    // HTTP CLIENT
    // ===================

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->getApiUrl() . $endpoint;
        $options = [];

        if (!empty($data)) {
            if ($method === 'GET') {
                $url .= '?' . http_build_query(array_filter($data, fn($v) => $v !== null));
            } else {
                $options['json'] = array_filter($data, fn($v) => $v !== null);
            }
        }

        if ($this->debug) {
            error_log("[LoyaltySDK] {$method} {$url}");
            if (!empty($options['json'])) {
                error_log("[LoyaltySDK] Data: " . json_encode($options['json']));
            }
        }

        $attempts = 0;

        do {
            try {
                $response = $this->httpClient->request($method, $url, $options);
                $body = json_decode($response->getBody()->getContents(), true);

                if ($this->debug) {
                    error_log("[LoyaltySDK] Response: " . substr(json_encode($body), 0, 500));
                }

                if (isset($body['success']) && !$body['success']) {
                    throw new LoyaltySDKException(
                        $body['message'] ?? 'API request failed',
                        $body['code'] ?? 'API_ERROR',
                        $response->getStatusCode()
                    );
                }

                // Return paginated response or data
                if (isset($body['data']) && isset($body['meta'])) {
                    return [
                        'data' => $body['data'],
                        'meta' => $body['meta'],
                    ];
                }

                return $body['data'] ?? $body;

            } catch (GuzzleException $e) {
                $attempts++;

                if ($attempts >= $this->retries) {
                    throw new LoyaltySDKException(
                        "Network error after {$this->retries} attempts: " . $e->getMessage(),
                        'NETWORK_ERROR',
                        $e->getCode()
                    );
                }

                // Exponential backoff
                usleep((int) pow(2, $attempts) * 100000);
            }
        } while ($attempts < $this->retries);

        throw new LoyaltySDKException('Request failed', 'REQUEST_FAILED');
    }
}
