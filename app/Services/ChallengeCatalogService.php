<?php

namespace App\Services;

use App\Enums\LevelProgressMode;
use App\Models\LearningTrack;
use App\Models\Question;

class ChallengeCatalogService
{
    /** @var list<string> */
    private const TIER_ORDER = ['basico', 'intermedio', 'avanzado'];

    /**
     * @return list<array{slug: string, name: string, description: string}>
     */
    public function tiers(): array
    {
        return config('learning.tiers');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function speakingLevels(): array
    {
        return $this->levelsForTrack(
            config('learning.tracks.speaking.slug'),
            'speaking',
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function quizLevels(): array
    {
        return $this->levelsForTrack(
            config('learning.tracks.quiz.slug'),
            'quiz',
        );
    }

    public function questionBelongsToLevel(
        LevelProgressMode $mode,
        int $levelId,
        int $questionId,
    ): bool {
        return in_array($questionId, $this->questionIdsForLevel($mode, $levelId), true);
    }

    /**
     * @return list<int>
     */
    public function questionIdsForLevel(LevelProgressMode $mode, int $levelId): array
    {
        $slug = $mode === LevelProgressMode::Speaking
            ? config('learning.tracks.speaking.slug')
            : config('learning.tracks.quiz.slug');

        $track = LearningTrack::query()->where('slug', $slug)->firstOrFail();

        return Question::query()
            ->where('learning_track_id', $track->id)
            ->where('source', 'seed')
            ->get()
            ->filter(fn (Question $question) => $this->levelId(
                $question->difficulty,
                (int) ($question->metadata['phase'] ?? 1),
            ) === $levelId)
            ->sortBy(fn (Question $question) => (int) ($question->metadata['question_index'] ?? 1))
            ->pluck('id')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function levelsForTrack(string $slug, string $type): array
    {
        $track = LearningTrack::query()
            ->where('slug', $slug)
            ->firstOrFail();

        /** @var array<int, array<string, mixed>> $grouped */
        $grouped = [];

        Question::query()
            ->where('learning_track_id', $track->id)
            ->where('source', 'seed')
            ->get()
            ->sortBy(fn (Question $question) => (
                $this->levelId($question->difficulty, (int) ($question->metadata['phase'] ?? 1)) * 10
            ) + (int) ($question->metadata['question_index'] ?? 1))
            ->each(function (Question $question) use (&$grouped, $type): void {
                $metadata = $question->metadata ?? [];
                $tier = $question->difficulty;
                $phase = (int) ($metadata['phase'] ?? 1);
                $levelId = $this->levelId($tier, $phase);

                if (! isset($grouped[$levelId])) {
                    $grouped[$levelId] = [
                        'id' => $levelId,
                        'tier' => $tier,
                        'phase' => $phase,
                        'sublevel_intensity' => $phase,
                        'questions' => [],
                    ];
                }

                $grouped[$levelId]['questions'][] = $this->serializeQuestion($question, $type);
            });

        ksort($grouped);

        return array_values($grouped);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeQuestion(Question $question, string $type): array
    {
        $metadata = $question->metadata ?? [];
        $tier = $question->difficulty;
        $phase = (int) ($metadata['phase'] ?? 1);
        $questionIndex = (int) ($metadata['question_index'] ?? 1);

        $stepDifficulty = match (true) {
            $questionIndex <= 1 => 'facil',
            $questionIndex === 2 => 'medio',
            default => 'dificil',
        };

        $base = [
            'question_id' => $question->id,
            'level_id' => $this->levelId($tier, $phase),
            'question_index' => $questionIndex,
            'step_difficulty' => $stepDifficulty,
            'sublevel_intensity' => $phase,
            'prompt' => $question->prompt,
        ];

        if ($type === 'speaking') {
            return array_merge($base, [
                'expected_translation' => $metadata['expected_translation'] ?? '',
                'hint' => $question->context,
            ]);
        }

        return array_merge($base, [
            'options' => $metadata['options'] ?? [],
            'correct_index' => (int) ($metadata['correct_index'] ?? 0),
        ]);
    }

    private function levelId(string $tier, int $phase): int
    {
        $tierIndex = array_search($tier, self::TIER_ORDER, true);

        if ($tierIndex === false) {
            return $phase;
        }

        return ($tierIndex * 5) + $phase;
    }
}
