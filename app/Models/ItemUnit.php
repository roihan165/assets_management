<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    protected $fillable = [
        'item_id',
        'code',
        'status',
        'condition_status',
        'condition_notes'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function loanDetails()
    {
        return $this->hasMany(LoanDetail::class);
    }
}
