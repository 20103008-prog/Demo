<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BiometricDevice extends Model
{
    protected $fillable = [
        'name', 'serial', 'ip', 'location', 'branch_id', 'api_token',
        'is_active', 'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_sync_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function punches(): HasMany
    {
        return $this->hasMany(BiometricPunch::class, 'device_id');
    }

    public static function makeToken(): string
    {
        return Str::random(48);
    }
}
