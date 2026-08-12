<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Bonus;
use App\Models\HrQuery;
use App\Models\Increment;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'employee_code' => 'ADM001',
            'name' => 'Admin',
            'email' => 'admin@corp.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
            'department' => 'HR',
            'job_title' => 'System Administrator',
            'salary' => 120000,
            'status' => 'Active',
            'join_date' => '2018-01-01',
            'email_verified_at' => now(),
        ]);

        $employees = [
            ['EMP001', 'Arjun Sharma', 'arjun.sharma@corp.com', 'employee', 'Engineering', 'Senior Developer', 85000, 'Active', '2021-03-15'],
            ['EMP002', 'Priya Nair', 'priya.nair@corp.com', 'employee', 'Engineering', 'Frontend Developer', 68000, 'Active', '2022-07-01'],
            ['EMP003', 'Rahul Mehta', 'rahul.mehta@corp.com', 'employee', 'Finance', 'Financial Analyst', 72000, 'Active', '2020-11-20'],
            ['EMP004', 'Sneha Gupta', 'sneha.gupta@corp.com', 'employee', 'HR', 'HR Executive', 55000, 'Active', '2023-01-10'],
            ['EMP005', 'Vikram Patel', 'vikram.patel@corp.com', 'employee', 'Engineering', 'Tech Lead', 98000, 'Active', '2019-06-03'],
            ['EMP006', 'Divya Krishnan', 'divya.krishnan@corp.com', 'manager', 'Engineering', 'Marketing Manager', 78000, 'Active', '2020-09-14'],
            ['EMP007', 'Aditya Kumar', 'aditya.kumar@corp.com', 'employee', 'Finance', 'Senior Analyst', 80000, 'Active', '2021-08-22'],
            ['EMP008', 'Meera Reddy', 'meera.reddy@corp.com', 'manager', 'HR', 'HR Manager', 88000, 'Active', '2018-12-01'],
            ['EMP009', 'Sanjay Desai', 'sanjay.desai@corp.com', 'employee', 'Marketing', 'Content Strategist', 62000, 'Inactive', '2022-03-15'],
            ['EMP010', 'Kavitha Rajan', 'kavitha.rajan@corp.com', 'employee', 'Engineering', 'QA Engineer', 60000, 'Active', '2023-06-05'],
        ];

        $map = ['admin' => $admin];
        foreach ($employees as $e) {
            $map[$e[0]] = User::create([
                'employee_code' => $e[0],
                'name' => $e[1],
                'email' => $e[2],
                'password' => Hash::make('demo1234'),
                'role' => $e[3],
                'department' => $e[4],
                'job_title' => $e[5],
                'salary' => $e[6],
                'status' => $e[7],
                'join_date' => $e[8],
                'email_verified_at' => now(),
            ]);
        }

        // Fix Divya department to Marketing for manager demo of marketing... actually prototype has her as Marketing Manager but OT is Engineering team. Keep Engineering for leave approvals of eng team - wait prototype says Marketing Manager in Marketing dept. Manager leave approvals show Engineering leaves. I'll put Divya in Engineering as manager for demo coherence.
        $map['EMP006']->update(['department' => 'Engineering', 'job_title' => 'Engineering Manager']);

        $attendance = [
            ['2025-07-22', '09:02', '18:15', 9.2, 'Present'],
            ['2025-07-21', '08:55', '18:00', 9.1, 'Present'],
            ['2025-07-18', '09:30', '18:30', 9.0, 'Late'],
            ['2025-07-17', '08:58', '18:05', 9.1, 'Present'],
            ['2025-07-16', '09:01', '13:30', 4.5, 'Half-day'],
            ['2025-07-15', '08:50', '18:10', 9.3, 'Present'],
            ['2025-07-14', '09:10', '18:00', 8.8, 'Present'],
            ['2025-07-11', null, null, 0, 'Absent'],
            ['2025-07-10', '09:00', '18:00', 9.0, 'Present'],
            ['2025-07-09', '08:45', '17:55', 9.2, 'Present'],
        ];
        foreach ($attendance as $a) {
            AttendanceRecord::create([
                'user_id' => $map['EMP001']->id,
                'date' => $a[0],
                'check_in' => $a[1],
                'check_out' => $a[2],
                'hours' => $a[3],
                'status' => $a[4],
            ]);
        }

        $leaves = [
            ['LV001', 'EMP002', 'Sick', '2025-07-28', '2025-07-29', 2, 'Fever and doctor visit', 'Pending', '2025-07-22'],
            ['LV002', 'EMP010', 'Casual', '2025-07-31', '2025-07-31', 1, 'Personal errand', 'Pending', '2025-07-21'],
            ['LV003', 'EMP003', 'Earned', '2025-08-04', '2025-08-08', 5, 'Annual family vacation', 'Pending', '2025-07-20'],
            ['LV004', 'EMP001', 'Casual', '2025-07-10', '2025-07-10', 1, 'Family function', 'Approved', '2025-07-07'],
            ['LV005', 'EMP005', 'Sick', '2025-07-03', '2025-07-04', 2, 'Flu', 'Approved', '2025-07-02'],
            ['LV006', 'EMP004', 'Earned', '2025-06-23', '2025-06-27', 5, 'Holiday trip', 'Rejected', '2025-06-15'],
        ];
        foreach ($leaves as $l) {
            LeaveRequest::create([
                'code' => $l[0],
                'user_id' => $map[$l[1]]->id,
                'type' => $l[2],
                'from_date' => $l[3],
                'to_date' => $l[4],
                'days' => $l[5],
                'reason' => $l[6],
                'status' => $l[7],
                'applied_on' => $l[8],
            ]);
        }

        $ots = [
            ['OT001', 'EMP001', '2025-07-21', 2.5, 'Production deployment support', 'Pending'],
            ['OT002', 'EMP002', '2025-07-20', 1.5, 'Critical bug fix for client demo', 'Pending'],
            ['OT003', 'EMP005', '2025-07-18', 3.0, 'Sprint release preparation', 'Approved'],
            ['OT004', 'EMP010', '2025-07-17', 2.0, 'Regression testing for v2.4', 'Approved'],
            ['OT005', 'EMP006', '2025-07-15', 1.0, 'Campaign launch deadline', 'Rejected'],
        ];
        foreach ($ots as $o) {
            OvertimeRequest::create([
                'code' => $o[0],
                'user_id' => $map[$o[1]]->id,
                'date' => $o[2],
                'hours' => $o[3],
                'reason' => $o[4],
                'status' => $o[5],
            ]);
        }

        $loans = [
            ['LN001', 'EMP001', 'Personal', 200000, 24, 9500, 142500, 'Active', '2024-10-01'],
            ['LN002', 'EMP005', 'Housing', 1500000, 84, 18000, 1380000, 'Active', '2024-03-01'],
            ['LN003', 'EMP003', 'Car', 450000, 40, 12000, 0, 'Closed', '2022-06-01'],
            ['LN004', 'EMP007', 'Education', 300000, 36, 8500, 212500, 'Active', '2024-08-01'],
            ['LN005', 'EMP002', 'Personal', 100000, 20, 5000, 75000, 'Active', '2024-12-01'],
        ];
        foreach ($loans as $l) {
            Loan::create([
                'code' => $l[0],
                'user_id' => $map[$l[1]]->id,
                'type' => $l[2],
                'amount' => $l[3],
                'installments' => $l[4],
                'emi' => $l[5],
                'outstanding' => $l[6],
                'status' => $l[7],
                'start_date' => $l[8],
            ]);
        }

        HrQuery::create([
            'code' => 'Q001', 'user_id' => $map['EMP002']->id, 'category' => 'Payroll',
            'subject' => 'Tax deduction seems higher this month',
            'description' => 'My TDS deduction for July 2025 is higher than last month.',
            'status' => 'Pending', 'priority' => 'High', 'submitted_on' => '2025-07-21',
            'ai_draft' => 'Dear Priya, your TDS for July increased due to a revision in projected annual income after your increment. Regards, HR Team.',
        ]);
        HrQuery::create([
            'code' => 'Q002', 'user_id' => $map['EMP007']->id, 'category' => 'Leave',
            'subject' => 'Leave balance discrepancy',
            'description' => 'My leave balance shows 8 casual leaves but I should have 12.',
            'status' => 'Pending', 'priority' => 'Medium', 'submitted_on' => '2025-07-20',
            'ai_draft' => 'Dear Aditya, 4 casual leaves were debited earlier this year. Please review January–February records.',
        ]);
        HrQuery::create([
            'code' => 'Q003', 'user_id' => $map['EMP010']->id, 'category' => 'Attendance',
            'subject' => 'Attendance punch not recorded on July 9',
            'description' => 'I was present on July 9 but the system shows absent.',
            'status' => 'Resolved', 'priority' => 'Low', 'submitted_on' => '2025-07-15',
            'response' => 'Attendance corrected based on badge logs.',
        ]);
        HrQuery::create([
            'code' => 'Q004', 'user_id' => $map['EMP003']->id, 'category' => 'Loan',
            'subject' => 'Loan EMI deduction for August',
            'description' => 'When will my car loan EMI stop being deducted?',
            'status' => 'Pending', 'priority' => 'High', 'submitted_on' => '2025-07-22',
            'ai_draft' => 'Dear Rahul, your car loan was fully settled in July 2025. No further deductions from August.',
        ]);

        foreach (User::where('role', '!=', 'admin')->where('status', 'Active')->get() as $emp) {
            $basic = round($emp->salary * 0.6, 2);
            $hra = round($emp->salary * 0.2, 2);
            $da = round($emp->salary * 0.1, 2);
            $allow = round($emp->salary * 0.1, 2);
            $gross = $basic + $hra + $da + $allow;
            $pf = round($basic * 0.12, 2);
            $tds = round($gross * 0.08, 2);
            $loanEmi = (float) Loan::where('user_id', $emp->id)->where('status', 'Active')->value('emi');
            Payslip::create([
                'user_id' => $emp->id,
                'month' => 'Jun 2025',
                'year' => 2025,
                'month_num' => 6,
                'basic' => $basic,
                'hra' => $hra,
                'da' => $da,
                'allowances' => $allow,
                'overtime_pay' => 0,
                'gross' => $gross,
                'tds' => $tds,
                'pf_employee' => $pf,
                'pf_employer' => $pf,
                'loan_deduction' => $loanEmi ?: 0,
                'other_deductions' => 0,
                'net' => max(0, $gross - $pf - $tds - ($loanEmi ?: 0)),
                'status' => 'Generated',
            ]);
        }

        $bonuses = [
            ['BN001', 'EMP001', 51000, 4.3, 51000, 17000, 'Pending'],
            ['BN002', 'EMP002', 40800, 3.1, 40800, 10200, 'Pending'],
            ['BN003', 'EMP003', 43200, 4.7, 43200, 14400, 'Approved'],
            ['BN004', 'EMP005', 58800, 6.1, 58800, 23520, 'Approved'],
            ['BN005', 'EMP007', 48000, 3.9, 48000, 16000, 'Paid'],
            ['BN006', 'EMP008', 52800, 6.6, 52800, 21120, 'Paid'],
        ];
        foreach ($bonuses as $b) {
            Bonus::create([
                'code' => $b[0],
                'user_id' => $map[$b[1]]->id,
                'basic' => $b[2],
                'years_of_service' => $b[3],
                'festival_bonus' => $b[4],
                'performance_bonus' => $b[5],
                'status' => $b[6],
            ]);
        }

        $incs = [
            ['INC001', 'EMP001', 85000, 15, 97750, '2025-08-01', 'Annual performance review – Exceeds Expectations', 'Draft'],
            ['INC002', 'EMP002', 68000, 12, 76160, '2025-08-01', 'Annual performance review – Meets Expectations', 'Draft'],
            ['INC003', 'EMP005', 98000, 18, 115640, '2025-08-01', 'Promoted to Principal Engineer', 'Approved'],
            ['INC004', 'EMP007', 80000, 10, 88000, '2025-08-01', 'Annual performance review', 'Applied'],
        ];
        foreach ($incs as $i) {
            Increment::create([
                'code' => $i[0],
                'user_id' => $map[$i[1]]->id,
                'current_salary' => $i[2],
                'increment_pct' => $i[3],
                'new_salary' => $i[4],
                'effective_date' => $i[5],
                'reason' => $i[6],
                'status' => $i[7],
            ]);
        }

        Settlement::create([
            'user_id' => $map['EMP009']->id,
            'exit_date' => '2025-07-15',
            'last_basic' => 37200,
            'years_of_service' => 3.33,
            'leave_encashment' => 15500,
            'last_increment_pct' => 12.00,
            'pf_employee' => 4464.00,
            'tds' => 3496.00,
            'final_month_salary' => 43700,
            'outstanding_loan' => 0,
            'net_settlement' => 35640,
            'status' => 'Initiated',
        ]);

        foreach ([
            ['leave', 'Leave Approved', 'Your casual leave for Jul 10 has been approved by manager.', false],
            ['payroll', 'Payslip Generated', 'Your June 2025 payslip is ready for download.', false],
            ['query', 'Query Resolved', 'Your query Q003 on attendance has been resolved.', false],
            ['system', 'Payroll Processing Complete', 'July 2025 payroll processed for 9 employees.', true],
            ['loan', 'EMI Deducted', 'Loan EMI of ₹9,500 deducted from July salary.', true],
        ] as $n) {
            AppNotification::create([
                'user_id' => $map['EMP001']->id,
                'type' => $n[0],
                'title' => $n[1],
                'body' => $n[2],
                'is_read' => $n[3],
            ]);
        }

        foreach ([
            ['AL001', 'Payroll Processed', 'Payroll', 'Admin', 'Admin', 'Jul 2025 payroll processed for 9 employees.', 'info', '2025-07-22 14:35:02'],
            ['AL002', 'Employee Added', 'Employees', 'Admin', 'Admin', 'New employee EMP011 – Neha Singh added.', 'info', '2025-07-22 11:20:15'],
            ['AL003', 'Leave Rejected', 'Leaves', 'Divya Krishnan', 'Manager', 'Rejected leave LV006 for Sneha Gupta.', 'warning', '2025-07-21 16:44:33'],
            ['AL004', 'Salary Modified', 'Employees', 'Admin', 'Admin', 'Basic salary of Vikram Patel updated.', 'critical', '2025-07-21 10:05:47'],
            ['AL005', 'Loan Registered', 'Loans', 'Admin', 'Admin', 'Personal loan LN005 registered for Priya Nair.', 'info', '2025-07-20 15:30:00'],
        ] as $a) {
            AuditLog::create([
                'code' => $a[0],
                'action' => $a[1],
                'module' => $a[2],
                'user_name' => $a[3],
                'role' => $a[4],
                'details' => $a[5],
                'severity' => $a[6],
                'logged_at' => $a[7],
            ]);
        }

        // Extra monthly payslips so charts are fully dynamic from DB
        $monthNames = [2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 7 => 'Jul'];
        foreach (User::where('role', '!=', 'admin')->where('status', 'Active')->get() as $emp) {
            foreach ($monthNames as $num => $label) {
                $factor = 0.96 + ($num * 0.005);
                $basic = round($emp->salary * 0.6 * $factor, 2);
                $hra = round($emp->salary * 0.2 * $factor, 2);
                $da = round($emp->salary * 0.1 * $factor, 2);
                $allow = round($emp->salary * 0.1 * $factor, 2);
                $gross = $basic + $hra + $da + $allow;
                $pf = round($basic * 0.12, 2);
                $tds = round($gross * 0.08, 2);
                $loanEmi = (float) Loan::where('user_id', $emp->id)->where('status', 'Active')->value('emi');

                Payslip::updateOrCreate(
                    ['user_id' => $emp->id, 'year' => 2025, 'month_num' => $num],
                    [
                        'month' => $label.' 2025',
                        'basic' => $basic,
                        'hra' => $hra,
                        'da' => $da,
                        'allowances' => $allow,
                        'overtime_pay' => 0,
                        'gross' => $gross,
                        'tds' => $tds,
                        'pf_employee' => $pf,
                        'pf_employer' => $pf,
                        'loan_deduction' => $loanEmi ?: 0,
                        'other_deductions' => 0,
                        'net' => max(0, $gross - $pf - $tds - ($loanEmi ?: 0)),
                        'status' => 'Generated',
                    ]
                );
            }
        }

        $this->seedProducts();
    }

    private function seedProducts(): void
    {
        $products = [
            [
                'slug' => 'hr-core',
                'name' => 'HR Core',
                'tagline' => 'Employee lifecycle management',
                'short_description' => 'Manage employees, roles, departments, and master HR records in one place.',
                'description' => 'HR Core gives your HR team a complete employee directory with CRUD, role assignment, salary structure, join dates, and status tracking. Perfect for growing teams that need clean, centralized employee data.',
                'category' => 'Module',
                'price_monthly' => 4999,
                'price_yearly' => 49990,
                'icon' => 'bi-people',
                'badge' => 'Essential',
                'is_featured' => true,
                'sort_order' => 1,
                'features' => ['Employee CRUD', 'Role & department setup', 'Bulk import ready', 'Audit-friendly records'],
            ],
            [
                'slug' => 'attendance-leave',
                'name' => 'Attendance & Leave',
                'tagline' => 'Punch, track, approve',
                'short_description' => 'Daily punch in/out, leave applications, manager approvals, and overtime workflows.',
                'description' => 'Employees punch attendance, apply leave, and managers approve leaves and overtime with bulk actions. Includes history views and department-level team visibility.',
                'category' => 'Module',
                'price_monthly' => 3999,
                'price_yearly' => 39990,
                'icon' => 'bi-clock-history',
                'badge' => 'Popular',
                'is_featured' => true,
                'sort_order' => 2,
                'features' => ['Punch in/out', 'Leave balance workflow', 'Bulk leave approval', 'Overtime reviews'],
            ],
            [
                'slug' => 'payroll-engine',
                'name' => 'Payroll Engine',
                'tagline' => 'Salary processing made simple',
                'short_description' => 'Run monthly payroll with gross, TDS, PF, loan EMI, and net pay calculations.',
                'description' => 'Process payroll for all active employees, generate payslips, deduct PF & TDS, apply loan EMIs, and keep full payroll history for reporting.',
                'category' => 'Module',
                'price_monthly' => 7999,
                'price_yearly' => 79990,
                'icon' => 'bi-cash-stack',
                'badge' => 'Best Value',
                'is_featured' => true,
                'sort_order' => 3,
                'features' => ['One-click payroll run', 'Payslip generation', 'PF & TDS deductions', 'Loan EMI auto-deduct'],
            ],
            [
                'slug' => 'tax-pf-loans',
                'name' => 'Tax, PF & Loans',
                'tagline' => 'Compliance & advances',
                'short_description' => 'Track tax slabs, PF contributions, and employee loan registers with EMI schedules.',
                'description' => 'Stay compliant with illustrative tax slabs, PF snapshots, and a full loan register covering personal, housing, car, and education loans.',
                'category' => 'Module',
                'price_monthly' => 3499,
                'price_yearly' => 34990,
                'icon' => 'bi-percent',
                'badge' => null,
                'is_featured' => false,
                'sort_order' => 4,
                'features' => ['Tax slab reference', 'PF employee/employer split', 'Loan register', 'Outstanding tracking'],
            ],
            [
                'slug' => 'bonus-settlement',
                'name' => 'Bonus & Settlement',
                'tagline' => 'Rewards and exit processing',
                'short_description' => 'Festival/performance bonuses, increments, gratuity, and final settlement workflows.',
                'description' => 'Approve bonuses and salary increments, then process exit settlements including gratuity, leave encashment, and outstanding loan adjustments.',
                'category' => 'Module',
                'price_monthly' => 2999,
                'price_yearly' => 29990,
                'icon' => 'bi-award',
                'badge' => 'New',
                'is_featured' => false,
                'sort_order' => 5,
                'features' => ['Festival & performance bonus', 'Increment letters workflow', 'Gratuity calculation', 'Final settlement status'],
            ],
            [
                'slug' => 'enterprise-suite',
                'name' => 'Enterprise Suite',
                'tagline' => 'Complete HR Payroll platform',
                'short_description' => 'All modules bundled — employee, attendance, payroll, tax, loans, AI queries, reports & audit.',
                'description' => 'The full HR Payroll Management System for mid-to-large organizations. Includes every module, role-based portals (Employee, Manager, Admin), AI-assisted HR queries, analytics, and audit logs.',
                'category' => 'Plan',
                'price_monthly' => 19999,
                'price_yearly' => 199990,
                'icon' => 'bi-building',
                'badge' => 'Enterprise',
                'is_featured' => true,
                'sort_order' => 6,
                'features' => [
                    'All modules included',
                    'Employee / Manager / Admin portals',
                    'AI query assistant',
                    'Reports & audit trail',
                    'Priority onboarding support',
                ],
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['slug' => $p['slug']], $p + ['is_published' => true, 'currency' => 'BDT']);
        }

        $this->call(PayrollRulesSeeder::class);
        $this->call(IndustrialFeaturesSeeder::class);
    }
}
