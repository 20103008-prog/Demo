<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function analytics(AnalyticsService $analytics): View
    {
        $data = $analytics->dashboard();

        return view('admin.analytics', $data);
    }
}
