<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Inertia\Inertia;
use Inertia\Response;

class MockPageController extends Controller
{
    public function learnerDashboard(): Response
    {
        return Inertia::render('Dashboard', [
            'progress' => MockData::learnerDashboard(),
        ]);
    }

    public function practice(): Response
    {
        return Inertia::render('Practice/Index', [
            'tiers' => MockData::tiers(),
            'challenges' => MockData::speakingChallenges(),
        ]);
    }

    public function tracks(): Response
    {
        return Inertia::render('Tracks/Index', [
            'tiers' => MockData::tiers(),
            'challenges' => MockData::quizChallenges(),
        ]);
    }

    public function adminDashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'dashboard' => MockData::adminDashboard(),
        ]);
    }

    public function adminUsers(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'learners' => MockData::adminLearners(),
        ]);
    }

    public function adminTracks(): Response
    {
        return Inertia::render('Admin/Tracks/Index', [
            'tracks' => MockData::adminTracks(),
        ]);
    }

    public function adminReports(): Response
    {
        return Inertia::render('Admin/Reports/Index', [
            'reports' => MockData::adminTrackReports(),
        ]);
    }
}
