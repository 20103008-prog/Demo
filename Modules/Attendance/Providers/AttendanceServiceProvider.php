<?php

namespace Modules\Attendance\Providers;

use Modules\Support\BaseModuleServiceProvider;

class AttendanceServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Attendance';
    }

    protected function moduleAlias(): string
    {
        return 'attendance';
    }
}
