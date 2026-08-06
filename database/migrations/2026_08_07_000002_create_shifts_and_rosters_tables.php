<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('start_time')->default('09:00');
            $table->string('end_time')->default('17:00');
            $table->integer('grace_minutes')->default(0);
            $table->integer('break_minutes')->default(0);
            $table->integer('ot_starts_after')->default(0);
            $table->boolean('is_overnight')->default(false);
            $table->timestamps();
        });

        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->timestamps();
        });

        // Seed initial shift
        $shiftId = DB::table('shifts')->insertGetId([
            'name' => 'Evening',
            'start_time' => '14:00',
            'end_time' => '21:00',
            'grace_minutes' => 12,
            'break_minutes' => 60,
            'ot_starts_after' => 0,
            'is_overnight' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed sample assignments for active users if present
        $users = DB::table('users')->where('role', '!=', 'admin')->limit(4)->get();
        foreach ($users as $u) {
            DB::table('shift_assignments')->insert([
                'user_id' => $u->id,
                'shift_id' => $shiftId,
                'from_date' => '2026-07-01',
                'to_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shifts');
    }
};
