<?php

namespace Modules\Leave\Providers;

use Modules\Support\BaseModuleServiceProvider;

class LeaveServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Leave';
    }

    protected function moduleAlias(): string
    {
        return 'leave';
    }
}
