<?php

if (! function_exists('money')) {
    function money($amount): string
    {
        // U+09F3 BENGALI RUPEE SIGN (৳)
        return "\u{09F3}".number_format((float) $amount, 0, '.', ',');
    }
}

if (! function_exists('bdt')) {
    function bdt($amount): string
    {
        return money($amount);
    }
}

/** @deprecated Use money()/bdt() — kept for Blade compatibility */
if (! function_exists('inr')) {
    function inr($amount): string
    {
        return money($amount);
    }
}

if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'Pending' => 'warning',
            'Approved' => 'success',
            'Rejected' => 'danger',
            'Active' => 'success',
            'Inactive' => 'secondary',
            'Closed' => 'secondary',
            'Resolved' => 'success',
            'Present' => 'success',
            'Absent' => 'danger',
            'Late' => 'warning',
            'Late (Absence Rule)' => 'danger',
            'Early Departure' => 'warning',
            'Not Punched' => 'secondary',
            'Half-day' => 'info',
            'Pending Approval' => 'warning',
            'Approved' => 'success',
            'High' => 'danger',
            'Medium' => 'warning',
            'Low' => 'success',
            'Submitted' => 'info',
            'Queued' => 'secondary',
            'Paid' => 'primary',
            'Draft' => 'secondary',
            'Applied' => 'success',
            'Initiated' => 'info',
            'Generated' => 'primary',
            'High Priority' => 'danger',
            'info' => 'info',
            'warning' => 'warning',
            'critical' => 'danger',
            'New' => 'primary',
            'Contacted' => 'info',
            'Closed' => 'secondary',
        ];

        // keep original High/Medium/Low priority keys too
        $map['High'] = $map['High'] ?? 'danger';

        $color = $map[$status] ?? 'secondary';

        return '<span class="badge text-bg-'.$color.' badge-status">'.e($status).'</span>';
    }
}
