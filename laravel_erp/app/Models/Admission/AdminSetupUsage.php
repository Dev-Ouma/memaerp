<?php

declare(strict_types=1);

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class AdminSetupUsage extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['admin_setup_version_id', 'consumer_type', 'consumer_id', 'purpose', 'used_at', 'correlation_id'];
}
