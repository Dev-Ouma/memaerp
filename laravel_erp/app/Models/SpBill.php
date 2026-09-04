<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ref', 'vendor', 'amount', 'due_date', 'status'])]
final class SpBill extends Model
{
    protected $table = 'sp_bills';
}
