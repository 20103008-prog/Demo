<?php

namespace Modules\Site\Providers;

use Modules\Support\BaseModuleServiceProvider;

class SiteServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Site';
    }

    protected function moduleAlias(): string
    {
        return 'site';
    }
}
