<?php

namespace App\Http\Controllers;

use App\Models\LearningTrack;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminService $admin,
    ) {}

    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'dashboard' => $this->admin->dashboard(),
        ]);
    }

    public function users(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'learners' => $this->admin->learners(),
        ]);
    }

    public function tracks(): Response
    {
        return Inertia::render('Admin/Tracks/Index', [
            'tracks' => $this->admin->tracks(),
        ]);
    }

    public function updateTrack(Request $request, LearningTrack $track): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'difficulty' => ['sometimes', 'string', 'in:beginner,intermediate,advanced'],
        ]);

        $this->admin->updateTrack($track, $validated);

        return back();
    }

    public function reports(): Response
    {
        return Inertia::render('Admin/Reports/Index', [
            'reports' => $this->admin->trackReports(),
        ]);
    }
}
