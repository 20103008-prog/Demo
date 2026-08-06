<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Seed initial departments
        $initialDepts = ['HR', 'IT', 'Manager', 'Engineering', 'Finance', 'Marketing', 'Operations', 'Sales'];
        foreach ($initialDepts as $dept) {
            DB::table('departments')->insertOrIgnore([
                'name' => $dept,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed initial designations
        $initialDesigs = ['Executive', 'Manager', 'Officer', 'Senior Officer', 'Staff', 'Team Lead', 'Senior Developer', 'Software Engineer'];
        foreach ($initialDesigs as $desig) {
            DB::table('designations')->insertOrIgnore([
                'name' => $desig,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};
