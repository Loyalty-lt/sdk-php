<?php

namespace LoyaltyLt\SDK;

use LoyaltyLt\SDK\Exceptions\LoyaltySDKException;
use WebSocket\Client;
use WebSocket\Message\Text;

/**
 * One short-lived subscription to one public channel, over the Pusher wire protocol.
 *
 * QR results arrive on a channel, not in a response body. Polling for them costs a
 * request per second per session while a customer stands at the till, so the SDK
 * speaks the socket rather than leaving you to write the loop.
 *
 * The QR channels are public — the unguessable session id in the channel name is the
 * secret — so there is no token to manage and nothing to authorize.
 *
 * Built by LoyaltySDK::subscribeQrLogin() / subscribeQrCard(); you would not normally
 * construct it yourself.
 */
class RealtimeSubscriber
{
    private const PROTOCOL = 7;
    private const CLIENT = 'loyaltylt-php';

    private array $config;
    private string $channel;

    public function __construct(array $config, string $channel)
    {
        if (! class_exists(Client::class)) {
            throw new LoyaltySDKException(
                'Realtime needs the phrity/websocket package: composer require phrity/websocket',
                'MISSING_DEPENDENCY'
            );
        }

        $this->config = $config;
        $this->channel = $channel;
    }

    private function url(): string
    {
        $scheme = ($this->config['scheme'] ?? 'https') === 'https' ? 'wss' : 'ws';
        $host = $this->config['host'];
        $port = $this->config['port'] ?? 443;
        $key = $this->config['key'];

        return sprintf(
            '%s://%s:%d/app/%s?protocol=%d&client=%s&version=2.0.0',
            $scheme,
            $host,
            $port,
            $key,
            self::PROTOCOL,
            self::CLIENT
        );
    }

    /**
     * Block until $callback returns a truthy value, or until $timeout seconds pass.
     *
     * $callback receives (string $event, array $payload) for every frame on the
     * channel. Return anything truthy to stop listening; that value comes back here.
     * Returns null on timeout — QR sessions expire after 5 minutes, so the default
     * matches.
     */
    public function listen(callable $callback, int $timeout = 300): ?array
    {
        $deadline = time() + $timeout;

        $client = new Client($this->url());
        $client->setTimeout(min(30, max(1, $timeout)));

        try {
            // 1. The broker greets us and names the socket.
            $this->read($client);

            // 2. Public channel: no auth signature, just the name.
            $client->text(json_encode([
                'event' => 'pusher:subscribe',
                'data' => ['channel' => $this->channel],
            ]));

            while (time() < $deadline) {
                $frame = $this->read($client);
                if ($frame === null) {
                    continue;
                }

                $event = $frame['event'] ?? '';
                if ($event === '' || str_starts_with($event, 'pusher:') || str_starts_with($event, 'pusher_internal:')) {
                    continue;
                }

                // Reverb sends `data` as a JSON string; peer events keep the client- prefix.
                $payload = $frame['data'] ?? [];
                if (is_string($payload)) {
                    $decoded = json_decode($payload, true);
                    $payload = is_array($decoded) ? $decoded : ['raw' => $payload];
                }

                $name = str_starts_with($event, 'client-') ? substr($event, 7) : $event;

                $result = $callback($name, $payload);
                if ($result) {
                    return is_array($result) ? $result : $payload;
                }
            }

            return null;
        } finally {
            try {
                $client->close();
            } catch (\Throwable) {
                // the socket is going away either way
            }
        }
    }

    /** One frame, or null when the read simply timed out. */
    private function read(Client $client): ?array
    {
        try {
            $message = $client->receive();
        } catch (\Throwable $e) {
            if (str_contains(strtolower($e->getMessage()), 'timeout')) {
                return null;
            }
            throw new LoyaltySDKException('Realtime connection lost: '.$e->getMessage(), 'REALTIME_ERROR');
        }

        if (! $message instanceof Text) {
            return null;
        }

        $decoded = json_decode($message->getContent(), true);

        return is_array($decoded) ? $decoded : null;
    }
}
