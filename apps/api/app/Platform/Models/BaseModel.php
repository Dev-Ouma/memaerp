<?php

declare(strict_types=1);

namespace App\Platform\Models;

use App\Platform\Concerns\HasUuid7;
use Illuminate\Database\Eloquent\Model;

/**
 * Every domain model extends this. It fixes two things globally so that no individual model has
 * to remember them: time-ordered UUID keys, and an allow-list mass-assignment posture.
 *
 * Deliberately NOT `$guarded = []`. Every model declares an explicit `$fillable`. A deny-list is
 * one forgotten column away from letting a request body set `is_approved`.
 */
abstract class BaseModel extends Model
{
    use HasUuid7;

    protected $guarded = ['id'];
}
