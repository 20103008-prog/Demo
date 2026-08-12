<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id', 'year', 'casual', 'sick', 'earned',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
