<?php

namespace LoyaltyLt\SDK\Models;

class Offer
{
    public int $id;
    public string $title;
    public string $description;
    public ?string $image;
    public string $type;
    public ?float $discount_percentage;
    public ?float $discount_amount;
    public ?int $points_required;
    public ?int $points_earned;
    public ?string $promo_code;
    public ?string $terms_conditions;
    public ?string $starts_at;
    public ?string $ends_at;
    public int $usage_limit;
    public int $usage_count;
    public int $user_usage_limit;
    public bool $is_active;
    public bool $is_featured;
    public array $categories;
    public array $meta_data;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->image = $data['image'] ?? null;
        $this->type = $data['type'];
        $this->discount_percentage = $data['discount_percentage'] ?? null;
        $this->discount_amount = $data['discount_amount'] ?? null;
        $this->points_required = $data['points_required'] ?? null;
        $this->points_earned = $data['points_earned'] ?? null;
        $this->promo_code = $data['promo_code'] ?? null;
        $this->terms_conditions = $data['terms_conditions'] ?? null;
        $this->starts_at = $data['starts_at'] ?? null;
        $this->ends_at = $data['ends_at'] ?? null;
        $this->usage_limit = $data['usage_limit'] ?? 0;
        $this->usage_count = $data['usage_count'] ?? 0;
        $this->user_usage_limit = $data['user_usage_limit'] ?? 1;
        $this->is_active = $data['is_active'] ?? true;
        $this->is_featured = $data['is_featured'] ?? false;
        $this->categories = $data['categories'] ?? [];
        $this->meta_data = $data['meta_data'] ?? [];
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'points_required' => $this->points_required,
            'points_earned' => $this->points_earned,
            'promo_code' => $this->promo_code,
            'terms_conditions' => $this->terms_conditions,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'usage_limit' => $this->usage_limit,
            'usage_count' => $this->usage_count,
            'user_usage_limit' => $this->user_usage_limit,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'categories' => $this->categories,
            'meta_data' => $this->meta_data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = time();
        
        if ($this->starts_at && strtotime($this->starts_at) > $now) {
            return false;
        }
        
        if ($this->ends_at && strtotime($this->ends_at) < $now) {
            return false;
        }

        return true;
    }

    public function isClaimable(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->usage_limit > 0 && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
} 
 
 