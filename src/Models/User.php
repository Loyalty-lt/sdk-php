<?php

namespace LoyaltyLt\SDK\Models;

class User
{
    public int $id;
    public string $phone;
    public ?string $email;
    public ?string $name;
    public ?string $avatar;
    public ?string $birth_date;
    public ?string $gender;
    public ?string $city;
    public ?string $postal_code;
    public ?string $address;
    public bool $is_verified;
    public bool $is_active;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->phone = $data['phone'];
        $this->email = $data['email'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->avatar = $data['avatar'] ?? null;
        $this->birth_date = $data['birth_date'] ?? null;
        $this->gender = $data['gender'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->postal_code = $data['postal_code'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->is_verified = $data['is_verified'] ?? false;
        $this->is_active = $data['is_active'] ?? true;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'is_verified' => $this->is_verified,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
 
 