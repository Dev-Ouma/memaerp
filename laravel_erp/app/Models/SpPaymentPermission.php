<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['staff_name', 'department', 'limit_amount', 'compliance', 'status'])]
final class SpPaymentPermission extends Model
{
    protected $table = 'sp_payment_permissions';
}
