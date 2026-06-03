<?php

namespace App\Auth;

use App\Models\MobileUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class MobileUserProvider implements UserProvider
{
    public const SESSION_KEY = 'auth.mobile_user';

    public function retrieveById($identifier): ?Authenticatable
    {
        $payload = session(self::SESSION_KEY);

        if (! is_array($payload) || ($payload['code'] ?? '') !== (string) $identifier) {
            return null;
        }

        return MobileUser::fromSession($payload);
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): void
    {
        //
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool
    {
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void
    {
        //
    }
}
