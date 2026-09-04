<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_records', function (Blueprint $table): void {
            $table->id();
            $table->string('module', 40)->index();
            $table->string('kind', 80)->index();
            $table->string('code', 80);
            $table->string('status', 80)->default('Active')->index();
            $table->string('title', 255);
            $table->string('party_name', 190)->nullable();
            $table->string('party_ref', 80)->nullable();
            $table->string('programme', 190)->nullable();
            $table->string('department', 190)->nullable();
            $table->decimal('amount', 16, 2)->nullable();
            $table->decimal('amount_secondary', 16, 2)->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->date('occurred_on')->nullable();
            $table->json('fields')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['module', 'kind', 'code']);
            $table->index(['module', 'kind', 'status']);
        });

        Schema::table('staff', function (Blueprint $table): void {
            $table->string('staff_no', 40)->nullable()->unique();
            $table->string('phone', 50)->nullable();
            $table->string('designation', 190)->nullable();
            $table->string('department', 190)->nullable();
            $table->string('employment_type', 80)->nullable();
            $table->string('rank', 80)->nullable();
            $table->string('qualification', 255)->nullable();
            $table->string('employment_status', 40)->default('ACTIVE');
            $table->date('joined_at')->nullable();
        });

        Schema::table('staff_leave_requests', function (Blueprint $table): void {
            $table->string('leave_type', 80)->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('days')->nullable();
            $table->string('reliever', 190)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('staff_leave_requests', function (Blueprint $table): void {
            $table->dropColumn(['leave_type', 'end_date', 'days', 'reliever']);
        });
        Schema::table('staff', function (Blueprint $table): void {
            $table->dropColumn([
                'staff_no', 'phone', 'designation', 'department', 'employment_type',
                'rank', 'qualification', 'employment_status', 'joined_at',
            ]);
        });
        Schema::dropIfExists('module_records');
    }
};
