<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->auth->login(
            $validated['username'],
            $validated['password'],
            (bool) ($validated['remember'] ?? false),
        );

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->auth->logout();

        return redirect()->route('login');
    }

    public function dashboard(): Response
    {
        return Inertia::render('Dashboard');
    }
}
