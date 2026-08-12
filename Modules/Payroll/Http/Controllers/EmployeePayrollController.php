<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\Increment;
use App\Models\Loan;
use App\Models\Payslip;
use App\Models\Settlement;
use App\Models\User;
use App\Services\PfService;
use App\Services\TaxService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeePayrollController extends Controller
{
    public function payroll(TaxService $tax, PfService $pf): View
    {
        $user = Auth::user();
        $payslips = Payslip::where('user_id', $user->id)->orderByDesc('year')->orderByDesc('month_num')->get();
        $latest = $payslips->first();
        $bonuses = Bonus::where('user_id', $user->id)->latest()->get();
        $increments = Increment::where('user_id', $user->id)->latest()->get();
        $loans = Loan::where('user_id', $user->id)->latest()->get();
        $settlement = Settlement::where('user_id', $user->id)->latest()->first();
        $pfRates = $pf->rates();
        $annualEst = $tax->annualIncome((float) $user->salary);
        $nbr = $tax->breakdownForUser($user);
        $monthlyTdsEst = $nbr['monthly_tds'];
        $categories = \App\Services\TaxService::categoryOptions();

        return view('employee.payroll', compact(
            'user', 'payslips', 'latest', 'bonuses', 'increments', 'loans',
            'settlement', 'pfRates', 'annualEst', 'monthlyTdsEst', 'nbr', 'categories'
        ));
    }
}
