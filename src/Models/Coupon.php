<?php

namespace LoyaltyLt\SDK\Models;

class Coupon
{
    public int $id;
    public int $user_id;
    public int $offer_id;
    public string $code;
    public string $status;
    public ?string $redeemed_at;
    public ?string $expires_at;
    public ?string $redemption_reference;
    public array $meta_data;
    public ?string $created_at;
    public ?string $updated_at;
    public ?Offer $offer;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->offer_id = $data['offer_id'];
        $this->code = $data['code'];
        $this->status = $data['status'];
        $this->redeemed_at = $data['redeemed_at'] ?? null;
        $this->expires_at = $data['expires_at'] ?? null;
        $this->redemption_reference = $data['redemption_reference'] ?? null;
        $this->meta_data = $data['meta_data'] ?? [];
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->offer = isset($data['offer']) ? new Offer($data['offer']) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'offer_id' => $this->offer_id,
            'code' => $this->code,
            'status' => $this->status,
            'redeemed_at' => $this->redeemed_at,
            'expires_at' => $this->expires_at,
            'redemption_reference' => $this->redemption_reference,
            'meta_data' => $this->meta_data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'offer' => $this->offer?->toArray(),
        ];
    }

    public function isRedeemed(): bool
    {
        return $this->status === 'redeemed';
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return strtotime($this->expires_at) < time();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function isRedeemable(): bool
    {
        return $this->isActive() && !$this->isRedeemed();
    }
} 
 
 