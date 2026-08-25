<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Provider-neutral application-fee payments.
 *
 * Purpose: the mandatory KES 1,000 application fee is the gate on submission, so its evidence chain has
 * to be airtight — an effective-dated fee setup states the amount, an attempt records intent, a
 * provider event records exactly what the provider sent (once, replay-protected), a transaction records
 * verified money, and a receipt is issued once per confirmed transaction.
 *
 * Amounts are `numeric(14,2)` in major currency units and are always stored beside their currency;
 * an amount without a currency is meaningless and a mixed-unit column is a defect waiting to happen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_fee_setups', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('code', 60);
            $t->string('name', 190);
            $t->decimal('amount', 14, 2);
            $t->string('currency', 3);
            $t->date('effective_from');
            $t->date('effective_to')->nullable();
            // Institutional policy, not a hard-coded string in the application layer.
            $t->boolean('is_refundable')->default(false);
            $t->string('policy_note', 500)->nullable();
            // Optional narrowing: a fee that applies to one intake or one offering only.
            $t->foreignId('admission_intake_id')->nullable()->constrained('admission_intakes')->nullOnDelete();
            $t->foreignId('programme_offering_id')->nullable()->constrained('programme_offerings')->nullOnDelete();
            $t->jsonb('allowed_channels')->default('["MPESA_STK","MPESA_C2B","CARD","BANK","CASHIER"]');
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampsTz();
            $t->unique(['institution_id', 'code', 'effective_from', 'admission_intake_id', 'programme_offering_id'], 'fee_setup_effective_unique');
        });

        Schema::table('application_payment_attempts', function (Blueprint $t): void {
            $t->uuid('institution_id')->nullable()->index();
            $t->uuid('fee_setup_id')->nullable()->index();
            $t->decimal('expected_amount', 14, 2)->nullable();
            // MPESA_STK | MPESA_C2B | CARD | BANK | CASHIER
            $t->string('provider', 40)->nullable()->index();
            $t->string('provider_request_ref', 190)->nullable();
            $t->string('payer_msisdn_masked', 32)->nullable();
            $t->string('payer_account_masked', 64)->nullable();
            $t->string('payer_name', 190)->nullable();
            $t->timestampTz('expires_at')->nullable();
            $t->timestampTz('last_verified_at')->nullable();
            $t->unsignedSmallInteger('verification_attempts')->default(0);
            $t->string('failure_code', 60)->nullable();
            $t->string('failure_reason', 255)->nullable();
            $t->uuid('correlation_id')->nullable()->index();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('payment_provider_events', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('provider', 40);
            // The provider's own event identifier — the replay-protection key.
            $t->string('provider_event_id', 190);
            $t->string('event_type', 80)->nullable();
            $t->timestampTz('received_at')->useCurrent()->index();
            $t->timestampTz('provider_timestamp')->nullable();
            $t->string('nonce', 190)->nullable();
            $t->string('signature', 512)->nullable();
            $t->string('signature_algorithm', 40)->nullable();
            $t->boolean('signature_verified')->default(false);
            // Raw payloads are stored on the private disk, never inline: they can contain payer detail.
            $t->string('payload_ref', 255)->nullable();
            $t->string('payload_hash', 64);
            // RECEIVED | PROCESSED | DUPLICATE | REJECTED | FAILED
            $t->string('processing_status', 20)->default('RECEIVED')->index();
            $t->timestampTz('processed_at')->nullable();
            $t->string('error_code', 80)->nullable();
            $t->string('error_detail', 500)->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->uuid('correlation_id')->nullable();
            $t->timestampsTz();
            $t->unique(['provider', 'provider_event_id']);
        });

        Schema::create('payment_transactions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->foreignUuid('application_payment_attempt_id')->nullable()->constrained('application_payment_attempts')->nullOnDelete();
            $t->foreignUuid('payment_provider_event_id')->nullable()->constrained('payment_provider_events')->nullOnDelete();
            $t->uuid('fee_setup_id')->nullable();
            $t->string('provider', 40);
            $t->string('provider_transaction_ref', 190)->nullable();
            $t->decimal('amount', 14, 2);
            $t->string('currency', 3);
            $t->decimal('expected_amount', 14, 2)->nullable();
            $t->timestampTz('transaction_time')->nullable();
            $t->timestampTz('received_at')->useCurrent();
            $t->string('raw_payload_ref', 255)->nullable();
            $t->string('raw_payload_hash', 64)->nullable();
            $t->boolean('signature_verified')->default(false);
            // PAID | PENDING | FAILED | REVERSED | REFUNDED | WAIVED
            $t->string('status', 20)->index();
            // Exactly one row per application may be the authoritative application-fee settlement.
            $t->boolean('is_authoritative_fee')->default(false);
            // MATCHED | UNMATCHED | EXCEPTION | RESOLVED
            $t->string('reconciliation_state', 20)->default('UNMATCHED')->index();
            $t->uuid('reversal_of_transaction_id')->nullable();
            $t->uuid('refund_of_transaction_id')->nullable();
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('notes', 500)->nullable();
            $t->uuid('correlation_id')->nullable()->index();
            $t->timestampsTz();
            $t->index(['admission_application_id', 'status']);
        });

        Schema::create('payment_receipts', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->foreignUuid('payment_transaction_id')->unique()->constrained('payment_transactions')->restrictOnDelete();
            $t->string('receipt_number', 60)->unique();
            $t->decimal('amount', 14, 2);
            $t->string('currency', 3);
            $t->string('payment_method', 40);
            $t->timestampTz('issued_at')->useCurrent();
            $t->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $t->uuid('generated_document_id')->nullable();
            $t->string('checksum', 64);
            $t->boolean('is_void')->default(false);
            $t->string('void_reason', 255)->nullable();
            $t->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('voided_at')->nullable();
            $t->timestampsTz();
        });

        Schema::create('payment_reconciliations', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('institution_id')->index();
            $t->string('provider', 40);
            $t->string('statement_reference', 190)->nullable();
            $t->date('period_start');
            $t->date('period_end');
            $t->foreignId('run_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('run_at')->useCurrent();
            $t->unsignedInteger('matched_count')->default(0);
            $t->unsignedInteger('unmatched_count')->default(0);
            $t->unsignedInteger('exception_count')->default(0);
            $t->decimal('provider_total', 16, 2)->default(0);
            $t->decimal('ledger_total', 16, 2)->default(0);
            // RUNNING | COMPLETED | FAILED
            $t->string('status', 20)->default('COMPLETED');
            $t->string('notes', 500)->nullable();
            $t->timestampsTz();
        });

        Schema::create('payment_reconciliation_exceptions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('payment_reconciliation_id')->nullable()->constrained('payment_reconciliations')->nullOnDelete();
            $t->uuid('payment_transaction_id')->nullable()->index();
            $t->uuid('admission_application_id')->nullable()->index();
            // OVERPAYMENT | UNDERPAYMENT | WRONG_CURRENCY | DUPLICATE | ORPHAN_EVENT | UNMATCHED | SIGNATURE_FAILED
            $t->string('exception_type', 40)->index();
            $t->decimal('expected_amount', 14, 2)->nullable();
            $t->decimal('actual_amount', 14, 2)->nullable();
            $t->string('currency', 3)->nullable();
            $t->jsonb('detail')->default('{}');
            // OPEN | RESOLVED | WRITTEN_OFF
            $t->string('status', 20)->default('OPEN')->index();
            $t->foreignId('raised_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('resolved_at')->nullable();
            $t->string('resolution_note', 500)->nullable();
            $t->timestampsTz();
        });

        Schema::create('payment_waivers', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $t->uuid('fee_setup_id')->nullable();
            $t->decimal('amount_waived', 14, 2);
            $t->string('currency', 3);
            $t->string('reason_code', 80);
            $t->text('justification');
            $t->uuid('evidence_document_id')->nullable();
            $t->foreignId('approved_by')->constrained('users')->restrictOnDelete();
            $t->timestampTz('approved_at')->useCurrent();
            // ACTIVE | REVOKED
            $t->string('status', 20)->default('ACTIVE')->index();
            $t->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestampTz('revoked_at')->nullable();
            $t->string('revocation_reason', 255)->nullable();
            $t->timestampsTz();
        });

        Schema::create('payment_status_history', function (Blueprint $t): void {
            $t->id();
            $t->uuid('admission_application_id')->index();
            $t->uuid('application_payment_attempt_id')->nullable()->index();
            $t->string('from_status', 20)->nullable();
            $t->string('to_status', 20);
            $t->string('reason_code', 80)->nullable();
            $t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('actor_role', 60)->nullable();
            $t->string('source_channel', 30)->default('api');
            $t->uuid('correlation_id')->nullable();
            $t->jsonb('metadata')->default('{}');
            $t->timestampTz('created_at')->useCurrent();
        });

        $this->backfill();
    }

    /**
     * Backfill: the mandatory KES 1,000 application fee setup, and projection of any legacy
     * successfully-paid attempt into a transaction + receipt so the new gate sees existing evidence.
     */
    private function backfill(): void
    {
        $institutionId = DB::table('institutions')->value('id');
        if ($institutionId === null) {
            return;
        }
        $now = now();
        $feeSetupId = (string) Str::uuid();

        DB::table('payment_fee_setups')->insert([
            'id' => $feeSetupId,
            'institution_id' => $institutionId,
            'code' => 'APPLICATION_FEE',
            'name' => 'Application processing fee',
            'amount' => 1000.00,
            'currency' => 'KES',
            'effective_from' => '2026-01-01',
            'is_refundable' => false,
            'policy_note' => 'Mandatory and non-refundable under the current institutional policy.',
            'allowed_channels' => json_encode(['MPESA_STK', 'MPESA_C2B', 'CARD', 'BANK', 'CASHIER']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('application_payment_attempts')->update([
            'institution_id' => $institutionId,
            'fee_setup_id' => $feeSetupId,
            'expected_amount' => 1000.00,
        ]);
        DB::statement('update application_payment_attempts set provider = upper(channel) where provider is null');

        foreach (DB::table('application_payment_attempts')->where('status', 'PAID')->get() as $attempt) {
            $transactionId = (string) Str::uuid();
            DB::table('payment_transactions')->insert([
                'id' => $transactionId,
                'admission_application_id' => $attempt->admission_application_id,
                'application_payment_attempt_id' => $attempt->id,
                'fee_setup_id' => $feeSetupId,
                'provider' => strtoupper((string) $attempt->channel),
                'provider_transaction_ref' => $attempt->reference,
                'amount' => (float) $attempt->amount,
                'currency' => $attempt->currency,
                'expected_amount' => 1000.00,
                'transaction_time' => $attempt->paid_at,
                'received_at' => $attempt->paid_at ?? $now,
                'signature_verified' => false,
                'status' => 'PAID',
                'is_authoritative_fee' => true,
                'reconciliation_state' => 'MATCHED',
                'notes' => 'Backfilled from legacy application_payment_attempts row.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            if ($attempt->receipt_number !== null) {
                DB::table('payment_receipts')->insert([
                    'id' => (string) Str::uuid(),
                    'admission_application_id' => $attempt->admission_application_id,
                    'payment_transaction_id' => $transactionId,
                    'receipt_number' => $attempt->receipt_number,
                    'amount' => (float) $attempt->amount,
                    'currency' => $attempt->currency,
                    'payment_method' => strtoupper((string) $attempt->channel),
                    'issued_at' => $attempt->paid_at ?? $now,
                    'checksum' => hash('sha256', $transactionId.$attempt->receipt_number),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            DB::table('admission_applications')
                ->where('id', $attempt->admission_application_id)
                ->update(['payment_status' => 'PAID', 'fee_setup_id' => $feeSetupId, 'fee_amount_expected' => 1000, 'fee_currency' => 'KES']);
        }
    }

    public function down(): void
    {
        foreach ([
            'payment_status_history', 'payment_waivers', 'payment_reconciliation_exceptions',
            'payment_reconciliations', 'payment_receipts', 'payment_transactions',
            'payment_provider_events', 'payment_fee_setups',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('application_payment_attempts', function (Blueprint $t): void {
            $t->dropColumn([
                'institution_id', 'fee_setup_id', 'expected_amount', 'provider', 'provider_request_ref',
                'payer_msisdn_masked', 'payer_account_masked', 'payer_name', 'expires_at',
                'last_verified_at', 'verification_attempts', 'failure_code', 'failure_reason',
                'correlation_id', 'created_by',
            ]);
        });
    }
};
