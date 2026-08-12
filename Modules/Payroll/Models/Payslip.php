<?php

namespace Modules\Payroll\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'user_id', 'payroll_run_id', 'month', 'year', 'month_num', 'basic', 'hra', 'da', 'allowances',
        'overtime_pay', 'night_differential', 'gross', 'tds', 'investment_rebate', 'pf_employee', 'pf_employer',
        'loan_deduction', 'attendance_deduction', 'unpaid_leave_deduction',
        'other_deductions', 'net', 'status',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'hra' => 'decimal:2',
            'da' => 'decimal:2',
            'allowances' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'night_differential' => 'decimal:2',
            'gross' => 'decimal:2',
            'tds' => 'decimal:2',
            'investment_rebate' => 'decimal:2',
            'pf_employee' => 'decimal:2',
            'pf_employer' => 'decimal:2',
            'loan_deduction' => 'decimal:2',
            'attendance_deduction' => 'decimal:2',
            'unpaid_leave_deduction' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'net' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
