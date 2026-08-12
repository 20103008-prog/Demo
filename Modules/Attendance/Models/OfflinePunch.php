<?php

namespace Modules\Attendance\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflinePunch extends Model
{
    protected $fillable = [
        'user_id', 'client_punched_at', 'punch_type', 'lat', 'lng', 'device_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'client_punched_at' => 'datetime',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
