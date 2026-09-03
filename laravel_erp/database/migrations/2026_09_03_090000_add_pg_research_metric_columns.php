<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two dashboard figures on the PG Research screens — flagged AI content and
 * faculty attendance at seminars — had no column behind them. Rather than let
 * a screen display a number nothing produces, both are captured at the point
 * the operator already records the underlying event.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pg_plagiarism_scans', function (Blueprint $table): void {
            $table->decimal('ai_index', 5, 2)->nullable()->after('threshold');
            $table->decimal('ai_threshold', 5, 2)->default(20)->after('ai_index');
        });

        Schema::table('pg_seminars', function (Blueprint $table): void {
            $table->unsignedSmallInteger('attendance_count')->nullable()->after('outcome_notes');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pg_plagiarism_scans ADD CONSTRAINT pg_scans_ai_index_range CHECK (ai_index IS NULL OR (ai_index >= 0 AND ai_index <= 100))');
            DB::statement('ALTER TABLE pg_plagiarism_scans ADD CONSTRAINT pg_scans_ai_threshold_range CHECK (ai_threshold >= 0 AND ai_threshold <= 100)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pg_plagiarism_scans DROP CONSTRAINT IF EXISTS pg_scans_ai_index_range');
            DB::statement('ALTER TABLE pg_plagiarism_scans DROP CONSTRAINT IF EXISTS pg_scans_ai_threshold_range');
        }

        Schema::table('pg_plagiarism_scans', function (Blueprint $table): void {
            $table->dropColumn(['ai_index', 'ai_threshold']);
        });

        Schema::table('pg_seminars', function (Blueprint $table): void {
            $table->dropColumn('attendance_count');
        });
    }
};
