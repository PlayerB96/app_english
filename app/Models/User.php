<?php

namespace App\Models;

/**
 * @property-read UserRole $role
 * @property int $tokens
 */
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'tokens'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use \Illuminate\Auth\MustVerifyEmail;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'tokens' => 'integer',
        ];
    }

    public function hasRole(UserRole $role): bool
    {
        $current = $this->getAttribute('role');

        return $current instanceof UserRole && $current === $role;
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(UserRole::Administrator);
    }

    public function isLearner(): bool
    {
        return $this->hasRole(UserRole::Learner);
    }
}
