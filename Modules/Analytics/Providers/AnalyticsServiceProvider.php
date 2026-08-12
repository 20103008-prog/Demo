<?php

namespace Modules\Analytics\Providers;

use Modules\Support\BaseModuleServiceProvider;

class AnalyticsServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Analytics';
    }

    protected function moduleAlias(): string
    {
        return 'analytics';
    }
}
