<?php

namespace LoyaltyLt\SDK\Models;

class Game
{
    public int $id;
    public string $name;
    public string $description;
    public ?string $image;
    public string $type;
    public array $config;
    public array $rewards;
    public ?int $points_cost;
    public ?int $daily_limit;
    public ?int $total_limit;
    public bool $is_active;
    public bool $is_featured;
    public array $categories;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->image = $data['image'] ?? null;
        $this->type = $data['type'];
        $this->config = $data['config'] ?? [];
        $this->rewards = $data['rewards'] ?? [];
        $this->points_cost = $data['points_cost'] ?? null;
        $this->daily_limit = $data['daily_limit'] ?? null;
        $this->total_limit = $data['total_limit'] ?? null;
        $this->is_active = $data['is_active'] ?? true;
        $this->is_featured = $data['is_featured'] ?? false;
        $this->categories = $data['categories'] ?? [];
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'config' => $this->config,
            'rewards' => $this->rewards,
            'points_cost' => $this->points_cost,
            'daily_limit' => $this->daily_limit,
            'total_limit' => $this->total_limit,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'categories' => $this->categories,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function isPlayable(): bool
    {
        return $this->is_active;
    }

    public function hasCost(): bool
    {
        return $this->points_cost !== null && $this->points_cost > 0;
    }
} 
 
 