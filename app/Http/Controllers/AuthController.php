<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Auth\Events\Registered;
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

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $home = $user->role === UserRole::Administrator
            ? route('admin.dashboard')
            : route('dashboard');

        return redirect()->intended($home);
    }

    public function registerCreate(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function registerStore(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $this->auth->register(
            $validated['name'],
            $validated['email'],
            $validated['password'],
        );

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->auth->logout();

        return redirect()->route('login');
    }
}
