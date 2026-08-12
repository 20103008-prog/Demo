<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\PayrollFaq;
use App\Models\PayrollSetting;
use App\Models\TaxSlab;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayrollRulesSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'salary_basic_pct', 'value' => '60', 'group' => 'salary', 'label' => 'Basic % of CTC'],
            ['key' => 'salary_hra_pct', 'value' => '20', 'group' => 'salary', 'label' => 'HRA / House Rent %'],
            ['key' => 'salary_da_pct', 'value' => '10', 'group' => 'salary', 'label' => 'DA / Medical %'],
            ['key' => 'salary_allowance_pct', 'value' => '10', 'group' => 'salary', 'label' => 'Other Allowances %'],
            ['key' => 'pf_employee_pct', 'value' => '10', 'group' => 'pf', 'label' => 'Employee PF % (BD typical 7–10%)'],
            ['key' => 'pf_employer_pct', 'value' => '10', 'group' => 'pf', 'label' => 'Employer PF %'],
            ['key' => 'tax_regime', 'value' => 'nbr', 'group' => 'tax', 'label' => 'Tax Regime'],
            ['key' => 'tax_employment_deduction_pct', 'value' => '33.3333', 'group' => 'tax', 'label' => 'NBR Employment Deduction %'],
            ['key' => 'tax_employment_deduction_max', 'value' => '500000', 'group' => 'tax', 'label' => 'NBR Employment Deduction Max'],
            ['key' => 'tax_free_general', 'value' => '375000', 'group' => 'tax', 'label' => 'NBR Tax-Free (General)'],
            ['key' => 'tax_free_woman', 'value' => '425000', 'group' => 'tax', 'label' => 'NBR Tax-Free (Woman/Senior)'],
            ['key' => 'tax_free_disabled', 'value' => '500000', 'group' => 'tax', 'label' => 'NBR Tax-Free (Disabled)'],
            ['key' => 'tax_free_freedom_fighter', 'value' => '525000', 'group' => 'tax', 'label' => 'NBR Tax-Free (Freedom Fighter)'],
            ['key' => 'tax_minimum_tax', 'value' => '5000', 'group' => 'tax', 'label' => 'NBR Minimum Tax'],
            ['key' => 'tax_cess_pct', 'value' => '0', 'group' => 'tax', 'label' => 'Cess % (not used for NBR)'],
            ['key' => 'tax_standard_deduction', 'value' => '0', 'group' => 'tax', 'label' => 'Legacy standard deduction'],
            ['key' => 'currency', 'value' => 'BDT', 'group' => 'general', 'label' => 'Currency'],
            ['key' => 'late_threshold', 'value' => '09:15', 'group' => 'attendance', 'label' => 'Default Late Threshold'],
            ['key' => 'lates_per_absence', 'value' => '3', 'group' => 'attendance', 'label' => 'Lates Equal One Absence'],
            ['key' => 'weekend_days', 'value' => '5', 'group' => 'attendance', 'label' => 'Weekend Day Numbers (0=Sun)'],
            ['key' => 'working_days_per_month', 'value' => '26', 'group' => 'payroll', 'label' => 'Working Days / Month'],
            ['key' => 'ot_hourly_multiplier', 'value' => '1.5', 'group' => 'payroll', 'label' => 'OT Hourly Multiplier'],
            ['key' => 'default_increment_pct', 'value' => '10', 'group' => 'payroll', 'label' => 'Default Increment %'],
            ['key' => 'festival_bonus_full_pct', 'value' => '50', 'group' => 'bonus', 'label' => 'Full Festival Bonus % of Basic'],
            ['key' => 'festival_bonus_prorata_pct', 'value' => '25', 'group' => 'bonus', 'label' => 'Pro-rata Festival Bonus %'],
            ['key' => 'festival_bonus_full_years', 'value' => '1', 'group' => 'bonus', 'label' => 'Years for Full Bonus'],
            ['key' => 'loan_salary_protection_pct', 'value' => '50', 'group' => 'loan', 'label' => 'Max Loan Deduction % of Net'],
            ['key' => 'leave_casual_per_year', 'value' => '12', 'group' => 'leave', 'label' => 'Casual Leaves / Year'],
            ['key' => 'leave_sick_per_year', 'value' => '6', 'group' => 'leave', 'label' => 'Sick Leaves / Year'],
            ['key' => 'leave_earned_per_year', 'value' => '15', 'group' => 'leave', 'label' => 'Earned Leaves / Year'],
            ['key' => 'leave_encashment_divisor', 'value' => '26', 'group' => 'settlement', 'label' => 'Leave Encashment Divisor'],
            ['key' => 'gratuity_min_years', 'value' => '5', 'group' => 'settlement', 'label' => 'Min Years for Gratuity'],
            ['key' => 'gratuity_days_per_year', 'value' => '15', 'group' => 'settlement', 'label' => 'Gratuity Days per Year'],
            ['key' => 'ai_confidence_threshold', 'value' => '0.35', 'group' => 'ai', 'label' => 'AI Match Threshold'],
            ['key' => 'ai_high_confidence', 'value' => '0.55', 'group' => 'ai', 'label' => 'AI High Confidence'],
        ];

        foreach ($settings as $row) {
            PayrollSetting::updateOrCreate(['key' => $row['key']], $row);
        }

        // NBR progressive bands (widths). First band width is overridden by tax-free category limit.
        TaxSlab::query()->delete();
        $slabs = [
            // min/max used only to derive "next" widths for bands after the tax-free band
            [0, 375000, 0, 1],          // tax-free (general reference)
            [375000, 675000, 10, 2],    // next 300,000 @ 10%
            [675000, 1075000, 15, 3],   // next 400,000 @ 15%
            [1075000, 1575000, 20, 4],  // next 500,000 @ 20%
            [1575000, 3575000, 25, 5],  // next 2,000,000 @ 25%
            [3575000, null, 30, 6],     // remaining @ 30%
        ];
        foreach ($slabs as [$min, $max, $rate, $sort]) {
            TaxSlab::create([
                'regime' => 'nbr',
                'min_income' => $min,
                'max_income' => $max,
                'rate_pct' => $rate,
                'sort_order' => $sort,
                'is_active' => true,
            ]);
        }

        $holidays = [
            ['2025-02-21', 'International Mother Language Day'],
            ['2025-03-17', 'Bangabandhu Birthday / Children Day'],
            ['2025-03-26', 'Independence Day'],
            ['2025-05-01', 'May Day'],
            ['2025-08-15', 'National Mourning Day'],
            ['2025-12-16', 'Victory Day'],
            ['2026-02-21', 'International Mother Language Day'],
            ['2026-03-17', 'Bangabandhu Birthday / Children Day'],
            ['2026-03-26', 'Independence Day'],
            ['2026-05-01', 'May Day'],
            ['2026-08-15', 'National Mourning Day'],
            ['2026-12-16', 'Victory Day'],
        ];
        foreach ($holidays as [$date, $name]) {
            Holiday::updateOrCreate(['date' => $date], ['name' => $name, 'is_active' => true]);
        }

        $faqs = [
            ['Tax / NBR', 'NBR income tax & TDS', 'tax,tds,nbr,income tax,slab,etin', 'Monthly TDS follows NBR progressive slabs (FY 2025-26). Tax-free limit depends on category. Employment income deduction is min(1/3 of salary, ৳5,00,000). Minimum tax ৳5,000 applies when income exceeds the tax-free limit.'],
            ['Leave', 'Leave balance entitlement', 'leave,balance,casual,sick', 'You are entitled to 12 casual, 6 sick and 15 earned leaves per calendar year. Unused casual leaves may be encashed on settlement.'],
            ['Provident Fund', 'PF contribution', 'pf,provident,epf', 'PF contribution rates are configurable in payroll settings (default 10% of basic for employee and employer).'],
            ['Payslip', 'Monthly payslip', 'payslip,salary,net pay', 'Payslips are generated after monthly payroll processing and include earnings, OT, NBR TDS, PF and loan deductions.'],
            ['Loan', 'Loan EMI and protection', 'loan,emi,advance,deduction', 'Loan EMIs are deducted automatically. Maximum deduction is capped at 50% of net salary (salary protection rule). Excess EMI is deferred.'],
            ['Bonus', 'Festival bonus', 'bonus,festival,eid', 'Festival bonus is 50% of basic after 1 year of service, otherwise pro-rata 25% of basic.'],
            ['Overtime', 'Overtime payment', 'overtime,ot,extra hours', 'Approved overtime is paid at 1.5× hourly rate based on basic salary during payroll processing.'],
            ['Settlement', 'Final settlement', 'settlement,gratuity,exit,resignation', 'Final settlement includes last month salary, leave encashment, gratuity (if eligible), minus PF, NBR TDS and outstanding loans.'],
            ['Attendance', 'Late and absence rule', 'attendance,late,absent,punch', 'Three late arrivals in a month count as one absence and affect attendance-based salary deductions.'],
            ['Increment', 'Salary increment', 'increment,raise,appraisal', 'Annual increments (minimum 10%) can be applied after completing 1 year of service and update the employee basic salary.'],
        ];
        PayrollFaq::query()->delete();
        foreach ($faqs as [$cat, $title, $kw, $resp]) {
            PayrollFaq::create([
                'category' => $cat,
                'title' => $title,
                'keywords' => $kw,
                'response' => $resp,
                'is_active' => true,
            ]);
        }

        $year = (int) date('Y');
        $casual = (int) PayrollSetting::getValue('leave_casual_per_year', 12);
        $sick = (int) PayrollSetting::getValue('leave_sick_per_year', 6);
        $earned = (int) PayrollSetting::getValue('leave_earned_per_year', 15);

        User::where('role', '!=', 'admin')->where('status', 'Active')->each(function (User $user) use ($year, $casual, $sick, $earned) {
            LeaveBalance::updateOrCreate(
                ['user_id' => $user->id, 'year' => $year],
                ['casual' => $casual, 'sick' => $sick, 'earned' => $earned]
            );
        });
    }
}
