<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSlab extends Model
{
    protected $fillable = [
        'regime', 'min_income', 'max_income', 'rate_pct', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_income' => 'decimal:2',
            'max_income' => 'decimal:2',
            'rate_pct' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
