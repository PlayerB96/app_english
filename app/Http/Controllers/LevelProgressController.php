<?php

namespace App\Http\Controllers;

use App\Enums\LevelProgressMode;
use App\Services\LevelProgressService;
use App\Services\ProgressService;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LevelProgressController extends Controller
{
    public function __construct(
        private readonly LevelProgressService $levelProgress,
        private readonly ProgressService $progress,
        private readonly TokenService $tokens,
    ) {}

    public function pass(Request $request, string $mode): RedirectResponse
    {
        return $this->questionPass($request, $mode);
    }

    public function questionPass(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);

        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'min:1', 'max:'.LevelProgressService::TOTAL_LEVELS],
            'question_id' => ['required', 'integer', 'min:1'],
            'response_text' => ['nullable', 'string', 'max:5000'],
            'input_mode' => ['nullable', 'string', 'max:16'],
        ]);

        $user = $request->user();

        try {
            $result = $this->levelProgress->recordQuestionPass(
                $user,
                $progressMode,
                (int) $validated['level_id'],
                (int) $validated['question_id'],
            );

            $this->progress->recordQuestionAttempt(
                $user,
                $progressMode,
                (int) $validated['question_id'],
                (int) $validated['level_id'],
                true,
                (string) ($validated['response_text'] ?? ''),
                (string) ($validated['input_mode'] ?? 'text'),
                $result['completed'],
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['question_id' => $exception->getMessage()]);
        }

        return back();
    }

    public function startSession(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);

        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'min:1', 'max:'.LevelProgressService::TOTAL_LEVELS],
        ]);

        try {
            $this->levelProgress->ensureSessionQuestions(
                $request->user(),
                $progressMode,
                (int) $validated['level_id'],
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['level_id' => $exception->getMessage()]);
        }

        return back();
    }

    public function fail(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);

        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'min:1', 'max:'.LevelProgressService::TOTAL_LEVELS],
            'question_id' => ['nullable', 'integer', 'min:1'],
            'response_text' => ['nullable', 'string', 'max:5000'],
            'input_mode' => ['nullable', 'string', 'max:16'],
            'hours' => ['sometimes', 'integer', 'min:1', 'max:168'],
        ]);

        $user = $request->user();

        try {
            $this->levelProgress->recordFail(
                $user,
                $progressMode,
                (int) $validated['level_id'],
                (int) ($validated['hours'] ?? 24),
            );

            if (isset($validated['question_id'])) {
                $this->progress->recordQuestionAttempt(
                    $user,
                    $progressMode,
                    (int) $validated['question_id'],
                    (int) $validated['level_id'],
                    false,
                    (string) ($validated['response_text'] ?? ''),
                    (string) ($validated['input_mode'] ?? 'text'),
                    true,
                );
            }
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['level_id' => $exception->getMessage()]);
        }

        return back();
    }

    public function reset(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);
        $this->levelProgress->reset($request->user(), $progressMode);

        return back();
    }

    public function resetLevel(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);

        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'min:1', 'max:'.LevelProgressService::TOTAL_LEVELS],
        ]);

        $this->levelProgress->resetLevel(
            $request->user(),
            $progressMode,
            (int) $validated['level_id'],
        );

        return back();
    }

    public function resetTier(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);

        $validated = $request->validate([
            'tier' => ['required', 'string', 'in:basico,intermedio,avanzado'],
        ]);

        try {
            $this->levelProgress->resetTier(
                $request->user(),
                $progressMode,
                $validated['tier'],
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['tier' => $exception->getMessage()]);
        }

        return back();
    }

    public function recordAttempt(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);

        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'min:1', 'max:'.LevelProgressService::TOTAL_LEVELS],
            'question_id' => ['required', 'integer', 'min:1'],
            'is_correct' => ['required', 'boolean'],
            'response_text' => ['nullable', 'string', 'max:5000'],
            'input_mode' => ['nullable', 'string', 'max:16'],
            'close_session' => ['sometimes', 'boolean'],
        ]);

        $this->progress->recordQuestionAttempt(
            $request->user(),
            $progressMode,
            (int) $validated['question_id'],
            (int) $validated['level_id'],
            (bool) $validated['is_correct'],
            (string) ($validated['response_text'] ?? ''),
            (string) ($validated['input_mode'] ?? 'text'),
            (bool) ($validated['close_session'] ?? false),
        );

        return back();
    }

    public function skipLockout(Request $request, string $mode): RedirectResponse
    {
        $progressMode = $this->resolveMode($mode);
        $user = $request->user();

        if ($user === null || ! $user->isLearner()) {
            abort(403);
        }

        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'min:1', 'max:'.LevelProgressService::TOTAL_LEVELS],
        ]);

        $levelId = (int) $validated['level_id'];
        $cost = (int) config('tokens.skip_lockout_cost', 10);

        try {
            DB::transaction(function () use ($user, $progressMode, $levelId, $cost): void {
                $this->tokens->spend($user, $cost, 'skip_lockout');
                $this->levelProgress->skipLockout($user, $progressMode, $levelId);
            });
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['level_id' => $exception->getMessage()]);
        }

        return back()->with('status', 'Subnivel desbloqueado.');
    }

    private function resolveMode(string $mode): LevelProgressMode
    {
        return LevelProgressMode::tryFrom($mode) ?? abort(404);
    }
}
