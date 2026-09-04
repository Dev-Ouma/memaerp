<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_windows', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 80);
            $table->string('academic_year', 20);
            $table->date('notification_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 40)->default('Open')->index();
            $table->timestamps();
            $table->unique(['type', 'academic_year']);
        });

        Schema::create('faculty_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('name', 190);
            $table->string('email', 190)->nullable();
            $table->string('reg_no', 40)->nullable()->index();
            $table->string('type', 40)->default('Intra')->index();
            $table->string('current_programme', 190)->nullable();
            $table->string('transfer_programme', 190)->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });

        Schema::create('credit_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('name', 190);
            $table->string('admission_number', 40)->nullable()->index();
            $table->string('course_code', 40)->nullable();
            $table->string('course_name', 190)->nullable();
            $table->string('programme_code', 40)->nullable();
            $table->string('programme_name', 190)->nullable();
            $table->string('prior_institution', 190)->nullable();
            $table->unsignedSmallInteger('credits')->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->string('status_type', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('course_exemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('name', 190);
            $table->string('admission_number', 40)->nullable()->index();
            $table->string('course_code', 40)->nullable();
            $table->string('course_name', 190)->nullable();
            $table->string('programme_code', 40)->nullable();
            $table->string('programme_name', 190)->nullable();
            $table->string('status', 40)->default('Pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_exemptions');
        Schema::dropIfExists('credit_transfers');
        Schema::dropIfExists('faculty_transfers');
        Schema::dropIfExists('transfer_windows');
    }
};
