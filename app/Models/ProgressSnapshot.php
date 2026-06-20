<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'learning_track_id',
        'practice_session_id',
        'level_estimated',
        'accuracy_pct',
        'total_questions',
        'correct_answers',
        'streak_days',
        'snapshot_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'accuracy_pct' => 'float',
            'snapshot_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningTrack(): BelongsTo
    {
        return $this->belongsTo(LearningTrack::class);
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class);
    }
}
