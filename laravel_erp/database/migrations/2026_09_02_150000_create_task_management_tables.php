<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_management_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('role_code', 40)->unique();
            $table->string('name', 190);
            $table->string('department', 190);
            $table->string('privilege_level', 190);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('task_role_bindings', function (Blueprint $table): void {
            $table->id();
            $table->string('mapping_ref', 60)->unique();
            $table->foreignId('task_management_role_id')->constrained()->cascadeOnDelete();
            $table->string('task_template', 190);
            $table->string('trigger_event', 190);
            $table->unsignedInteger('sla_hours');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('institutional_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('task_ref', 60)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->foreignId('assignee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('priority', 20)->default('MEDIUM')->index();
            $table->string('status', 30)->default('OPEN')->index();
            $table->dateTime('due_at')->index();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['assignee_user_id', 'status']);
        });
        Schema::create('institutional_task_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institutional_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutional_task_events');
        Schema::dropIfExists('institutional_tasks');
        Schema::dropIfExists('task_role_bindings');
        Schema::dropIfExists('task_management_roles');
    }
};
