<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['provider_code', 'name', 'group', 'contact', 'outstanding_bills', 'status'])]
final class SpProvider extends Model
{
    protected $table = 'sp_providers';
}
