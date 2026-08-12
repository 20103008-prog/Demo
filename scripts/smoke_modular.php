<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

function hit($kernel, string $method, string $uri, $user = null): array
{
    $request = Illuminate\Http\Request::create($uri, $method);
    if ($user) {
        $request->setUserResolver(fn () => $user);
        auth()->setUser($user);
    }
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $body = (string) $response->getContent();
        $err = $status >= 500;

        return [$status, $err, $err ? substr(strip_tags($body), 0, 200) : ''];
    } catch (Throwable $e) {
        return [500, true, $e->getMessage()];
    }
}

[$code] = hit($kernel, 'GET', '/login');
echo "GET /login => {$code}\n";
[$code] = hit($kernel, 'GET', '/');
echo "GET / => {$code}\n";

$tests = [
    ['admin', 'admin@corp.com', [
        '/admin/dashboard', '/admin/employees', '/admin/payroll', '/admin/analytics', '/admin/companies', '/admin/tax-pf',
    ]],
    ['employee', 'arjun.sharma@corp.com', [
        '/employee/dashboard', '/employee/attendance', '/employee/payroll', '/employee/leave',
    ]],
    ['manager', 'divya.krishnan@corp.com', [
        '/manager/dashboard', '/manager/team', '/manager/reports',
    ]],
];

foreach ($tests as [$role, $email, $paths]) {
    $user = App\Models\User::where('email', $email)->first();
    if (! $user) {
        echo "MISSING USER {$email}\n";
        continue;
    }
    foreach ($paths as $path) {
        [$status, $err, $msg] = hit($kernel, 'GET', $path, $user);
        echo strtoupper($role)." {$path} => {$status}".($err ? " ERR: {$msg}" : '')."\n";
    }
    auth()->logout();
}

[$code] = hit($kernel, 'POST', '/api/biometric/punches');
echo "POST /api/biometric/punches => {$code}\n";
