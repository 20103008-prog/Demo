<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_minutes',
        'break_minutes',
        'ot_starts_after',
        'is_overnight',
    ];

    protected function casts(): array
    {
        return [
            'grace_minutes' => 'integer',
            'break_minutes' => 'integer',
            'ot_starts_after' => 'integer',
            'is_overnight' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function getActiveAssignmentsCountAttribute(): int
    {
        return $this->assignments()->count();
    }

    public function getFormattedHoursAttribute(): string
    {
        return $this->start_time . '–' . $this->end_time;
    }
}
