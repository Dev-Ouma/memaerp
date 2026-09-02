<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)->default('student')->index();
            $table->string('first_name')->default('');
            $table->string('last_name')->default('');
            $table->string('gender', 1)->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
        });

        Schema::create('academic_sessions', function (Blueprint $table): void {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admission_number')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_id', 'code']);
        });

        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->timestamps();
            $table->unique(['subject_id', 'date']);
        });

        Schema::create('attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->boolean('present')->default(false);
            $table->timestamps();
            $table->unique(['attendance_id', 'student_id']);
        });

        Schema::create('student_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('test_score', 5, 2)->default(0);
            $table->decimal('exam_score', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['student_id', 'subject_id']);
        });

        foreach (['student', 'staff'] as $type) {
            Schema::create("{$type}_leave_requests", function (Blueprint $table) use ($type): void {
                $table->id();
                $table->foreignId("{$type}_id")->constrained($type === 'staff' ? 'staff' : 'students')->cascadeOnDelete();
                $table->date('leave_date');
                $table->text('message');
                $table->string('status', 20)->default('pending');
                $table->timestamps();
            });
            Schema::create("{$type}_feedback", function (Blueprint $table) use ($type): void {
                $table->id();
                $table->foreignId("{$type}_id")->constrained($type === 'staff' ? 'staff' : 'students')->cascadeOnDelete();
                $table->text('message');
                $table->text('reply')->nullable();
                $table->timestamps();
            });
            Schema::create("{$type}_notifications", function (Blueprint $table) use ($type): void {
                $table->id();
                $table->foreignId("{$type}_id")->constrained($type === 'staff' ? 'staff' : 'students')->cascadeOnDelete();
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn', 20)->unique();
            $table->string('category')->nullable();
            $table->unsignedInteger('copies')->default(1);
            $table->timestamps();
        });

        Schema::create('book_loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('returned_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['book_loans', 'books', 'staff_notifications', 'staff_feedback', 'staff_leave_requests', 'student_notifications', 'student_feedback', 'student_leave_requests', 'student_results', 'attendance_records', 'attendances', 'subjects', 'students', 'staff', 'courses', 'academic_sessions'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'first_name', 'last_name', 'gender', 'address', 'profile_photo', 'is_active', 'legacy_id']);
        });
    }
};
