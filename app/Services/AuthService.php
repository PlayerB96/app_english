<?php

namespace App\Services;

use App\Auth\MobileUserProvider;
use App\DTOs\Auth\MobileUserValidationRowDto;
use App\Enums\MobileRoleCode;
use App\Enums\UserRole;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private AuthRepositoryInterface $authRepository) {}

    public function login(string $username, string $password, bool $remember = false): void
    {
        $rows = $this->authRepository->validateCredentials($username, $password);

        if ($rows === [] || ! $rows[0]->userExists()) {
            throw ValidationException::withMessages([
                'username' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $allowedRow = $this->resolveLoginRow($rows);

        if ($allowedRow === null) {
            throw ValidationException::withMessages([
                'username' => ['No tienes permiso para acceder a la caja rápida.'],
            ]);
        }

        $user = $allowedRow->toMobileUser($this->mapRoleCodeToUserRole($allowedRow));

        session([MobileUserProvider::SESSION_KEY => $user->toSessionArray()]);

        Auth::login($user, $remember);
    }

    public function logout(): void
    {
        session()->forget(MobileUserProvider::SESSION_KEY);

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Prioriza caja rápida (POS); si no existe, acepta administrador legacy.
     *
     * @param  list<MobileUserValidationRowDto>  $rows
     */
    private function resolveLoginRow(array $rows): ?MobileUserValidationRowDto
    {
        foreach ([MobileRoleCode::CajaRapida, MobileRoleCode::Administrador] as $role) {
            $row = $this->findRowWithRole($rows, $role->value);

            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    private function mapRoleCodeToUserRole(MobileUserValidationRowDto $row): UserRole
    {
        return match ($row->roleCode) {
            MobileRoleCode::Administrador->value => UserRole::Administrator,
            MobileRoleCode::CajaRapida->value => UserRole::Cashier,
            default => UserRole::Cashier,
        };
    }

    /**
     * @param  list<MobileUserValidationRowDto>  $rows
     */
    private function findRowWithRole(array $rows, string $roleCode): ?MobileUserValidationRowDto
    {
        foreach ($rows as $row) {
            if ($row->roleCode === $roleCode) {
                return $row;
            }
        }

        return null;
    }
}
