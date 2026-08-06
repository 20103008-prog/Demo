<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    protected $fillable = [
        'user_id', 'exit_date', 'last_basic', 'years_of_service',
        'leave_encashment', 'last_increment_pct', 'pf_employee', 'tds',
        'final_month_salary', 'outstanding_loan', 'net_settlement', 'status',
    ];

    protected function casts(): array
    {
        return [
            'exit_date' => 'date',
            'last_basic' => 'decimal:2',
            'years_of_service' => 'decimal:2',
            'leave_encashment' => 'decimal:2',
            'last_increment_pct' => 'decimal:2',
            'pf_employee' => 'decimal:2',
            'tds' => 'decimal:2',
            'final_month_salary' => 'decimal:2',
            'outstanding_loan' => 'decimal:2',
            'net_settlement' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
