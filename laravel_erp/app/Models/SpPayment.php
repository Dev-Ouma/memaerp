<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ref', 'vendor', 'amount', 'date', 'mode', 'status'])]
final class SpPayment extends Model
{
    protected $table = 'sp_payments';
}
