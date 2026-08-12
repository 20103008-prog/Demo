<?php

namespace Modules\HR\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'code', 'user_id', 'reviewer_id', 'year', 'period', 'score',
        'recommended_increment_pct', 'comments', 'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'recommended_increment_pct' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
