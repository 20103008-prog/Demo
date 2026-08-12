<?php

namespace App\Support;

use App\Models\Holiday;
use App\Models\PayrollSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class HolidayCalendar
{
    public static function weekendDays(): array
    {
        if (Schema::hasTable('payroll_settings')) {
            $raw = PayrollSetting::getValue('weekend_days', null);
            if ($raw !== null && $raw !== '') {
                return array_map('intval', array_filter(explode(',', (string) $raw), 'strlen'));
            }
        }

        return config('holidays.weekend_days', [5]);
    }

    public static function holidayDates(): array
    {
        if (Schema::hasTable('holidays')) {
            $db = Holiday::active()->get()->mapWithKeys(
                fn (Holiday $h) => [$h->date->toDateString() => $h->name]
            )->all();

            if (! empty($db)) {
                return $db;
            }
        }

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
