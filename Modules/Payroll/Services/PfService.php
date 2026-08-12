<?php

namespace Modules\Payroll\Services;

use App\Models\PayrollSetting;

class PfService
{
    public function employeeContribution(float $basic): float
    {
        $rate = PayrollSetting::getFloat('pf_employee_pct', 12);

        return round($basic * ($rate / 100), 2);
    }

    public function employerContribution(float $basic): float
    {
        $rate = PayrollSetting::getFloat('pf_employer_pct', 12);

        return round($basic * ($rate / 100), 2);
    }

    public function rates(): array
    {
        return [
            'employee' => PayrollSetting::getFloat('pf_employee_pct', 12),
            'employer' => PayrollSetting::getFloat('pf_employer_pct', 12),
        ];
    }
}
