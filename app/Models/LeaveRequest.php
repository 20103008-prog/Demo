<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'code', 'user_id', 'type', 'from_date', 'to_date', 'days',
        'is_half_day', 'half_day_session',
        'reason', 'status', 'applied_on', 'reviewed_by', 'review_comment',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'applied_on' => 'date',
            'is_half_day' => 'boolean',
            'days' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
