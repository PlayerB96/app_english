<?php

namespace App\DTOs\Auth;

use App\Enums\UserRole;
use App\Models\MobileUser;

readonly class MobileUserValidationRowDto
{
    public function __construct(
        public int $userExists,
        public string $userCode,
        public string $userName,
        public string $companyCode,
        public string $branchCode,
        public float $exchangeRate,
        public string $roleCode,
        public string $roleName,
        public string $branchName,
        public string $branchSigla,
    ) {}

    public static function fromSpRow(object $row): self
    {
        return new self(
            userExists: (int) ($row->l_exis_usua ?? 0),
            userCode: trim((string) ($row->c_usua_codi ?? '')),
            userName: trim((string) ($row->c_usua_nomb ?? '')),
            companyCode: trim((string) ($row->c_codi_empr ?? '')),
            branchCode: trim((string) ($row->c_codi_sucu ?? '')),
            exchangeRate: (float) ($row->n_tcam_vent ?? 0),
            roleCode: trim((string) ($row->c_role_codi ?? '')),
            roleName: trim((string) ($row->c_role_nomb ?? '')),
            branchName: trim((string) ($row->c_nomb_sucu ?? '')),
            branchSigla: trim((string) ($row->c_sigl_sucu ?? '')),
        );
    }

    public function userExists(): bool
    {
        return $this->userExists === 1;
    }

    public function toMobileUser(UserRole $role): MobileUser
    {
        return new MobileUser(
            code: $this->userCode,
            name: $this->userName,
            companyCode: $this->companyCode,
            branchCode: $this->branchCode,
            exchangeRate: $this->exchangeRate,
            roleCode: $this->roleCode,
            roleName: $this->roleName,
            branchName: $this->branchName,
            branchSigla: $this->branchSigla,
            role: $role,
        );
    }
}
