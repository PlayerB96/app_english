<?php

namespace App\Http\Controllers;

use App\Services\ProgressService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ProgressService $progress,
    ) {}

    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Inertia::render('Dashboard', [
            'progress' => $this->progress->learnerDashboard($user),
        ]);
    }
}
