<?php

namespace App\Http\Controllers;

use App\Enums\LevelProgressMode;
use App\Services\ChallengeCatalogService;
use App\Services\LevelProgressService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MockPageController extends Controller
{
    public function __construct(
        private readonly ChallengeCatalogService $challengeCatalog,
        private readonly LevelProgressService $levelProgress,
    ) {}

    public function practice(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Inertia::render('Practice/Index', [
            'tiers' => $this->challengeCatalog->tiers(),
            'levels' => $this->challengeCatalog->speakingLevels(),
            'progress' => $this->levelProgress->snapshot($user, LevelProgressMode::Speaking),
        ]);
    }

    public function tracks(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Inertia::render('Tracks/Index', [
            'tiers' => $this->challengeCatalog->tiers(),
            'levels' => $this->challengeCatalog->quizLevels(),
            'progress' => $this->levelProgress->snapshot($user, LevelProgressMode::Quiz),
        ]);
    }
}
