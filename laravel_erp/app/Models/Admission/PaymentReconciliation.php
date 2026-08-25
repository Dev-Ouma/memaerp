<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentReconciliation extends Model
{
    use HasUuids;

    protected $table = 'payment_reconciliations';

    /** Mass-assignment allow-list — services write validated arrays only. */
    protected $fillable = [
        'institution_id',
        'provider',
        'statement_reference',
        'period_start',
        'period_end',
        'run_by',
        'run_at',
        'matched_count',
        'unmatched_count',
        'exception_count',
        'provider_total',
        'ledger_total',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'run_at' => 'datetime',
            'provider_total' => 'decimal:2',
            'ledger_total' => 'decimal:2',
        ];
    }
}
