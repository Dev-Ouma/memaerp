<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE iam.users ADD COLUMN IF NOT EXISTS status varchar(24) NOT NULL DEFAULT 'ACTIVE'");
        DB::statement('ALTER TABLE iam.users ADD COLUMN IF NOT EXISTS session_version integer NOT NULL DEFAULT 1');
        // Laravel's encrypted:array cast produces an encrypted string, so the backing column must
        // be text. JSONB rejects the ciphertext before the model can decrypt it.
        DB::statement('ALTER TABLE iam.users ALTER COLUMN mfa_recovery_codes TYPE text USING mfa_recovery_codes::text');
        DB::statement("ALTER TABLE iam.users ADD CONSTRAINT users_status_check CHECK (status IN ('PENDING', 'ACTIVE', 'LOCKED', 'SUSPENDED', 'DEACTIVATED'))");

        DB::statement('ALTER TABLE iam.roles ADD COLUMN IF NOT EXISTS hierarchy_level smallint NOT NULL DEFAULT 10');
        DB::statement('ALTER TABLE iam.roles ADD COLUMN IF NOT EXISTS is_mfa_mandatory boolean NOT NULL DEFAULT false');
        DB::statement("ALTER TABLE iam.roles ADD COLUMN IF NOT EXISTS default_scope_type varchar(32) NOT NULL DEFAULT 'institution'");

        DB::statement(<<<'SQL'
            CREATE TABLE iam.password_history (
                id uuid PRIMARY KEY,
                institution_id uuid NOT NULL REFERENCES institution.institutions(id) ON DELETE CASCADE,
                user_id uuid NOT NULL REFERENCES iam.users(id) ON DELETE CASCADE,
                password_hash varchar(255) NOT NULL,
                created_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now()
            )
        SQL);
        DB::statement('CREATE INDEX password_history_user_idx ON iam.password_history (user_id, created_at DESC)');

        DB::statement(<<<'SQL'
            CREATE TABLE iam.password_reset_tokens (
                email varchar(255) PRIMARY KEY,
                institution_id uuid NULL REFERENCES institution.institutions(id) ON DELETE CASCADE,
                token_hash varchar(255) NOT NULL,
                expires_at timestamptz NOT NULL,
                created_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE iam.mfa_challenges (
                id uuid PRIMARY KEY,
                institution_id uuid NOT NULL REFERENCES institution.institutions(id) ON DELETE CASCADE,
                user_id uuid NOT NULL REFERENCES iam.users(id) ON DELETE CASCADE,
                token_hash char(64) NOT NULL UNIQUE,
                attempts smallint NOT NULL DEFAULT 0,
                expires_at timestamptz NOT NULL,
                consumed_at timestamptz NULL,
                ip_address varchar(45) NOT NULL,
                user_agent text NULL,
                created_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                CONSTRAINT mfa_challenges_attempts_check CHECK (attempts BETWEEN 0 AND 5)
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE iam.user_sessions (
                id uuid PRIMARY KEY,
                institution_id uuid NOT NULL REFERENCES institution.institutions(id) ON DELETE CASCADE,
                user_id uuid NOT NULL REFERENCES iam.users(id) ON DELETE CASCADE,
                token_id bigint NULL,
                session_hash char(64) NOT NULL UNIQUE,
                session_version integer NOT NULL,
                ip_address varchar(45) NOT NULL,
                user_agent text NULL,
                device_name varchar(255) NULL,
                mfa_verified boolean NOT NULL DEFAULT false,
                idle_expires_at timestamptz NOT NULL,
                absolute_expires_at timestamptz NOT NULL,
                last_activity_at timestamptz NOT NULL DEFAULT now(),
                revoked_at timestamptz NULL,
                revoked_reason varchar(100) NULL,
                created_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                updated_by uuid NULL REFERENCES iam.users(id) ON DELETE SET NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now()
            )
        SQL);
        DB::statement('CREATE INDEX user_sessions_active_idx ON iam.user_sessions (user_id, last_activity_at DESC) WHERE revoked_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS iam.user_sessions');
        DB::statement('DROP TABLE IF EXISTS iam.mfa_challenges');
        DB::statement('DROP TABLE IF EXISTS iam.password_reset_tokens');
        DB::statement('DROP TABLE IF EXISTS iam.password_history');
        DB::statement('ALTER TABLE iam.roles DROP COLUMN IF EXISTS hierarchy_level, DROP COLUMN IF EXISTS is_mfa_mandatory, DROP COLUMN IF EXISTS default_scope_type');
        DB::statement('ALTER TABLE iam.users DROP CONSTRAINT IF EXISTS users_status_check');
        DB::statement('ALTER TABLE iam.users ALTER COLUMN mfa_recovery_codes TYPE jsonb USING NULL::jsonb');
        DB::statement('ALTER TABLE iam.users DROP COLUMN IF EXISTS status, DROP COLUMN IF EXISTS session_version');
    }
};
