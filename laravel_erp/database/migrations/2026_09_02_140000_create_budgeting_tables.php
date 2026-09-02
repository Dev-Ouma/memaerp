<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_number_sequences', function (Blueprint $table): void {
            $table->unsignedSmallInteger('fiscal_year')->primary();
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
        });

        Schema::create('budget_submitters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('department', 190);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('granted_at');
            $table->timestamps();
        });

        Schema::create('budget_proposals', function (Blueprint $table): void {
            $table->id();
            $table->string('proposal_ref', 40)->unique();
            $table->unsignedSmallInteger('fiscal_year')->index();
            $table->string('trimester', 30)->index();
            $table->string('department', 190)->index();
            $table->text('description');
            $table->decimal('requested_amount', 16, 2);
            $table->decimal('approved_amount', 16, 2)->default(0);
            $table->string('status', 40)->default('DRAFT')->index();
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fiscal_year', 'trimester', 'status']);
        });

        Schema::create('budget_proposal_transitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('budget_proposal_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 40);
            $table->string('to_status', 40);
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->text('reason')->nullable();
            $table->decimal('approved_amount', 16, 2)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['budget_proposal_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_proposal_transitions');
        Schema::dropIfExists('budget_proposals');
        Schema::dropIfExists('budget_submitters');
        Schema::dropIfExists('budget_number_sequences');
    }
};
