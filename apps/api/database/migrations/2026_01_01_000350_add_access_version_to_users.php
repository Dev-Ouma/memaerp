<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cache invalidation for resolved authorization scopes, enforced by the database.
 *
 * The resolver caches "which scopes does this user hold this permission over?" — an expensive
 * question asked on nearly every request. Invalidating that cache from Eloquent observers is not
 * enough: a mass `update()`, a raw SQL statement, a psql session or a data migration all change
 * grants without firing a single model event, and the user keeps their old access until the TTL
 * lapses. Revocation that can be bypassed by using a different tool is not revocation.
 *
 * So the counter lives on the user row and is bumped by triggers. Every read path already loads
 * the user, so including `access_version` in the cache key costs nothing, and ANY change to a
 * grant — through any client — orphans the old entries atomically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iam.users', function ($table): void {
            $table->unsignedBigInteger('access_version')->default(0);
        });

        // A grant changed for one user.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION iam.bump_access_version()
            RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                IF TG_OP = 'DELETE' THEN
                    UPDATE iam.users SET access_version = access_version + 1
                    WHERE id = OLD.user_id;
                    RETURN OLD;
                END IF;

                UPDATE iam.users SET access_version = access_version + 1
                WHERE id = NEW.user_id
                   OR (TG_OP = 'UPDATE' AND id = OLD.user_id);
                RETURN NEW;
            END; $$;

            CREATE TRIGGER role_assignments_bump_access_version
            AFTER INSERT OR UPDATE OR DELETE ON iam.role_assignments
            FOR EACH ROW EXECUTE FUNCTION iam.bump_access_version();
        ");

        // A role's permission set changed: everyone holding that role is affected.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION iam.bump_access_version_for_role()
            RETURNS trigger LANGUAGE plpgsql AS $$
            DECLARE
                affected_role uuid := COALESCE(NEW.role_id, OLD.role_id);
            BEGIN
                UPDATE iam.users SET access_version = access_version + 1
                WHERE id IN (
                    SELECT user_id FROM iam.role_assignments WHERE role_id = affected_role
                );
                RETURN COALESCE(NEW, OLD);
            END; $$;

            CREATE TRIGGER permission_role_bump_access_version
            AFTER INSERT OR UPDATE OR DELETE ON iam.permission_role
            FOR EACH ROW EXECUTE FUNCTION iam.bump_access_version_for_role();
        ");
    }

    public function down(): void
    {
        DB::unprepared('
            DROP TRIGGER IF EXISTS permission_role_bump_access_version ON iam.permission_role;
            DROP TRIGGER IF EXISTS role_assignments_bump_access_version ON iam.role_assignments;
            DROP FUNCTION IF EXISTS iam.bump_access_version_for_role();
            DROP FUNCTION IF EXISTS iam.bump_access_version();
        ');

        Schema::table('iam.users', function ($table): void {
            $table->dropColumn('access_version');
        });
    }
};
