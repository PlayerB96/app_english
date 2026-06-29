<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldQuestion extends Model
{
    protected $fillable = [
        'world_level_id',
        'question_index',
        'type',
        'difficulty',
        'prompt',
        'context',
        'options',
        'correct_index',
    ];

    protected function casts(): array
    {
        return [
            'world_level_id' => 'integer',
            'question_index' => 'integer',
            'options' => 'array',
            'correct_index' => 'integer',
        ];
    }
}
