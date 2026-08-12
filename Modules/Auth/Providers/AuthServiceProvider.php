<?php

namespace Modules\Auth\Providers;

use Modules\Support\BaseModuleServiceProvider;

class AuthServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Auth';
    }

    protected function moduleAlias(): string
    {
        return 'auth';
    }
}
