<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->string('tin')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('tin');
            }
            if (! Schema::hasColumn('users', 'bank_account')) {
                $table->string('bank_account')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('users', 'bank_routing')) {
                $table->string('bank_routing')->nullable()->after('bank_account');
            }
            if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('portal_login');
            }
            if (! Schema::hasColumn('users', 'two_factor_code')) {
                $table->string('two_factor_code', 10)->nullable()->after('two_factor_enabled');
            }
            if (! Schema::hasColumn('users', 'two_factor_expires_at')) {
                $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
            }
            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 5)->default('en')->after('two_factor_expires_at');
            }
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // nid, joining_letter, tin, contract, other
            $table->string('title');
            $table->string('file_path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('investment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('fiscal_year');
            $table->string('category'); // dps, life_insurance, donation, sanchaypatra, other
            $table->decimal('amount', 14, 2);
            $table->string('file_path')->nullable();
            $table->string('status')->default('Pending'); // Pending|Approved|Rejected
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Casual|Sick|Earned
            $table->unsignedTinyInteger('annual_quota')->default(12);
            $table->boolean('carry_forward')->default(false);
            $table->unsignedTinyInteger('max_carry_forward')->default(0);
            $table->boolean('allow_half_day')->default(true);
            $table->boolean('sandwich_rule')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('type');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_requests', 'is_half_day')) {
                $table->boolean('is_half_day')->default(false)->after('days');
            }
            if (! Schema::hasColumn('leave_requests', 'half_day_session')) {
                $table->string('half_day_session')->nullable()->after('is_half_day'); // AM|PM
            }
        });

        // Allow half-day fractions
        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE leave_requests MODIFY days DECIMAL(5,1) NOT NULL');
        } catch (\Throwable $e) {
            // ignore if already decimal / sqlite
        }

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('period')->default('Annual'); // Annual|Mid-year
            $table->decimal('score', 5, 2)->default(0);
            $table->decimal('recommended_increment_pct', 5, 2)->default(0);
            $table->text('comments')->nullable();
            $table->string('status')->default('Draft'); // Draft|Submitted|Approved|Applied
            $table->timestamps();
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('status')->default('Draft'); // Draft|Pending Approval|Approved|Rejected|Paid
            $table->unsignedInteger('employee_count')->default(0);
            $table->decimal('total_net', 14, 2)->default(0);
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['year', 'month']);
        });

        Schema::create('shift_swap_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('requester_shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('target_shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status')->default('Pending'); // Pending|Approved|Rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial')->unique();
            $table->string('ip')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('api_token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('biometric_punches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('biometric_devices')->nullOnDelete();
            $table->string('employee_code');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('punched_at');
            $table->string('punch_type')->default('auto'); // in|out|auto
            $table->boolean('processed')->default(false);
            $table->timestamps();
            $table->index(['employee_code', 'punched_at']);
        });

        Schema::create('offline_punches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('client_punched_at');
            $table->string('punch_type'); // in|out
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('device_id')->nullable();
            $table->string('status')->default('Queued'); // Queued|Applied|Rejected
            $table->timestamps();
        });

        Schema::table('payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('payslips', 'night_differential')) {
                $table->decimal('night_differential', 12, 2)->default(0)->after('overtime_pay');
            }
            if (! Schema::hasColumn('payslips', 'investment_rebate')) {
                $table->decimal('investment_rebate', 12, 2)->default(0)->after('tds');
            }
            if (! Schema::hasColumn('payslips', 'payroll_run_id')) {
                $table->foreignId('payroll_run_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('shifts', function (Blueprint $table) {
            if (! Schema::hasColumn('shifts', 'is_night')) {
                $table->boolean('is_night')->default(false)->after('is_overnight');
            }
            if (! Schema::hasColumn('shifts', 'night_differential_pct')) {
                $table->decimal('night_differential_pct', 5, 2)->default(0)->after('is_night');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            foreach (['night_differential', 'investment_rebate', 'payroll_run_id'] as $col) {
                if (Schema::hasColumn('payslips', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('shifts', function (Blueprint $table) {
            foreach (['is_night', 'night_differential_pct'] as $col) {
                if (Schema::hasColumn('shifts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('offline_punches');
        Schema::dropIfExists('biometric_punches');
        Schema::dropIfExists('biometric_devices');
        Schema::dropIfExists('shift_swap_requests');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('performance_reviews');
        Schema::table('leave_requests', function (Blueprint $table) {
            foreach (['is_half_day', 'half_day_session'] as $col) {
                if (Schema::hasColumn('leave_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('leave_policies');
        Schema::dropIfExists('investment_proofs');
        Schema::dropIfExists('employee_documents');
        Schema::table('users', function (Blueprint $table) {
            foreach (['company_id', 'branch_id', 'bank_name', 'bank_account', 'bank_routing', 'two_factor_enabled', 'two_factor_code', 'two_factor_expires_at', 'locale'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('branches');
        Schema::dropIfExists('companies');
    }
};
