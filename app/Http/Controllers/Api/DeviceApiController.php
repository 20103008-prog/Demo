<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\OfflinePunch;
use App\Models\User;
use App\Services\BiometricService;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    public function syncPunches(Request $request, BiometricService $biometric)
    {
        $token = $request->bearerToken() ?: $request->header('X-Device-Token');
        $device = BiometricDevice::where('api_token', $token)->where('is_active', true)->first();
        if (! $device) {
            return response()->json(['message' => 'Unauthorized device'], 401);
        }

        $data = $request->validate([
            'punches' => 'required|array|min:1',
            'punches.*.employee_code' => 'required_without:punches.*.pin|string',
            'punches.*.pin' => 'nullable|string',
            'punches.*.punched_at' => 'required_without:punches.*.timestamp',
            'punches.*.timestamp' => 'nullable',
            'punches.*.punch_type' => 'nullable|in:in,out,auto',
        ]);

        $count = $biometric->ingest($device, $data['punches']);

        return response()->json([
            'ok' => true,
            'device' => $device->serial,
            'accepted' => $count,
            'last_sync_at' => $device->fresh()->last_sync_at,
        ]);
    }

    public function offlinePunch(Request $request, BiometricService $biometric)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'client_punched_at' => 'required|date',
            'punch_type' => 'required|in:in,out',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'device_id' => 'nullable|string|max:100',
        ]);

        $offline = OfflinePunch::create([
            'user_id' => $user->id,
            'client_punched_at' => $data['client_punched_at'],
            'punch_type' => $data['punch_type'],
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'status' => 'Queued',
        ]);

        $biometric->applyOffline($offline);

        return response()->json(['ok' => true, 'status' => $offline->fresh()->status, 'id' => $offline->id]);
    }

    public function employees(Request $request)
    {
        $token = $request->bearerToken() ?: $request->header('X-Device-Token');
        $device = BiometricDevice::where('api_token', $token)->where('is_active', true)->first();
        if (! $device) {
            return response()->json(['message' => 'Unauthorized device'], 401);
        }

        $list = User::where('role', '!=', 'admin')->where('status', 'Active')
            ->get(['employee_code', 'name', 'department']);

        return response()->json(['employees' => $list]);
    }
}
