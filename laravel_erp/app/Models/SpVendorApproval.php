<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ref', 'name', 'kra_pin', 'compliance_doc', 'status'])]
final class SpVendorApproval extends Model
{
    protected $table = 'sp_vendor_approvals';
}
