<?php

namespace Modules\Payroll\Providers;

use Modules\Support\BaseModuleServiceProvider;

class PayrollServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Payroll';
    }

    protected function moduleAlias(): string
    {
        return 'payroll';
    }
}
