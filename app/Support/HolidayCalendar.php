<?php

namespace App\Support;

use Carbon\Carbon;

class HolidayCalendar
{
    public static function weekendDays(): array
    {
        return config('holidays.weekend_days', [5]);
    }

    public static function holidayDates(): array
    {
        return config('holidays.dates', []);
    }

    public static function isWeekend(Carbon $date): bool
    {
        return in_array((int) $date->dayOfWeek, self::weekendDays(), true);
    }

    public static function isHoliday(Carbon $date): bool
    {
        return array_key_exists($date->toDateString(), self::holidayDates());
    }

    public static function isNonWorkingDay(Carbon $date): bool
    {
        return self::isWeekend($date) || self::isHoliday($date);
    }

    public static function label(Carbon $date): ?string
    {
        if (self::isHoliday($date)) {
            return self::holidayDates()[$date->toDateString()];
        }

        return self::isWeekend($date) ? 'Weekend' : null;
    }
}
