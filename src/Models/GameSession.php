<?php

namespace LoyaltyLt\SDK\Models;

class GameSession
{
    public int $id;
    public int $user_id;
    public int $game_id;
    public string $session_key;
    public string $status;
    public ?int $score;
    public ?int $level;
    public array $progress_data;
    public ?array $reward_claimed;
    public ?string $started_at;
    public ?string $completed_at;
    public ?string $expires_at;
    public ?string $created_at;
    public ?string $updated_at;
    public ?Game $game;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->game_id = $data['game_id'];
        $this->session_key = $data['session_key'];
        $this->status = $data['status'];
        $this->score = $data['score'] ?? null;
        $this->level = $data['level'] ?? null;
        $this->progress_data = $data['progress_data'] ?? [];
        $this->reward_claimed = $data['reward_claimed'] ?? null;
        $this->started_at = $data['started_at'] ?? null;
        $this->completed_at = $data['completed_at'] ?? null;
        $this->expires_at = $data['expires_at'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->game = isset($data['game']) ? new Game($data['game']) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'game_id' => $this->game_id,
            'session_key' => $this->session_key,
            'status' => $this->status,
            'score' => $this->score,
            'level' => $this->level,
            'progress_data' => $this->progress_data,
            'reward_claimed' => $this->reward_claimed,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'game' => $this->game?->toArray(),
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return strtotime($this->expires_at) < time();
    }

    public function canContinue(): bool
    {
        return $this->isActive() && !$this->isExpired();
    }

    public function hasReward(): bool
    {
        return $this->reward_claimed !== null;
    }
} 
 
 