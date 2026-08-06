<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteInquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'company', 'phone', 'product_id', 'subject', 'message', 'status',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
