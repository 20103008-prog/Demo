<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('regime')->default('new');
            $table->decimal('min_income', 14, 2)->default(0);
            $table->decimal('max_income', 14, 2)->nullable();
            $table->decimal('rate_pct', 5, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payroll_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('title');
            $table->text('keywords');
            $table->text('response');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('casual')->default(12);
            $table->unsignedTinyInteger('sick')->default(6);
            $table->unsignedTinyInteger('earned')->default(15);
            $table->timestamps();
            $table->unique(['user_id', 'year']);
        });

        Schema::table('settlements', function (Blueprint $table) {
            if (! Schema::hasColumn('settlements', 'gratuity')) {
                $table->decimal('gratuity', 12, 2)->default(0)->after('years_of_service');
            }
        });

        Schema::table('hr_queries', function (Blueprint $table) {
            if (! Schema::hasColumn('hr_queries', 'ai_category')) {
                $table->string('ai_category')->nullable()->after('ai_draft');
            }
            if (! Schema::hasColumn('hr_queries', 'ai_confidence')) {
                $table->decimal('ai_confidence', 5, 4)->nullable()->after('ai_category');
            }
            if (! Schema::hasColumn('hr_queries', 'needs_manual_review')) {
                $table->boolean('needs_manual_review')->default(false)->after('ai_confidence');
            }
        });

        Schema::table('payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('payslips', 'attendance_deduction')) {
                $table->decimal('attendance_deduction', 12, 2)->default(0)->after('loan_deduction');
            }
            if (! Schema::hasColumn('payslips', 'unpaid_leave_deduction')) {
                $table->decimal('unpaid_leave_deduction', 12, 2)->default(0)->after('attendance_deduction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            if (Schema::hasColumn('payslips', 'attendance_deduction')) {
                $table->dropColumn('attendance_deduction');
            }
            if (Schema::hasColumn('payslips', 'unpaid_leave_deduction')) {
                $table->dropColumn('unpaid_leave_deduction');
            }
        });

        Schema::table('hr_queries', function (Blueprint $table) {
            foreach (['ai_category', 'ai_confidence', 'needs_manual_review'] as $col) {
                if (Schema::hasColumn('hr_queries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('settlements', function (Blueprint $table) {
            if (Schema::hasColumn('settlements', 'gratuity')) {
                $table->dropColumn('gratuity');
            }
        });

        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('payroll_faqs');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('tax_slabs');
        Schema::dropIfExists('payroll_settings');
    }
};
