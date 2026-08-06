<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrQuery extends Model
{
    protected $fillable = [
        'code', 'user_id', 'category', 'subject', 'description',
        'status', 'priority', 'ai_draft', 'response', 'submitted_on',
    ];

    protected function casts(): array
    {
        return ['submitted_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
