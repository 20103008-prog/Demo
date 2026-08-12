<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BiometricController extends Controller
{
    public function biometrics(): View
    {
        $devices = BiometricDevice::with('branch')->latest()->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.biometrics', compact('devices', 'branches'));
    }

    public function storeDevice(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'serial' => 'required|string|unique:biometric_devices,serial',
            'ip' => 'nullable|string',
            'location' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        BiometricDevice::create($data + [
            'api_token' => BiometricDevice::makeToken(),
            'is_active' => true,
        ]);

        return back()->with('success', 'Device registered. Copy the API token from the list.');
    }
}
