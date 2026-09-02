<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_stakeholder_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stakeholder_type', 40);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->unique(['user_id', 'stakeholder_type']);
        });
        Schema::create('user_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('language', 12)->default('en');
            $table->string('timezone', 64)->default('Africa/Nairobi');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('browser_notifications')->default(true);
            $table->boolean('profile_discoverable')->default(false);
            $table->string('theme', 16)->default('system');
            $table->timestampsTz();
        });
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->string('subject_type', 160);
            $table->string('subject_id', 64);
            $table->jsonb('before')->nullable();
            $table->jsonb('after')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('occurred_at')->useCurrent();
            $table->index(['subject_type', 'subject_id', 'occurred_at']);
        });

        DB::table('users')->orderBy('id')->each(fn (object $user) => DB::table('user_stakeholder_types')->insert([
            'user_id' => $user->id, 'stakeholder_type' => $user->role, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]));

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE OR REPLACE FUNCTION prevent_main_audit_log_mutation() RETURNS trigger AS $$
                BEGIN RAISE EXCEPTION 'audit_logs are append-only'; END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER main_audit_logs_no_update BEFORE UPDATE ON audit_logs FOR EACH ROW EXECUTE FUNCTION prevent_main_audit_log_mutation();
                CREATE TRIGGER main_audit_logs_no_delete BEFORE DELETE ON audit_logs FOR EACH ROW EXECUTE FUNCTION prevent_main_audit_log_mutation();
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS main_audit_logs_no_update ON audit_logs; DROP TRIGGER IF EXISTS main_audit_logs_no_delete ON audit_logs; DROP FUNCTION IF EXISTS prevent_main_audit_log_mutation();');
        }
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('user_stakeholder_types');
    }
};
