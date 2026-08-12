<?php

namespace Modules\Payroll\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'year', 'month', 'status', 'employee_count', 'total_net',
        'prepared_by', 'approved_by', 'prepared_at', 'approved_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_net' => 'decimal:2',
            'prepared_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function label(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('M Y');
    }
}
