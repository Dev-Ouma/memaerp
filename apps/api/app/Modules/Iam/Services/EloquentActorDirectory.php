<?php

declare(strict_types=1);

namespace App\Modules\Iam\Services;

use App\Modules\Iam\Contracts\Actor;
use App\Modules\Iam\Contracts\ActorDirectory;
use App\Modules\Iam\Models\User;

/**
 * The IAM module's implementation of actor lookup for out-of-request work.
 */
final class EloquentActorDirectory implements ActorDirectory
{
    public function find(string $id): ?Actor
    {
        return User::query()->withTrashed()->find($id);
    }
}
