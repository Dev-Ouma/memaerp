<?php

declare(strict_types=1);

namespace App\Modules\Platform\Numbering;

use Illuminate\Support\Facades\DB;

/**
 * Atomic, collision-safe generation of the human-readable identifiers the institution actually uses.
 *
 * Internal keys are UUIDs; these numbers exist for humans, letters and receipts. Generation takes a row
 * lock on the sequence, so two concurrent submissions can never be handed the same number even if they
 * are served by different workers.
 */
final class NumberGenerator
{
    /**
     * @param  string  $scopeKey  the sequence to draw from, e.g. `application_number:SEP2026`
     * @param  string  $pattern  a template containing `{seq}`, e.g. `MC/APL/SEP2026/{seq}`
     */
    public function next(string $scopeKey, string $pattern, int $padLength = 6): string
    {
        return DB::transaction(function () use ($scopeKey, $pattern, $padLength): string {
            $row = DB::table('number_sequences')->where('scope_key', $scopeKey)->lockForUpdate()->first();

            if ($row === null) {
                // insertOrIgnore keeps a concurrent creator from failing on the unique key; the row is
                // then re-read under the lock so both callers agree on the current value.
                DB::table('number_sequences')->insertOrIgnore([
                    'scope_key' => $scopeKey,
                    'pattern' => $pattern,
                    'next_value' => 1,
                    'pad_length' => $padLength,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $row = DB::table('number_sequences')->where('scope_key', $scopeKey)->lockForUpdate()->first();
            }

            $value = (int) $row->next_value;
            DB::table('number_sequences')->where('id', $row->id)->update([
                'next_value' => $value + 1,
                'updated_at' => now(),
            ]);

            return str_replace(
                '{seq}',
                str_pad((string) $value, (int) $row->pad_length, '0', STR_PAD_LEFT),
                $pattern,
            );
        });
    }

    public function applicantNumber(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return $this->next("applicant_number:{$year}", "MC/APP/{$year}/{seq}");
    }

    public function applicationNumber(string $intakeToken): string
    {
        return $this->next("application_number:{$intakeToken}", "MC/APL/{$intakeToken}/{seq}");
    }

    public function receiptNumber(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return $this->next("receipt_number:{$year}", "MC/RCP/{$year}/{seq}");
    }

    public function submissionReceiptNumber(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return $this->next("submission_receipt:{$year}", "MC/SUB/{$year}/{seq}");
    }

    public function offerReference(string $intakeToken): string
    {
        return $this->next("offer_reference:{$intakeToken}", "MC/ADM/{$intakeToken}/{seq}");
    }

    public function admissionRollReference(string $intakeToken): string
    {
        return $this->next("admission_roll:{$intakeToken}", "MC/ROLL/{$intakeToken}/{seq}", 3);
    }

    public function studentNumber(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return $this->next("student_number:{$year}", "MC/STD/{$year}/{seq}", 5);
    }

    public function decisionBatchReference(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return $this->next("decision_batch:{$year}", "MC/DEC/{$year}/{seq}", 4);
    }
}
