<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payment_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 255);
            $table->string('category', 80)->nullable();
            $table->boolean('mandatory')->default(true);
            $table->string('ledger_allocation', 120)->nullable();
            $table->string('refund_policy', 255)->nullable();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });

        Schema::create('fee_funding_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->string('allocation_rule', 255)->nullable();
            $table->unsignedInteger('candidates_count')->default(0);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_funding_sources');
        Schema::dropIfExists('fee_payment_types');
    }
};
