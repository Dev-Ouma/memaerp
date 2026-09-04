<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'title', 'course_id', 'cohort', 'tuition_amount', 'admin_amount', 'currency', 'status',
])]
final class FeeStructure extends Model
{
    protected function casts(): array
    {
        return [
            'tuition_amount' => 'decimal:2',
            'admin_amount' => 'decimal:2',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function totalAmount(): float
    {
        return (float) $this->tuition_amount + (float) $this->admin_amount;
    }
}
