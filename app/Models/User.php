<?php

namespace App\Models;

/**
 * Plantilla del patrón WS-002 y tests de ejemplo.
 *
 * @internal Solo para tests de patrón (RepositoryTest, RequestTest). No usar en auth ni rutas de producción.
 *
 * @property-read UserRole $role
 */
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
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

    public function isCashier(): bool
    {
        return $this->hasRole(UserRole::Cashier);
    }
}
