<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerComplaint extends Model
{
    protected $fillable = [
        'complaint_number',
        'user_id',
        'consumer_name',
        'document_type',
        'document_number',
        'address',
        'email',
        'phone',
        'item_type',
        'amount',
        'complaint_type',
        'description',
        'order_reference',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
