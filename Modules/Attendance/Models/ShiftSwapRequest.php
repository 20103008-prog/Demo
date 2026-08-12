<?php

namespace Modules\Attendance\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSwapRequest extends Model
{
    protected $fillable = [
        'requester_id', 'target_user_id', 'date', 'requester_shift_id',
        'target_shift_id', 'reason', 'status', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
