<?php

namespace Modules\Attendance\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'code', 'user_id', 'date', 'hours', 'reason', 'status',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'hours' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
