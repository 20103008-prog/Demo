<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            if (Schema::hasColumn('settlements', 'gratuity')) {
                $table->dropColumn('gratuity');
            }

            if (! Schema::hasColumn('settlements', 'last_increment_pct')) {
                $table->decimal('last_increment_pct', 5, 2)->default(0)->after('leave_encashment');
            }

            if (! Schema::hasColumn('settlements', 'pf_employee')) {
                $table->decimal('pf_employee', 12, 2)->default(0)->after('last_increment_pct');
            }

            if (! Schema::hasColumn('settlements', 'tds')) {
                $table->decimal('tds', 12, 2)->default(0)->after('pf_employee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            if (Schema::hasColumn('settlements', 'last_increment_pct')) {
                $table->dropColumn('last_increment_pct');
            }

            if (Schema::hasColumn('settlements', 'pf_employee')) {
                $table->dropColumn('pf_employee');
            }

            if (Schema::hasColumn('settlements', 'tds')) {
                $table->dropColumn('tds');
            }

            if (! Schema::hasColumn('settlements', 'gratuity')) {
                $table->decimal('gratuity', 12, 2)->default(0)->after('years_of_service');
            }
        });
    }
};
