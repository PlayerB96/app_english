<?php

namespace App\Repositories;

use App\DTOs\Auth\MobileUserValidationRowDto;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @return list<MobileUserValidationRowDto>
     */
    public function validateCredentials(string $username, string $password): array
    {
        $rows = DB::connection('sqlsrv')->select(
            'EXEC dbo.usp_movil_valida_usu_pwd_2 ?, ?',
            [$username, $password],
        );

        return array_map(
            static fn (object $row): MobileUserValidationRowDto => MobileUserValidationRowDto::fromSpRow($row),
            $rows,
        );
    }
}
