<?php

namespace Modules\HR\Providers;

use Modules\Support\BaseModuleServiceProvider;

class HRServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'HR';
    }

    protected function moduleAlias(): string
    {
        return 'hr';
    }
}
