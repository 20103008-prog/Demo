<?php

namespace App\Support;

use App\Models\User;

class RoleRedirector
{
    public static function home(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'manager' => route('manager.dashboard'),
            default => route('employee.dashboard'),
        };
    }
}
