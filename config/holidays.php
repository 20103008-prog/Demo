<?php

return [
    // Weekends are considered non-working days and should not be counted as absences.
    'weekend_days' => [5], // Friday only

    // Government holidays by date.
    'dates' => [
        '2025-01-26' => 'Republic Day',
        '2025-08-15' => 'Independence Day',
        '2025-10-02' => 'Gandhi Jayanti',
        '2025-11-04' => 'Diwali',
        '2025-12-25' => 'Christmas Day',
    ],
];
