<?php

namespace App\Services;

use App\Auth\MobileUserProvider;
use App\Enums\MobileRoleCode;
use App\Enums\UserRole;
use App\Models\MobileUser;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private AuthRepositoryInterface $authRepository) {}

    public function login(string $username, string $password, bool $remember = false): void
    {
        $rows = $this->authRepository->validateCredentials($username, $password);

        if ($rows === [] || (int) ($rows[0]->l_exis_usua ?? 0) !== 1) {
            throw ValidationException::withMessages([
                'username' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $allowedRow = $this->findRowWithRole($rows, MobileRoleCode::CajaRapida->value);

        if ($allowedRow === null) {
            throw ValidationException::withMessages([
                'username' => ['No tienes permiso para acceder a la caja rápida.'],
            ]);
        }

        $user = MobileUser::fromSpRow($allowedRow, UserRole::Cashier);

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
     * @param  list<object>  $rows
     */
    private function findRowWithRole(array $rows, string $roleCode): ?object
    {
        foreach ($rows as $row) {
            if (trim((string) ($row->c_role_codi ?? '')) === $roleCode) {
                return $row;
            }
        }

        return null;
    }
}
