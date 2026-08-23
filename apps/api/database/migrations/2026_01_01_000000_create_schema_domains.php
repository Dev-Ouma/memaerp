<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ADR-003 — one PostgreSQL database, one schema per bounded context.
 *
 * Schemas are the database-level expression of the module boundaries enforced in PHP by Deptrac.
 * A module owns its schema; no module writes to another module's tables.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $schemas = [
        'iam',
        'institution',
        'curriculum',
        'course',
        'admission',
        'student',
        'enrollment',
        'finance',
        'examination',
        'graduation',
        'hr',
        'procurement',
        'research',
        'audit',
        'cms',
        'analytics',
        'lms',
        'attendance',
        'advising',
        'attachment',
    ];

    public function up(): void
    {
        foreach ($this->schemas as $schema) {
            DB::statement("CREATE SCHEMA IF NOT EXISTS {$schema}");
        }

        // UUID v7 support. pgcrypto gives us gen_random_uuid (v4); v7 is generated in PHP
        // so that identifiers are time-ordered for index locality (see PLAN/03-DATA-ARCHITECTURE.md).
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        // btree_gist is required for the room double-booking exclusion constraint.
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
        // Trigram indexes back the name-search paths used across registry screens.
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    }

    public function down(): void
    {
        foreach (array_reverse($this->schemas) as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS {$schema} CASCADE");
        }
    }
};
