<?php

namespace Modules\Organization\Providers;

use Modules\Support\BaseModuleServiceProvider;

class OrganizationServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Organization';
    }

    protected function moduleAlias(): string
    {
        return 'organization';
    }
}
