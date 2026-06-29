<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorldLevelProgress extends Model
{
    protected $table = 'user_world_level_progress';

    protected $fillable = [
        'user_id',
        'level_id',
        'completed_at',
        'locked_until',
        'correct_question_ids',
        'session_question_ids',
    ];

    protected function casts(): array
    {
        return [
            'level_id' => 'integer',
            'completed_at' => 'datetime',
            'locked_until' => 'datetime',
            'correct_question_ids' => 'array',
            'session_question_ids' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
