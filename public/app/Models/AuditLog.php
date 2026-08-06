<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'code', 'action', 'module', 'user_name', 'role', 'details', 'severity', 'logged_at',
    ];

    protected function casts(): array
    {
        return ['logged_at' => 'datetime'];
    }
}
