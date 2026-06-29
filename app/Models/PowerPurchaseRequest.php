<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PowerPurchaseRequest extends Model
{
    protected $fillable = [
        'user_id',
        'power_amount',
        'soles_amount',
        'payment_method',
        'receipt_path',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'power_amount' => 'integer',
            'soles_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
