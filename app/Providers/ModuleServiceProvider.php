<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulesPath = base_path('Modules');

        if (! File::isDirectory($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $moduleDir) {
            $jsonPath = $moduleDir.DIRECTORY_SEPARATOR.'module.json';

            if (! File::exists($jsonPath)) {
                continue;
            }

            $raw = File::get($jsonPath);
            // Strip UTF-8 BOM if present (Windows editors / PowerShell).
            if (str_starts_with($raw, "\xEF\xBB\xBF")) {
                $raw = substr($raw, 3);
            }

            $config = json_decode($raw, true);

            if (! ($config['enabled'] ?? true)) {
                continue;
            }

            foreach ($config['providers'] ?? [] as $provider) {
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }
    }

    public function boot(): void
    {
        //
    }
}
