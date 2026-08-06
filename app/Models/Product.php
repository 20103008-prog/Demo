<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'slug', 'name', 'tagline', 'short_description', 'description',
        'category', 'price_monthly', 'price_yearly', 'currency', 'icon',
        'badge', 'features', 'is_featured', 'is_published', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
        ];
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(SiteInquiry::class);
    }

    public function formatPrice(string $period = 'monthly'): string
    {
        $amount = $period === 'yearly' ? $this->price_yearly : $this->price_monthly;
        if ((float) $amount <= 0) {
            return 'Custom';
        }

        return '৳'.number_format((float) $amount, 0).($period === 'yearly' ? '/yr' : '/mo');
    }
}
