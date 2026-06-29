<?php

namespace App\Services;

use App\Models\WorldQuestion;

class WorldQuestionCatalogService
{
    public function questionsPerLevel(): int
    {
        return 3;
    }

    /**
     * @return list<int>
     */
    public function questionIdsForLevel(int $levelId): array
    {
        return WorldQuestion::query()
            ->where('world_level_id', $levelId)
            ->orderBy('question_index')
            ->pluck('id')
            ->values()
            ->all();
    }

    public function questionBelongsToLevel(int $levelId, int $questionId): bool
    {
        return in_array($questionId, $this->questionIdsForLevel($levelId), true);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function questionById(int $questionId): ?array
    {
        $question = WorldQuestion::query()->find($questionId);

        if ($question === null) {
            return null;
        }

        return $this->serializeQuestion($question);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function questionsForLevel(int $levelId): array
    {
        return WorldQuestion::query()
            ->where('world_level_id', $levelId)
            ->orderBy('question_index')
            ->get()
            ->map(fn (WorldQuestion $question) => $this->serializeQuestion($question))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeQuestion(WorldQuestion $question): array
    {
        return [
            'question_id' => $question->id,
            'world_level_id' => $question->world_level_id,
            'question_index' => $question->question_index,
            'type' => $question->type,
            'difficulty' => $question->difficulty,
            'prompt' => $question->prompt,
            'context' => $question->context,
            'options' => $question->options,
            'correct_index' => $question->correct_index,
        ];
    }
}
