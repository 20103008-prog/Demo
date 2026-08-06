<?php

if (! function_exists('inr')) {
    function inr($amount): string
    {
        return '₹'.number_format((float) $amount, 0, '.', ',');
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
            'Half-day' => 'info',
            'Paid' => 'primary',
            'Draft' => 'secondary',
            'Applied' => 'success',
            'Initiated' => 'info',
            'Generated' => 'primary',
            'High' => 'danger',
            'Medium' => 'warning',
            'Low' => 'secondary',
            'info' => 'info',
            'warning' => 'warning',
            'critical' => 'danger',
            'New' => 'primary',
            'Contacted' => 'info',
            'Closed' => 'secondary',
        ];

        $color = $map[$status] ?? 'secondary';

        return '<span class="badge text-bg-'.$color.' badge-status">'.e($status).'</span>';
    }
}
