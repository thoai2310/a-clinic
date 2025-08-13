<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function register(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }

    public function login(array $credentials): array
    {
        if (!$token = auth('api')->attempt($credentials)) {
            throw new \Exception('Invalid credentials', 401);
        }
        $user = auth('api')->user();
        $user->role = 'admin';

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }

    public function logout(): void
    {
        try {
            auth('api')->logout();
        } catch (\Exception $exception) {
        }
    }

    public function refresh(): array
    {
        $token = auth('api')->refresh();
        return [
            'user' => auth('api')->user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }

    public function me()
    {
        $user = auth('api')->user();
        $user->role = 'admin';
        return $user;
    }
}
