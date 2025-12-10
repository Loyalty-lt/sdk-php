<?php

namespace LoyaltyLt\SDK\Models;

class PointsTransaction
{
    public int $id;
    public int $loyalty_card_id;
    public int $points;
    public string $type;
    public string $description;
    public ?string $reference_id;
    public ?string $reference_type;
    public ?string $expires_at;
    public bool $is_expired;
    public array $meta_data;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->loyalty_card_id = $data['loyalty_card_id'];
        $this->points = $data['points'];
        $this->type = $data['type'];
        $this->description = $data['description'];
        $this->reference_id = $data['reference_id'] ?? null;
        $this->reference_type = $data['reference_type'] ?? null;
        $this->expires_at = $data['expires_at'] ?? null;
        $this->is_expired = $data['is_expired'] ?? false;
        $this->meta_data = $data['meta_data'] ?? [];
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'loyalty_card_id' => $this->loyalty_card_id,
            'points' => $this->points,
            'type' => $this->type,
            'description' => $this->description,
            'reference_id' => $this->reference_id,
            'reference_type' => $this->reference_type,
            'expires_at' => $this->expires_at,
            'is_expired' => $this->is_expired,
            'meta_data' => $this->meta_data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function isEarning(): bool
    {
        return $this->points > 0;
    }

    public function isRedemption(): bool
    {
        return $this->points < 0;
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return strtotime($this->expires_at) < time();
    }
} 
 
 