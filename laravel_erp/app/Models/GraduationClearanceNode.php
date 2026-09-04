<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['node_name', 'check_type', 'assigned_role', 'requires_approval', 'status'])]
final class GraduationClearanceNode extends Model
{
    protected $table = 'graduation_clearance_nodes';
}
