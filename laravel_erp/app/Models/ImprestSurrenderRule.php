<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['policy_code', 'title', 'timeline', 'document_requirements', 'non_compliance_action', 'waiver_authority'])]
final class ImprestSurrenderRule extends Model
{
    protected $table = 'imprest_surrender_rules';
}
