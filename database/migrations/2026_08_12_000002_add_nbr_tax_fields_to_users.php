<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tax_category')) {
                $table->string('tax_category')->default('general')->after('join_date');
                // general | woman | senior | disabled | freedom_fighter
            }
            if (! Schema::hasColumn('users', 'tin')) {
                $table->string('tin', 20)->nullable()->after('tax_category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['tax_category', 'tin'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
