<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrQuery extends Model
{
    protected $fillable = [
        'code', 'user_id', 'category', 'subject', 'description',
        'status', 'priority', 'ai_draft', 'ai_category', 'ai_confidence',
        'needs_manual_review', 'response', 'submitted_on',
    ];

    protected function casts(): array
    {
        return [
            'submitted_on' => 'date',
            'ai_confidence' => 'decimal:4',
            'needs_manual_review' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
