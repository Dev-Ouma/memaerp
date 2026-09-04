<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'desc', 'status'])]
final class SpProviderGroup extends Model
{
    protected $table = 'sp_provider_groups';
}
