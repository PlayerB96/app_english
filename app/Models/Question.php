<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'practice_session_id',
        'learning_track_id',
        'prompt',
        'context',
        'difficulty',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function learningTrack(): BelongsTo
    {
        return $this->belongsTo(LearningTrack::class);
    }
}
