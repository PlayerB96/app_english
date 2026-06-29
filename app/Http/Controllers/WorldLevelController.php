<?php

namespace App\Http\Controllers;

use App\Services\TokenService;
use App\Services\WorldLevelProgressService;
use App\Services\WorldProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorldLevelController extends Controller
{
    public function __construct(
        private readonly WorldLevelProgressService $levelProgress,
        private readonly WorldProgressService $worldProgress,
        private readonly TokenService $tokens,
    ) {}

    public function startSession(Request $request, int $level): RedirectResponse
    {
        try {
            $this->levelProgress->ensureSessionQuestions($request->user(), $level);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['level_id' => $exception->getMessage()]);
        }

        return back();
    }

    public function answer(Request $request, int $level): RedirectResponse
    {
        $validated = $request->validate([
            'question_id' => ['required', 'integer', 'min:1'],
            'response_text' => ['nullable', 'string', 'max:5000'],
            'input_mode' => ['nullable', 'string', 'max:16'],
        ]);

        try {
            $this->levelProgress->recordQuestionPass(
                $request->user(),
                $level,
                (int) $validated['question_id'],
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['question_id' => $exception->getMessage()]);
        }

        return back();
    }

    public function fail(Request $request, int $level): RedirectResponse
    {
        $validated = $request->validate([
            'question_id' => ['nullable', 'integer', 'min:1'],
            'response_text' => ['nullable', 'string', 'max:5000'],
            'input_mode' => ['nullable', 'string', 'max:16'],
            'hours' => ['sometimes', 'integer', 'min:1', 'max:168'],
        ]);

        try {
            $this->levelProgress->recordFail(
                $request->user(),
                $level,
                (int) ($validated['hours'] ?? config('world.lockout_hours', 4)),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['level_id' => $exception->getMessage()]);
        }

        return back();
    }

    public function skipLockout(Request $request, int $level): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || ! $user->isLearner()) {
            abort(403);
        }

        $cost = (int) config('tokens.world_skip_lockout_cost', 20);

        try {
            DB::transaction(function () use ($user, $level, $cost): void {
                $this->tokens->spend($user, $cost, 'world_skip_lockout');
                $this->levelProgress->skipLockout($user, $level);
            });
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['level_id' => $exception->getMessage()]);
        }

        return back()->with('status', 'Desafío desbloqueado. ¡Inténtalo de nuevo!');
    }
}
