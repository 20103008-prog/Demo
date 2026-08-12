<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\PayrollSetting;
use App\Models\User;

class LoanService
{
    public function monthlyEmi(User $user): float
    {
        return (float) Loan::where('user_id', $user->id)
            ->where('status', 'Active')
            ->sum('emi');
    }

    public function outstanding(User $user): float
    {
        return (float) Loan::where('user_id', $user->id)
            ->where('status', 'Active')
            ->sum('outstanding');
    }

    /**
     * Apply salary protection: EMI cannot exceed protection_pct of (gross - pf - tds).
     * Returns [deduction, deferred].
     */
    public function protectedDeduction(User $user, float $gross, float $pf, float $tds): array
    {
        $emi = $this->monthlyEmi($user);
        if ($emi <= 0) {
            return [0.0, 0.0];
        }

        $protectionPct = PayrollSetting::getFloat('loan_salary_protection_pct', 50);
        $netBeforeLoan = max(0, $gross - $pf - $tds);
        $maxDeduction = round($netBeforeLoan * ($protectionPct / 100), 2);

        if ($emi <= $maxDeduction) {
            return [$emi, 0.0];
        }

        return [$maxDeduction, round($emi - $maxDeduction, 2)];
    }

    public function applyDeduction(User $user, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $remaining = $amount;
        $loans = Loan::where('user_id', $user->id)
            ->where('status', 'Active')
            ->orderBy('start_date')
            ->get();

        foreach ($loans as $loan) {
            if ($remaining <= 0) {
                break;
            }
            $pay = min((float) $loan->outstanding, $remaining);
            $loan->outstanding = round((float) $loan->outstanding - $pay, 2);
            if ($loan->outstanding <= 0) {
                $loan->outstanding = 0;
                $loan->status = 'Closed';
            }
            $loan->save();
            $remaining = round($remaining - $pay, 2);
        }
    }
}
