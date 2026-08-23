<?php

declare(strict_types=1);

namespace App\Modules\Admission\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApplicationPayment extends BaseModel
{
    use Auditable;

    protected $table = 'admission.application_payments';

    protected $fillable = [
        'institution_id', 'application_id', 'channel', 'transaction_reference', 'payer_phone',
        'amount', 'currency', 'status', 'receipt_number', 'paid_at', 'gateway_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
            'gateway_payload' => 'array',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
