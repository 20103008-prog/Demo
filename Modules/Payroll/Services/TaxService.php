<?php

namespace Modules\Payroll\Services;

use App\Models\PayrollSetting;
use App\Models\TaxSlab;
use App\Models\User;

class TaxService
{
    public function annualIncome(float $monthlyGross): float
    {
        return round($monthlyGross * 12, 2);
    }

    /**
     * NBR employment income deduction: min(1/3 of employment income, max cap).
     */
    public function employmentDeduction(float $annualIncome): float
    {
        $pct = PayrollSetting::getFloat('tax_employment_deduction_pct', 33.3333);
        $max = PayrollSetting::getFloat('tax_employment_deduction_max', 500000);
        $byPct = $annualIncome * ($pct / 100);

        return round(min($byPct, $max), 2);
    }

    public function taxFreeLimit(string $category = 'general'): float
    {
        return match ($category) {
            'woman', 'senior' => PayrollSetting::getFloat('tax_free_woman', 425000),
            'disabled' => PayrollSetting::getFloat('tax_free_disabled', 500000),
            'freedom_fighter' => PayrollSetting::getFloat('tax_free_freedom_fighter', 525000),
            default => PayrollSetting::getFloat('tax_free_general', 375000),
        };
    }

    /**
     * Assessable income after NBR employment deduction.
     */
    public function taxableIncome(float $annualIncome, string $category = 'general'): float
    {
        $deduction = $this->employmentDeduction($annualIncome);

        return max(0, round($annualIncome - $deduction, 2));
    }

    /**
     * Build absolute NBR slabs for a taxpayer category.
     * Slabs in DB use regime=nbr with "next band" widths stored as absolute for general;
     * first band max is replaced by category tax-free limit.
     */
    public function effectiveSlabs(string $category = 'general', string $regime = 'nbr'): array
    {
        $rows = TaxSlab::active()
            ->where('regime', $regime)
            ->orderBy('sort_order')
            ->get();

        if ($rows->isEmpty()) {
            // Fallback NBR FY 2025-26 / AY 2026-27 general structure
            $free = $this->taxFreeLimit($category);

            return [
                ['min' => 0, 'max' => $free, 'rate' => 0],
                ['min' => $free, 'max' => $free + 300000, 'rate' => 10],
                ['min' => $free + 300000, 'max' => $free + 700000, 'rate' => 15],
                ['min' => $free + 700000, 'max' => $free + 1200000, 'rate' => 20],
                ['min' => $free + 1200000, 'max' => $free + 3200000, 'rate' => 25],
                ['min' => $free + 3200000, 'max' => null, 'rate' => 30],
            ];
        }

        $free = $this->taxFreeLimit($category);
        $bands = [];
        $cursor = 0.0;
        $first = true;

        foreach ($rows as $row) {
            $rate = (float) $row->rate_pct;
            if ($first) {
                $width = $free;
                $first = false;
            } elseif ($row->max_income === null) {
                $bands[] = ['min' => $cursor, 'max' => null, 'rate' => $rate];
                break;
            } else {
                $width = (float) $row->max_income - (float) $row->min_income;
            }

            $max = $cursor + $width;
            $bands[] = ['min' => $cursor, 'max' => $max, 'rate' => $rate];
            $cursor = $max;
        }

        return $bands;
    }

    public function annualTax(float $assessableIncome, string $category = 'general', string $regime = 'nbr'): float
    {
        $slabs = $this->effectiveSlabs($category, $regime);
        $tax = 0.0;

        foreach ($slabs as $slab) {
            $min = (float) $slab['min'];
            $max = $slab['max'] !== null ? (float) $slab['max'] : PHP_FLOAT_MAX;
            if ($assessableIncome <= $min) {
                continue;
            }
            $inSlab = min($assessableIncome, $max) - $min;
            if ($inSlab > 0) {
                $tax += $inSlab * ((float) $slab['rate'] / 100);
            }
        }

        $tax = round($tax, 2);

        // NBR minimum tax when income exceeds tax-free threshold
        $free = $this->taxFreeLimit($category);
        $minTax = PayrollSetting::getFloat('tax_minimum_tax', 5000);
        if ($assessableIncome > $free && $tax > 0 && $tax < $minTax) {
            $tax = $minTax;
        }

        return $tax;
    }

    public function monthlyTds(float $monthlyGross, string $category = 'general', string $regime = 'nbr'): float
    {
        $annual = $this->annualIncome($monthlyGross);
        $assessable = $this->taxableIncome($annual, $category);
        $annualTax = $this->annualTax($assessable, $category, $regime);

        return round($annualTax / 12, 2);
    }

    public function monthlyTdsForUser(User $user, float $monthlyGross): float
    {
        $category = $user->tax_category ?: 'general';

        return $this->monthlyTds($monthlyGross, $category);
    }

    public function breakdownForUser(User $user, ?float $monthlyGross = null): array
    {
        $monthly = $monthlyGross ?? (float) $user->salary;
        $category = $user->tax_category ?: 'general';
        $annual = $this->annualIncome($monthly);
        $employmentDeduction = $this->employmentDeduction($annual);
        $assessable = max(0, $annual - $employmentDeduction);
        $annualTax = $this->annualTax($assessable, $category);
        $monthlyTds = round($annualTax / 12, 2);

        return [
            'category' => $category,
            'tax_free_limit' => $this->taxFreeLimit($category),
            'annual_income' => $annual,
            'employment_deduction' => $employmentDeduction,
            'assessable_income' => $assessable,
            'annual_tax' => $annualTax,
            'monthly_tds' => $monthlyTds,
            'minimum_tax' => PayrollSetting::getFloat('tax_minimum_tax', 5000),
            'slabs' => $this->effectiveSlabs($category),
        ];
    }

    public function slabSummary(string $category = 'general', string $regime = 'nbr'): array
    {
        return collect($this->effectiveSlabs($category, $regime))->map(function (array $slab) {
            $min = (float) $slab['min'];
            $max = $slab['max'];
            $rate = (float) $slab['rate'];

            if ($max === null) {
                $label = 'Above ৳'.number_format($min, 0);
            } elseif ($min == 0.0) {
                $label = 'First ৳'.number_format((float) $max, 0);
            } else {
                $label = 'Next ৳'.number_format((float) $max - $min, 0);
            }

            return [
                'label' => $label,
                'rate' => $rate == 0.0 ? 'Nil' : $rate.'%',
                'min' => $min,
                'max' => $max !== null ? (float) $max : null,
                'rate_pct' => $rate,
            ];
        })->all();
    }

    public static function categoryOptions(): array
    {
        return [
            'general' => 'General (Male)',
            'woman' => 'Woman',
            'senior' => 'Senior Citizen (65+)',
            'disabled' => 'Person with Disability / Third Gender',
            'freedom_fighter' => 'Gazetted Freedom Fighter',
        ];
    }
}
