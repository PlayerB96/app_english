<?php

namespace App\Http\Middleware;

use App\Models\MobileUser;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user instanceof MobileUser ? [
                    'code' => $user->code,
                    'name' => $user->name,
                    'companyCode' => $user->companyCode,
                    'branchCode' => $user->branchCode,
                    'exchangeRate' => $user->exchangeRate,
                    'roleCode' => $user->roleCode,
                    'roleName' => $user->roleName,
                    'branchName' => $user->branchName,
                    'branchSigla' => $user->branchSigla,
                    'role' => $user->role->value,
                ] : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
