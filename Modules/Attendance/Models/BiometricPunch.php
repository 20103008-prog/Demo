<?php

namespace Modules\Attendance\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricPunch extends Model
{
    protected $fillable = [
        'device_id', 'employee_code', 'user_id', 'punched_at', 'punch_type', 'processed',
    ];

    protected function casts(): array
    {
        return [
            'punched_at' => 'datetime',
            'processed' => 'boolean',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
