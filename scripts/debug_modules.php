<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'Payroll provider exists: '.(class_exists(Modules\Payroll\Providers\PayrollServiceProvider::class) ? 'yes' : 'no').PHP_EOL;
echo 'Modules dirs: '.implode(',', array_map('basename', glob(base_path('Modules/*'), GLOB_ONLYDIR) ?: [])).PHP_EOL;

$json = file_get_contents(base_path('Modules/Payroll/module.json'));
echo 'JSON raw: '.$json.PHP_EOL;
echo 'JSON decoded: ';
var_export(json_decode($json, true));
echo PHP_EOL;

echo "Loaded module providers:\n";
foreach (array_keys($app->getLoadedProviders()) as $p) {
    if (str_contains($p, 'Modules') || str_contains($p, 'Module')) {
        echo $p.PHP_EOL;
    }
}

echo 'Route count: '.count(app('router')->getRoutes()).PHP_EOL;
