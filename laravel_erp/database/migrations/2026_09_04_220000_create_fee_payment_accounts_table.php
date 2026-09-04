<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payment_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 255);
            $table->string('bank_name', 120)->nullable();
            $table->string('account_number', 80)->nullable();
            $table->string('integration_type', 80)->nullable();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });

        Schema::table('fee_payments', function (Blueprint $table): void {
            $table->foreignId('payment_account_id')
                ->nullable()
                ->after('fee_invoice_id')
                ->constrained('fee_payment_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_account_id');
        });
        Schema::dropIfExists('fee_payment_accounts');
    }
};
