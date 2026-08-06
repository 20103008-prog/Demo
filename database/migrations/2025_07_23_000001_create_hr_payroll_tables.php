<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->string('role')->default('employee')->after('email'); // employee|manager|admin
            $table->string('department')->nullable()->after('role');
            $table->string('job_title')->nullable()->after('department');
            $table->decimal('salary', 12, 2)->default(0)->after('job_title');
            $table->string('status')->default('Active')->after('salary'); // Active|Inactive
            $table->date('join_date')->nullable()->after('status');
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('check_in')->nullable();
            $table->string('check_out')->nullable();
            $table->decimal('hours', 5, 2)->default(0);
            $table->string('status')->default('Present'); // Present|Absent|Late|Half-day
            $table->timestamps();
            $table->unique(['user_id', 'date']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // Casual|Sick|Earned|Compensatory
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedInteger('days');
            $table->text('reason')->nullable();
            $table->string('status')->default('Pending'); // Pending|Approved|Rejected
            $table->date('applied_on');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_comment')->nullable();
            $table->timestamps();
        });

        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->text('reason')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // Personal|Housing|Car|Education
            $table->decimal('amount', 14, 2);
            $table->decimal('emi', 12, 2);
            $table->decimal('outstanding', 14, 2);
            $table->string('status')->default('Active'); // Active|Closed
            $table->date('start_date');
            $table->timestamps();
        });

        Schema::create('hr_queries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('subject');
            $table->text('description');
            $table->string('status')->default('Pending'); // Pending|Resolved
            $table->string('priority')->default('Medium'); // High|Medium|Low
            $table->text('ai_draft')->nullable();
            $table->text('response')->nullable();
            $table->date('submitted_on');
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month'); // Jul 2025
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month_num');
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('hra', 12, 2)->default(0);
            $table->decimal('da', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('gross', 12, 2)->default(0);
            $table->decimal('tds', 12, 2)->default(0);
            $table->decimal('pf_employee', 12, 2)->default(0);
            $table->decimal('pf_employer', 12, 2)->default(0);
            $table->decimal('loan_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->string('status')->default('Generated');
            $table->timestamps();
            $table->unique(['user_id', 'year', 'month_num']);
        });

        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic', 12, 2);
            $table->decimal('years_of_service', 5, 2);
            $table->decimal('festival_bonus', 12, 2)->default(0);
            $table->decimal('performance_bonus', 12, 2)->default(0);
            $table->string('status')->default('Pending'); // Pending|Approved|Paid
            $table->timestamps();
        });

        Schema::create('increments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_salary', 12, 2);
            $table->decimal('increment_pct', 5, 2);
            $table->decimal('new_salary', 12, 2);
            $table->date('effective_date');
            $table->string('reason')->nullable();
            $table->string('status')->default('Draft'); // Draft|Approved|Applied
            $table->timestamps();
        });

        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('exit_date');
            $table->decimal('last_basic', 12, 2);
            $table->decimal('years_of_service', 5, 2);
            $table->decimal('gratuity', 12, 2)->default(0);
            $table->decimal('leave_encashment', 12, 2)->default(0);
            $table->decimal('final_month_salary', 12, 2)->default(0);
            $table->decimal('outstanding_loan', 12, 2)->default(0);
            $table->decimal('net_settlement', 12, 2)->default(0);
            $table->string('status')->default('Initiated'); // Initiated|Approved|Paid
            $table->timestamps();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // leave|payroll|query|loan|system
            $table->string('title');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('action');
            $table->string('module');
            $table->string('user_name');
            $table->string('role')->nullable();
            $table->text('details')->nullable();
            $table->string('severity')->default('info'); // info|warning|critical
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('settlements');
        Schema::dropIfExists('increments');
        Schema::dropIfExists('bonuses');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('hr_queries');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_records');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_code', 'role', 'department', 'job_title',
                'salary', 'status', 'join_date',
            ]);
        });
    }
};
