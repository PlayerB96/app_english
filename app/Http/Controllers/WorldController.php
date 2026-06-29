<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TokenService;
use App\Services\WorldAccessService;
use App\Services\WorldCatalogService;
use App\Services\WorldLevelProgressService;
use App\Services\WorldProgressService;
use App\Services\WorldQuestionCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class WorldController extends Controller
{
    public function __construct(
        private readonly WorldAccessService $access,
        private readonly WorldCatalogService $catalog,
        private readonly WorldProgressService $progress,
        private readonly WorldLevelProgressService $levelProgress,
        private readonly WorldQuestionCatalogService $questions,
        private readonly TokenService $tokens,
    ) {}

    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $baseProgress = $this->progress->snapshot($user);
        $quizProgress = $this->levelProgress->quizSnapshot($user);

        $questionsByLevel = [];

        foreach ($this->catalog->levels() as $level) {
            $levelId = (int) $level['id'];
            $questionsByLevel[$levelId] = $this->questions->questionsForLevel($levelId);
        }

        return Inertia::render('World/Index', [
            'worlds' => $this->catalog->worlds(),
            'levels' => $this->catalog->levels(),
            'world_access' => [
                'unlocked' => $this->access->hasAccess($user),
                'unlock_cost' => $this->access->unlockCost(),
                'unlocked_at' => $user->world_unlocked_at?->toIso8601String(),
            ],
            'progress' => [
                ...$baseProgress,
                ...$quizProgress,
            ],
            'questions_by_level' => $questionsByLevel,
        ]);
    }

    public function unlock(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user === null || ! $user->isLearner()) {
            abort(403);
        }

        try {
            $this->access->unlock($user, $this->tokens);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', '¡Mundo desbloqueado! Explora Linux Kingdom en el mapa.');
    }

    public function complete(Request $request, int $level): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user === null || ! $user->isLearner()) {
            abort(403);
        }

        try {
            $this->progress->completeLevel($user, $level);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['level_id' => $exception->getMessage()]);
        }

        return back()->with('status', 'Desafío completado. ¡Siguiente nivel desbloqueado!');
    }
}
