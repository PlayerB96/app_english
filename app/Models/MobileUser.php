<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;

class MobileUser implements Authenticatable
{
    use AuthenticatableTrait;

    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $companyCode,
        public readonly string $branchCode,
        public readonly float $exchangeRate,
        public readonly string $roleCode,
        public readonly string $roleName,
        public readonly string $branchName,
        public readonly string $branchSigla,
        public readonly UserRole $role = UserRole::Cashier,
    ) {}

    /**
     * @param  object{
     *     c_usua_codi?: mixed,
     *     c_usua_nomb?: mixed,
     *     c_codi_empr?: mixed,
     *     c_codi_sucu?: mixed,
     *     n_tcam_vent?: mixed,
     *     c_role_codi?: mixed,
     *     c_role_nomb?: mixed,
     *     c_nomb_sucu?: mixed,
     *     c_sigl_sucu?: mixed,
     * }  $row
     */
    public static function fromSpRow(object $row, UserRole $role = UserRole::Cashier): self
    {
        return new self(
            code: trim((string) ($row->c_usua_codi ?? '')),
            name: trim((string) ($row->c_usua_nomb ?? '')),
            companyCode: trim((string) ($row->c_codi_empr ?? '')),
            branchCode: trim((string) ($row->c_codi_sucu ?? '')),
            exchangeRate: (float) ($row->n_tcam_vent ?? 0),
            roleCode: trim((string) ($row->c_role_codi ?? '')),
            roleName: trim((string) ($row->c_role_nomb ?? '')),
            branchName: trim((string) ($row->c_nomb_sucu ?? '')),
            branchSigla: trim((string) ($row->c_sigl_sucu ?? '')),
            role: $role,
        );
    }

    /**
     * @return array<string, string|float>
     */
    public function toSessionArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'companyCode' => $this->companyCode,
            'branchCode' => $this->branchCode,
            'exchangeRate' => $this->exchangeRate,
            'roleCode' => $this->roleCode,
            'roleName' => $this->roleName,
            'branchName' => $this->branchName,
            'branchSigla' => $this->branchSigla,
            'role' => $this->role->value,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromSession(array $payload): self
    {
        return new self(
            code: (string) ($payload['code'] ?? ''),
            name: (string) ($payload['name'] ?? ''),
            companyCode: (string) ($payload['companyCode'] ?? ''),
            branchCode: (string) ($payload['branchCode'] ?? ''),
            exchangeRate: (float) ($payload['exchangeRate'] ?? 0),
            roleCode: (string) ($payload['roleCode'] ?? ''),
            roleName: (string) ($payload['roleName'] ?? ''),
            branchName: (string) ($payload['branchName'] ?? ''),
            branchSigla: (string) ($payload['branchSigla'] ?? ''),
            role: UserRole::from((string) ($payload['role'] ?? UserRole::Cashier->value)),
        );
    }

    public function getAuthIdentifierName(): string
    {
        return 'code';
    }

    public function getAuthIdentifier(): string
    {
        return $this->code;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        //
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
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
