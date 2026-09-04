<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['staff_name', 'department', 'limit_amount', 'policy_level', 'last_audited', 'status'])]
final class SpInvoicePermission extends Model
{
    protected $table = 'sp_invoice_permissions';
}
