<?php

namespace LoyaltyLt\SDK\Models;

class LoyaltyCard
{
    public int $id;
    public int $user_id;
    public string $card_number;
    public string $card_type;
    public string $brand_name;
    public ?string $brand_logo;
    public ?string $background_color;
    public ?string $text_color;
    public ?string $design_template;
    public array $custom_fields;
    public int $points_balance;
    public ?string $expires_at;
    public bool $is_active;
    public bool $is_third_party;
    public array $third_party_data;
    public ?string $qr_code;
    public ?string $barcode;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->card_number = $data['card_number'];
        $this->card_type = $data['card_type'];
        $this->brand_name = $data['brand_name'];
        $this->brand_logo = $data['brand_logo'] ?? null;
        $this->background_color = $data['background_color'] ?? null;
        $this->text_color = $data['text_color'] ?? null;
        $this->design_template = $data['design_template'] ?? null;
        $this->custom_fields = $data['custom_fields'] ?? [];
        $this->points_balance = $data['points_balance'] ?? 0;
        $this->expires_at = $data['expires_at'] ?? null;
        $this->is_active = $data['is_active'] ?? true;
        $this->is_third_party = $data['is_third_party'] ?? false;
        $this->third_party_data = $data['third_party_data'] ?? [];
        $this->qr_code = $data['qr_code'] ?? null;
        $this->barcode = $data['barcode'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'card_number' => $this->card_number,
            'card_type' => $this->card_type,
            'brand_name' => $this->brand_name,
            'brand_logo' => $this->brand_logo,
            'background_color' => $this->background_color,
            'text_color' => $this->text_color,
            'design_template' => $this->design_template,
            'custom_fields' => $this->custom_fields,
            'points_balance' => $this->points_balance,
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active,
            'is_third_party' => $this->is_third_party,
            'third_party_data' => $this->third_party_data,
            'qr_code' => $this->qr_code,
            'barcode' => $this->barcode,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return strtotime($this->expires_at) < time();
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
} 
 
 