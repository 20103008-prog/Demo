<?php

namespace Modules\Analytics\Services;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Simple attrition risk score 0–100 based on attendance/leave patterns.
     */
    public function attritionRisk(User $user): array
    {
        $start = now()->subMonths(3)->startOfDay();
        $lates = AttendanceRecord::where('user_id', $user->id)
            ->where('date', '>=', $start)
            ->whereIn('status', ['Late', 'Late (Absence Rule)'])
            ->count();
        $absents = AttendanceRecord::where('user_id', $user->id)
            ->where('date', '>=', $start)
            ->where('status', 'Absent')
            ->count();
        $leaves = LeaveRequest::where('user_id', $user->id)
            ->where('applied_on', '>=', $start)
            ->count();
        $early = AttendanceRecord::where('user_id', $user->id)
            ->where('date', '>=', $start)
            ->where('status', 'Early Departure')
            ->count();

        $score = min(100, ($lates * 3) + ($absents * 8) + ($leaves * 2) + ($early * 4));
        $level = $score >= 60 ? 'High' : ($score >= 30 ? 'Medium' : 'Low');

        return compact('score', 'level', 'lates', 'absents', 'leaves', 'early');
    }

    public function dashboard(): array
    {
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->get();
        $risks = $employees->map(function (User $u) {
            $r = $this->attritionRisk($u);

            return [
                'id' => $u->id,
                'name' => $u->name,
                'department' => $u->department,
                'score' => $r['score'],
                'level' => $r['level'],
            ];
        })->sortByDesc('score')->values();

        $payrollTrend = Payslip::selectRaw('year, month_num, SUM(net) as total, COUNT(*) as cnt')
            ->groupBy('year', 'month_num')
            ->orderBy('year')
            ->orderBy('month_num')
            ->limit(12)
            ->get();

        $deptCost = User::where('role', '!=', 'admin')
            ->selectRaw('department, COUNT(*) as headcount, SUM(salary) as cost')
            ->groupBy('department')
            ->get();

        $presentToday = AttendanceRecord::whereDate('date', today())
            ->whereIn('status', ['Present', 'Late', 'Half-day', 'Early Departure'])
            ->count();

        return [
            'headcount' => $employees->count(),
            'present_today' => $presentToday,
            'high_risk' => $risks->where('level', 'High')->count(),
            'risks' => $risks->take(15),
            'payroll_trend' => $payrollTrend,
            'dept_cost' => $deptCost,
        ];
    }
}
