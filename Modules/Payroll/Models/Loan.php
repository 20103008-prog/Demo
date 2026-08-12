<?php

namespace Modules\Payroll\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = [
        'code', 'user_id', 'type', 'amount', 'installments', 'emi', 'outstanding', 'status', 'start_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'installments' => 'integer',
            'emi' => 'decimal:2',
            'outstanding' => 'decimal:2',
            'start_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
