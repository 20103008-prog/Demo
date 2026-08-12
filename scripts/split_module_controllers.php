<?php

/**
 * Extract named methods from a controller source into a new module controller.
 */
function extractMethods(string $source, array $methodNames): string
{
    $methods = [];
    foreach ($methodNames as $name) {
        $pattern = '/    public function '.preg_quote($name, '/').'\s*\([^{]*\{/';
        if (! preg_match($pattern, $source, $m, PREG_OFFSET_CAPTURE)) {
            fwrite(STDERR, "Missing method: {$name}\n");
            continue;
        }
        $start = $m[0][1];
        $braceStart = strpos($source, '{', $start);
        $depth = 0;
        $len = strlen($source);
        for ($i = $braceStart; $i < $len; $i++) {
            $ch = $source[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    $methods[] = substr($source, $start, $i - $start + 1);
                    break;
                }
            }
        }
    }

    return implode("\n\n", $methods);
}

function collectUses(string $source, string $methodsBody): array
{
    preg_match_all('/^use\s+([^;]+);/m', $source, $matches);
    $needed = [];
    foreach ($matches[1] as $use) {
        $short = $use;
        if (str_contains($use, ' as ')) {
            [, $alias] = preg_split('/\s+as\s+/i', $use);
            $short = trim($alias);
        } else {
            $parts = explode('\\', $use);
            $short = end($parts);
        }
        if (str_contains($methodsBody, $short)) {
            $needed[] = 'use '.$use.';';
        }
    }

    return array_values(array_unique($needed));
}

function writeController(string $path, string $namespace, string $class, string $source, array $methods): void
{
    $body = extractMethods($source, $methods);
    $uses = collectUses($source, $body);
    $uses[] = 'use App\Http\Controllers\Controller;';
    $uses = array_unique($uses);
    sort($uses);

    $code = "<?php\n\nnamespace {$namespace};\n\n".implode("\n", $uses)."\n\nclass {$class} extends Controller\n{\n{$body}\n}\n";
    file_put_contents($path, $code);
    echo "Wrote {$path}\n";
}

$admin = file_get_contents(__DIR__.'/../app/Http/Controllers/AdminController.php');
$industrial = file_get_contents(__DIR__.'/../app/Http/Controllers/IndustrialController.php');
$employee = file_get_contents(__DIR__.'/../app/Http/Controllers/EmployeeController.php');
$manager = file_get_contents(__DIR__.'/../app/Http/Controllers/ManagerController.php');
$base = __DIR__.'/../Modules';

writeController("{$base}/Analytics/Http/Controllers/DashboardController.php", 'Modules\\Analytics\\Http\\Controllers', 'DashboardController', $admin, [
    'dashboard', 'todaySummary', 'notPunchedToday', 'todayPunches', 'lateToday', 'employeeHistory', 'reports', 'audit',
]);
writeController("{$base}/Analytics/Http/Controllers/AnalyticsController.php", 'Modules\\Analytics\\Http\\Controllers', 'AnalyticsController', $industrial, [
    'analytics',
]);

writeController("{$base}/Organization/Http/Controllers/EmployeeController.php", 'Modules\\Organization\\Http\\Controllers', 'EmployeeController', $admin, [
    'employees', 'createEmployee', 'storeEmployee', 'editEmployee', 'updateEmployee', 'destroyEmployee',
    'departments', 'storeDepartment', 'updateDepartment', 'destroyDepartment',
    'storeDesignation', 'updateDesignation', 'destroyDesignation',
]);
writeController("{$base}/Organization/Http/Controllers/CompanyController.php", 'Modules\\Organization\\Http\\Controllers', 'CompanyController', $industrial, [
    'companies', 'storeCompany', 'storeBranch',
]);

writeController("{$base}/Attendance/Http/Controllers/RosterController.php", 'Modules\\Attendance\\Http\\Controllers', 'RosterController', $admin, [
    'roster', 'storeShift', 'updateShift', 'destroyShift', 'storeShiftAssignment', 'destroyShiftAssignment',
]);
writeController("{$base}/Attendance/Http/Controllers/BiometricController.php", 'Modules\\Attendance\\Http\\Controllers', 'BiometricController', $industrial, [
    'biometrics', 'storeDevice',
]);
writeController("{$base}/Attendance/Http/Controllers/ShiftSwapController.php", 'Modules\\Attendance\\Http\\Controllers', 'ShiftSwapController', $industrial, [
    'shiftSwaps', 'reviewShiftSwap', 'myShiftSwaps', 'storeShiftSwap',
]);
writeController("{$base}/Attendance/Http/Controllers/EmployeeAttendanceController.php", 'Modules\\Attendance\\Http\\Controllers', 'EmployeeAttendanceController', $employee, [
    'attendance', 'punch',
]);
writeController("{$base}/Attendance/Http/Controllers/ManagerAttendanceController.php", 'Modules\\Attendance\\Http\\Controllers', 'ManagerAttendanceController', $manager, [
    'dashboard', 'team', 'attendance', 'overtime', 'reviewOvertime',
]);

