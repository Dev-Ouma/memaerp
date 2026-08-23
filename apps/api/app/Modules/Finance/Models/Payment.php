<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Person;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Payment extends BaseModel
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'finance.payments';

    protected $fillable = [
        'institution_id',
        'invoice_id',
        'person_id',
        'receipt_number',
        'payment_method',
        'transaction_reference',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** @return BelongsTo<Person, $this> */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
