<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'type', 'rate', 'status'])]
final class SpTax extends Model
{
    protected $table = 'sp_taxes';
}
