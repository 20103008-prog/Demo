<?php

namespace Database\Seeders;

use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\Company;
use App\Models\LeavePolicy;
use App\Models\PayrollSetting;
use Illuminate\Database\Seeder;

class IndustrialFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['code' => 'HT01'],
            [
                'name' => 'HT Payroll Ltd',
                'address' => 'Dhaka, Bangladesh',
                'tin' => '1234567890',
                'phone' => '01700000000',
                'email' => 'hr@htpayroll.local',
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'DHK'],
            [
                'company_id' => $company->id,
                'name' => 'Dhaka HQ',
                'address' => 'Gulshan, Dhaka',
                'is_active' => true,
            ]
        );

        foreach ([
            ['Casual', 12, true, 3, true, false],
            ['Sick', 6, false, 0, true, false],
            ['Earned', 15, true, 5, false, true],
        ] as [$type, $quota, $cf, $maxCf, $half, $sandwich]) {
            LeavePolicy::updateOrCreate(
                ['type' => $type],
                [
                    'annual_quota' => $quota,
                    'carry_forward' => $cf,
                    'max_carry_forward' => $maxCf,
                    'allow_half_day' => $half,
                    'sandwich_rule' => $sandwich,
                    'is_active' => true,
                ]
            );
        }

        PayrollSetting::updateOrCreate(
            ['key' => 'night_differential_pct'],
            ['value' => '15', 'group' => 'payroll', 'label' => 'Default Night Differential %']
        );
        PayrollSetting::updateOrCreate(
            ['key' => 'investment_rebate_pct'],
            ['value' => '15', 'group' => 'tax', 'label' => 'Investment Rebate %']
        );
        PayrollSetting::updateOrCreate(
            ['key' => 'investment_rebate_max'],
            ['value' => '1000000', 'group' => 'tax', 'label' => 'Investment Rebate Cap']
        );

        BiometricDevice::updateOrCreate(
            ['serial' => 'ZK-DEMO-001'],
            [
                'name' => 'Demo ZKTeco Gate',
                'ip' => '192.168.1.201',
                'location' => 'Main Entrance',
                'branch_id' => Branch::where('code', 'DHK')->value('id'),
                'api_token' => BiometricDevice::makeToken(),
                'is_active' => true,
            ]
        );
    }
}
