<?php

namespace Modules\Leave\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    protected $fillable = [
        'type', 'annual_quota', 'carry_forward', 'max_carry_forward',
        'allow_half_day', 'sandwich_rule', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'carry_forward' => 'boolean',
            'allow_half_day' => 'boolean',
            'sandwich_rule' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
