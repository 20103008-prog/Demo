<?php

namespace Modules\Payroll\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Increment extends Model
{
    protected $fillable = [
        'code', 'user_id', 'current_salary', 'increment_pct',
        'new_salary', 'effective_date', 'reason', 'status',
    ];

    protected function casts(): array
    {
        return [
            'current_salary' => 'decimal:2',
            'increment_pct' => 'decimal:2',
            'new_salary' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
