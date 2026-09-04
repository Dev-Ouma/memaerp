<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Legacy generic operational rows table. Desks now persist to dedicated domain
 * tables; this model remains only for historical rows until a data migration drops them.
 */
#[Fillable([
    'module', 'kind', 'code', 'status', 'title', 'party_name', 'party_ref', 'programme',
    'department', 'amount', 'amount_secondary', 'quantity', 'starts_on', 'ends_on',
    'occurred_on', 'fields', 'created_by',
])]
final class ModuleRecord extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'amount' => 'decimal:2',
            'amount_secondary' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'occurred_on' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return array<string, mixed> */
    public function toViewRow(): array
    {
        $row = is_array($this->fields) ? $this->fields : [];
        $row['id'] = $this->id;
        $row['code'] = $row['code'] ?? $this->code;
        $row['status'] = $row['status'] ?? $this->status;
        $row['title'] = $row['title'] ?? $this->title;
        $row['name'] = $row['name'] ?? $this->party_name ?? $this->title;
        if ($this->party_name) {
            $row['party_name'] = $row['party_name'] ?? $this->party_name;
            $row['student_name'] = $row['student_name'] ?? $this->party_name;
            $row['applicant_name'] = $row['applicant_name'] ?? $this->party_name;
            $row['staff_name'] = $row['staff_name'] ?? $this->party_name;
            $row['full_name'] = $row['full_name'] ?? $this->party_name;
        }
        if ($this->party_ref) {
            $row['party_ref'] = $row['party_ref'] ?? $this->party_ref;
            $row['reg_no'] = $row['reg_no'] ?? $this->party_ref;
            $row['admission_number'] = $row['admission_number'] ?? $this->party_ref;
        }
        if ($this->programme) {
            $row['programme'] = $row['programme'] ?? $this->programme;
        }
        if ($this->department) {
            $row['department'] = $row['department'] ?? $this->department;
        }
        if ($this->amount !== null && empty($row['amount']) && empty($row['trimester_revenue'])) {
            $row['amount'] = 'KES '.number_format((float) $this->amount, 0);
        }

        return $row;
    }
}
