<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'showDevAccounts' => app()->isLocal() && config('app.debug'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->auth->login(
            $validated['email'],
            $validated['password'],
            (bool) ($validated['remember'] ?? false),
        );

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $home = $user->role === UserRole::Administrator
            ? route('admin.dashboard')
            : route('dashboard');

        return redirect()->intended($home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->auth->logout();

        return redirect()->route('login');
    }
}
