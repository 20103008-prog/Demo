<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\BiometricDevice;
use App\Models\BiometricPunch;
use App\Models\OfflinePunch;
use App\Models\User;
use Carbon\Carbon;

class BiometricService
{
    public function ingest(BiometricDevice $device, array $punches): int
    {
        $count = 0;
        foreach ($punches as $row) {
            $code = (string) ($row['employee_code'] ?? $row['pin'] ?? '');
            $at = Carbon::parse($row['punched_at'] ?? $row['timestamp'] ?? now());
            if ($code === '') {
                continue;
            }

            $user = User::where('employee_code', $code)->first();
            $punch = BiometricPunch::create([
                'device_id' => $device->id,
                'employee_code' => $code,
                'user_id' => $user?->id,
                'punched_at' => $at,
                'punch_type' => $row['punch_type'] ?? 'auto',
                'processed' => false,
            ]);

            if ($user) {
                $this->applyToAttendance($user, $at, $punch->punch_type);
                $punch->update(['processed' => true]);
            }
            $count++;
        }

        $device->update(['last_sync_at' => now()]);

        return $count;
    }

    public function applyToAttendance(User $user, Carbon $at, string $type = 'auto'): void
    {
        $record = AttendanceRecord::firstOrNew([
            'user_id' => $user->id,
            'date' => $at->toDateString(),
        ]);

        $time = $at->format('H:i');

        if ($type === 'out' || ($type === 'auto' && $record->check_in && ! $record->check_out)) {
            $record->check_out = $time;
            if ($record->check_in) {
                $in = Carbon::createFromFormat('H:i', $record->check_in);
                $out = Carbon::createFromFormat('H:i', $time);
                $record->hours = round($in->diffInMinutes($out) / 60, 1);
            }
            $record->status = $record->status ?: 'Present';
        } else {
            if (! $record->check_in) {
                $record->check_in = $time;
                $record->status = $time > '09:15' ? 'Late' : 'Present';
                $record->hours = $record->hours ?: 0;
            }
        }

        $record->save();
    }

    public function applyOffline(OfflinePunch $offline): bool
    {
        if ($offline->status !== 'Queued') {
            return false;
        }

        $this->applyToAttendance(
            $offline->user,
            $offline->client_punched_at,
            $offline->punch_type
        );

        $offline->update(['status' => 'Applied']);

        return true;
    }
}
