<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTierReset extends Model
{
    protected $fillable = [
        'user_id',
        'mode',
        'tier',
        'reset_count',
    ];

    protected function casts(): array
    {
        return [
            'reset_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
