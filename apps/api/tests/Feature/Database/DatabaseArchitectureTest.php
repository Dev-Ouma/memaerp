<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Platform\Support\Uuid7;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DatabaseArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_bounded_context_schemas_and_foundation_tables_exist(): void
    {
        $schemas = DB::table('information_schema.schemata')
            ->whereIn('schema_name', $this->expectedSchemas())
            ->pluck('schema_name')
            ->all();

        $this->assertEqualsCanonicalizing($this->expectedSchemas(), $schemas);

        foreach ([
            'audit.activity_log',
            'documents.files',
            'documents.file_versions',
            'finance.ledger_accounts',
            'finance.journals',
            'finance.journal_entries',
            'finance.student_ledger_entries',
            'course.rooms',
            'course.teaching_slots',
            'iam.personal_access_tokens',
        ] as $qualifiedTable) {
            [$schema, $table] = explode('.', $qualifiedTable, 2);

            $this->assertTrue(
                DB::table('information_schema.tables')
                    ->where('table_schema', $schema)
                    ->where('table_name', $table)
                    ->exists(),
                "Expected table {$qualifiedTable} to exist.",
            );
        }
    }

    public function test_every_governed_table_has_actor_timestamps(): void
    {
        $missing = DB::select(<<<'SQL'
            SELECT tables.table_schema, tables.table_name
            FROM information_schema.tables tables
            WHERE tables.table_type = 'BASE TABLE'
              AND tables.table_schema = ANY (?)
              AND NOT (tables.table_schema = 'audit' AND tables.table_name LIKE 'activity_log%')
              AND EXISTS (
                  SELECT 1
                  FROM unnest(ARRAY['created_by', 'updated_by', 'created_at', 'updated_at']) required(column_name)
                  WHERE NOT EXISTS (
                      SELECT 1
                      FROM information_schema.columns columns
                      WHERE columns.table_schema = tables.table_schema
                        AND columns.table_name = tables.table_name
                        AND columns.column_name = required.column_name
                  )
              )
            ORDER BY tables.table_schema, tables.table_name
        SQL, ['{'.implode(',', $this->expectedSchemas()).'}']);

        $this->assertSame([], $missing, 'Every governed table must carry actor and timestamp metadata.');
    }

    public function test_audit_rows_cannot_be_updated(): void
    {
        $institutionId = $this->createInstitution();
        $auditId = Uuid7::generate();

        DB::table('audit.activity_log')->insert([
            'id' => $auditId,
            'institution_id' => $institutionId,
            'event' => 'database.architecture.tested',
            'auditable_type' => 'event',
            'module' => 'platform',
        ]);

        try {
            DB::table('audit.activity_log')
                ->where('id', $auditId)
                ->update(['reason' => 'tampered']);
            $this->fail('Updating an audit record should be rejected by PostgreSQL.');
        } catch (QueryException $exception) {
            $this->assertSame('42501', $exception->errorInfo[0]);
        }
    }

    public function test_audit_rows_cannot_be_deleted(): void
    {
        $institutionId = $this->createInstitution();
        $auditId = Uuid7::generate();

        DB::table('audit.activity_log')->insert([
            'id' => $auditId,
            'institution_id' => $institutionId,
            'event' => 'database.architecture.tested',
            'auditable_type' => 'event',
            'module' => 'platform',
        ]);

        $this->expectException(QueryException::class);
        DB::table('audit.activity_log')->where('id', $auditId)->delete();
    }

    public function test_posted_journal_must_balance_at_transaction_commit(): void
    {
        $institutionId = $this->createInstitution();
        $accountId = Uuid7::generate();
        $journalId = Uuid7::generate();

        DB::table('finance.ledger_accounts')->insert([
            'id' => $accountId,
            'institution_id' => $institutionId,
            'code' => '1100',
            'name' => 'Student Receivables',
            'account_type' => 'ASSET',
        ]);

        DB::table('finance.journals')->insert([
            'id' => $journalId,
            'institution_id' => $institutionId,
            'journal_number' => 'TEST-UNBALANCED-001',
            'source_type' => 'TEST',
            'description' => 'Unbalanced journal must fail',
            'effective_on' => now()->toDateString(),
        ]);

        $this->expectException(QueryException::class);

        DB::transaction(function () use ($accountId, $institutionId, $journalId): void {
            DB::table('finance.journal_entries')->insert([
                'id' => Uuid7::generate(),
                'institution_id' => $institutionId,
                'journal_id' => $journalId,
                'account_id' => $accountId,
                'debit' => 100,
                'credit' => 0,
            ]);

            DB::table('finance.journals')->where('id', $journalId)->update([
                'status' => 'POSTED',
                'posted_at' => now(),
            ]);

            // RefreshDatabase keeps the test inside an outer transaction. Force the deferred
            // constraint at this savepoint so the test observes the same validation that occurs
            // at a real top-level transaction commit.
            DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
        });
    }

    public function test_posted_entries_are_append_only(): void
    {
        $institutionId = $this->createInstitution();
        $debitAccountId = $this->createAccount($institutionId, '1100', 'Receivables', 'ASSET');
        $creditAccountId = $this->createAccount($institutionId, '4100', 'Fee Revenue', 'REVENUE');
        $journalId = Uuid7::generate();
        $debitEntryId = Uuid7::generate();

        DB::transaction(function () use (
            $creditAccountId,
            $debitAccountId,
            $debitEntryId,
            $institutionId,
            $journalId,
        ): void {
            DB::table('finance.journals')->insert([
                'id' => $journalId,
                'institution_id' => $institutionId,
                'journal_number' => 'TEST-BALANCED-001',
                'source_type' => 'TEST',
                'description' => 'Balanced posting',
                'effective_on' => now()->toDateString(),
            ]);
            DB::table('finance.journal_entries')->insert([
                [
                    'id' => $debitEntryId,
                    'institution_id' => $institutionId,
                    'journal_id' => $journalId,
                    'account_id' => $debitAccountId,
                    'debit' => 100,
                    'credit' => 0,
                ],
                [
                    'id' => Uuid7::generate(),
                    'institution_id' => $institutionId,
                    'journal_id' => $journalId,
                    'account_id' => $creditAccountId,
                    'debit' => 0,
                    'credit' => 100,
                ],
            ]);
            DB::table('finance.journals')->where('id', $journalId)->update([
                'status' => 'POSTED',
                'posted_at' => now(),
            ]);
        });

        $this->expectException(QueryException::class);
        DB::table('finance.journal_entries')->where('id', $debitEntryId)->update(['debit' => 90]);
    }

    /** @return list<string> */
    private function expectedSchemas(): array
    {
        return [
            'iam', 'institution', 'curriculum', 'course', 'admission', 'student',
            'enrollment', 'finance', 'examination', 'graduation', 'hr',
            'procurement', 'research', 'audit', 'cms', 'analytics', 'documents',
        ];
    }

    private function createInstitution(): string
    {
        $institutionId = Uuid7::generate();

        DB::table('institution.institutions')->insert([
            'id' => $institutionId,
            'code' => 'TEST-'.substr($institutionId, -8),
            'name' => 'Database Architecture Test University',
            'legal_name' => 'Database Architecture Test University',
        ]);

        return $institutionId;
    }

    private function createAccount(
        string $institutionId,
        string $code,
        string $name,
        string $type,
    ): string {
        $accountId = Uuid7::generate();

        DB::table('finance.ledger_accounts')->insert([
            'id' => $accountId,
            'institution_id' => $institutionId,
            'code' => $code,
            'name' => $name,
            'account_type' => $type,
        ]);

        return $accountId;
    }
}
