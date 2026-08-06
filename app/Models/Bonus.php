<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bonus extends Model
{
    protected $fillable = [
        'code', 'user_id', 'basic', 'years_of_service',
        'festival_bonus', 'performance_bonus', 'status',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'years_of_service' => 'decimal:2',
            'festival_bonus' => 'decimal:2',
            'performance_bonus' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
