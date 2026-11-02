<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetPurchaseRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'asset_name',
        'specifications',
        'quantity',
        'estimated_unit_cost',
        'justification',
        'needed_by',
        'status',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'needed_by' => 'date',
            'reviewed_at' => 'datetime',
            'quantity' => 'integer',
            'estimated_unit_cost' => 'integer',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
