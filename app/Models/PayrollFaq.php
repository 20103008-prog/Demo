<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollFaq extends Model
{
    protected $fillable = [
        'category', 'title', 'keywords', 'response', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function keywordList(): array
    {
        return collect(preg_split('/[,|]+/', strtolower($this->keywords)))
            ->map(fn ($k) => trim($k))
            ->filter()
            ->values()
            ->all();
    }
}
