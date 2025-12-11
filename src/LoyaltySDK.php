<?php

namespace LoyaltyLt\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LoyaltyLt\SDK\Exceptions\LoyaltySDKException;

class LoyaltySDK
{
    private const VERSION = '2.0.0';
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
    // QR LOGIN (Partner API)
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
    // QR CARD SCAN (Partner API)
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
    // ABLY REAL-TIME (Partner API)
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
    // SHOPS (Partner API)
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
    // LOYALTY CARDS (Partner API)
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
    // TRANSACTIONS (Partner API)
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
     * Award points (alias for createTransaction)
     * POST /shop/transactions/award-points
     */
    public function awardPoints(array $data): array
    {
        return $this->request('POST', '/shop/transactions/award-points', $data);
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
    // OFFERS (Partner API)
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
    // XML IMPORT (Partner API)
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
    // SYSTEM (Partner API)
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
