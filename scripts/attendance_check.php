<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cmd = $argv[1] ?? 'help';
if ($cmd === 'count') {
    $from = $argv[2] ?? '2025-07-01';
    $to = $argv[3] ?? '2025-07-31';
    $count = DB::table('attendance_records')
        ->whereBetween('date', [$from, $to])
        ->where('status', 'Absent')
        ->count();
    echo $count . PHP_EOL;
    exit(0);
}

if ($cmd === 'rows') {
    $date = $argv[2] ?? '2025-07-17';
    $rows = DB::table('attendance_records')->whereDate('date', $date)->get()->toArray();
    echo json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

echo "Usage:\n";
echo "  php scripts/attendance_check.php count <from> <to>\n";
echo "  php scripts/attendance_check.php rows <date>\n";
exit(1);
