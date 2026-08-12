<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Payslip {{ $p->month }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#222}table{width:100%;border-collapse:collapse}td,th{padding:6px;border-bottom:1px solid #ddd}.head{font-size:18px;font-weight:bold;margin-bottom:8px}</style>
</head>
<body>
<div class="head">Payslip — {{ $p->month }}</div>
<p><strong>{{ $p->user->name }}</strong> ({{ $p->user->employee_code }}) · {{ $p->user->department }}</p>
<table>
<tr><td>Basic</td><td align="right">{{ number_format($p->basic,2) }}</td></tr>
<tr><td>HRA</td><td align="right">{{ number_format($p->hra,2) }}</td></tr>
<tr><td>DA</td><td align="right">{{ number_format($p->da,2) }}</td></tr>
<tr><td>Allowances</td><td align="right">{{ number_format($p->allowances,2) }}</td></tr>
<tr><td>Overtime</td><td align="right">{{ number_format($p->overtime_pay,2) }}</td></tr>
<tr><td>Night Differential</td><td align="right">{{ number_format($p->night_differential ?? 0,2) }}</td></tr>
<tr><td><strong>Gross</strong></td><td align="right"><strong>{{ number_format($p->gross,2) }}</strong></td></tr>
<tr><td>NBR TDS</td><td align="right">{{ number_format($p->tds,2) }}</td></tr>
<tr><td>Investment Rebate</td><td align="right">{{ number_format($p->investment_rebate ?? 0,2) }}</td></tr>
<tr><td>PF Employee</td><td align="right">{{ number_format($p->pf_employee,2) }}</td></tr>
<tr><td>Loan</td><td align="right">{{ number_format($p->loan_deduction,2) }}</td></tr>
<tr><td>Other Deductions</td><td align="right">{{ number_format($p->other_deductions,2) }}</td></tr>
<tr><td><strong>Net Pay (BDT)</strong></td><td align="right"><strong>{{ number_format($p->net,2) }}</strong></td></tr>
</table>
</body>
</html>
