<?php

namespace Modules\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    abstract protected function moduleName(): string;

    abstract protected function moduleAlias(): string;

    protected function modulePath(string $path = ''): string
    {
        $base = base_path('Modules'.DIRECTORY_SEPARATOR.$this->moduleName());

        return $path === '' ? $base : $base.DIRECTORY_SEPARATOR.ltrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $views = $this->modulePath('Resources/views');
        if (is_dir($views)) {
            $this->loadViewsFrom($views, $this->moduleAlias());
        }

        $webRoutes = $this->modulePath('Routes/web.php');
        if (file_exists($webRoutes)) {
            Route::middleware('web')->group($webRoutes);
        }

        $apiRoutes = $this->modulePath('Routes/api.php');
        if (file_exists($apiRoutes)) {
            Route::middleware('api')
                ->prefix('api')
                ->group($apiRoutes);
        }
    }
}
