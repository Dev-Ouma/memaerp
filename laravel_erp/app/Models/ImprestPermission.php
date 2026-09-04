<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['role_title', 'authority_level', 'min_limit', 'max_limit', 'allowed_categories', 'mandate_rule', 'status'])]
final class ImprestPermission extends Model
{
    protected $table = 'imprest_permissions';
}
