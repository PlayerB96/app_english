<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorldProgress extends Model
{
    protected $table = 'user_world_progress';

    protected $fillable = [
        'user_id',
        'level_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'level_id' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