writeController("{$base}/Leave/Http/Controllers/LeavePolicyController.php", 'Modules\\Leave\\Http\\Controllers', 'LeavePolicyController', $industrial, [
    'leavePolicies', 'updateLeavePolicy',
]);
writeController("{$base}/Leave/Http/Controllers/EmployeeLeaveController.php", 'Modules\\Leave\\Http\\Controllers', 'EmployeeLeaveController', $employee, [
    'leave', 'storeLeave', 'overtime', 'storeOvertime',
]);
writeController("{$base}/Leave/Http/Controllers/ManagerLeaveController.php", 'Modules\\Leave\\Http\\Controllers', 'ManagerLeaveController', $manager, [
    'leaves', 'reviewLeave', 'bulkApproveLeaves',
]);

writeController("{$base}/Payroll/Http/Controllers/PayrollController.php", 'Modules\\Payroll\\Http\\Controllers', 'PayrollController', $admin, [
    'payroll', 'processPayroll', 'taxPf', 'loans', 'storeLoan', 'bonus', 'storeFestivalBonus',
    'updateBonusStatus', 'storeIncrement', 'updateIncrementStatus', 'settlement', 'updateSettlement',
    'prepareSettlement', 'finalizeSettlement',
]);
writeController("{$base}/Payroll/Http/Controllers/PayrollApprovalController.php", 'Modules\\Payroll\\Http\\Controllers', 'PayrollApprovalController', $industrial, [
    'payrollApprovals', 'approvePayroll', 'bankAdvice', 'downloadPayslip', 'emailPayslip',
]);
writeController("{$base}/Payroll/Http/Controllers/EmployeePayrollController.php", 'Modules\\Payroll\\Http\\Controllers', 'EmployeePayrollController', $employee, [
    'payroll',
]);

writeController("{$base}/HR/Http/Controllers/DocumentController.php", 'Modules\\HR\\Http\\Controllers', 'DocumentController', $industrial, [
    'documents', 'storeDocument', 'myDocuments', 'uploadMyDocument',
]);
writeController("{$base}/HR/Http/Controllers/InvestmentController.php", 'Modules\\HR\\Http\\Controllers', 'InvestmentController', $industrial, [
    'investments', 'reviewInvestment', 'myInvestments', 'storeMyInvestment',
]);
writeController("{$base}/HR/Http/Controllers/AppraisalController.php", 'Modules\\HR\\Http\\Controllers', 'AppraisalController', $industrial, [
    'appraisals', 'storeAppraisal', 'applyAppraisal', 'myAppraisals',
]);
writeController("{$base}/HR/Http/Controllers/QueryController.php", 'Modules\\HR\\Http\\Controllers', 'QueryController', $admin, [
    'queries', 'replyQuery',
]);
writeController("{$base}/HR/Http/Controllers/EmployeeQueryController.php", 'Modules\\HR\\Http\\Controllers', 'EmployeeQueryController', $employee, [
    'queries', 'storeQuery',
]);
writeController("{$base}/HR/Http/Controllers/LocaleController.php", 'Modules\\HR\\Http\\Controllers', 'LocaleController', $industrial, [
    'setLocale',
]);

writeController("{$base}/Auth/Http/Controllers/TwoFactorController.php", 'Modules\\Auth\\Http\\Controllers', 'TwoFactorController', $industrial, [
    'twoFactor', 'toggleTwoFactor',
]);

writeController("{$base}/Site/Http/Controllers/AdminProductController.php", 'Modules\\Site\\Http\\Controllers', 'AdminProductController', $admin, [
    'products', 'updateProduct', 'inquiries', 'updateInquiry',
]);

writeController("{$base}/Analytics/Http/Controllers/ManagerReportController.php", 'Modules\\Analytics\\Http\\Controllers', 'ManagerReportController', $manager, [
    'reports',
]);
writeController("{$base}/Analytics/Http/Controllers/EmployeeDashboardController.php", 'Modules\\Analytics\\Http\\Controllers', 'EmployeeDashboardController', $employee, [
    'dashboard',
]);

echo "Done.\n";
