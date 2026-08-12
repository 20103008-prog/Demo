<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentProof extends Model
{
    protected $fillable = [
        'user_id', 'fiscal_year', 'category', 'amount', 'file_path',
        'status', 'notes', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
