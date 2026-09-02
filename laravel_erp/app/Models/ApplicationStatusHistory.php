<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['admission_application_id', 'from_status', 'to_status', 'actor_user_id', 'reason_code', 'note', 'created_at'])] final class ApplicationStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'application_status_history';

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }
}
