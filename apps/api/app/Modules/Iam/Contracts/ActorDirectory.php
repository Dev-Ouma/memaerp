<?php

declare(strict_types=1);

namespace App\Modules\Iam\Contracts;

/**
 * Looks up an actor by id outside a request — a queued job or a console command continuing work
 * that began as somebody's click.
 */
interface ActorDirectory
{
    /**
     * Soft-deleted accounts are included on purpose.
     *
     * A job may outlive the account that queued it, and a departed employee's actions still have
     * to be attributed to them. Returning null there would silently rewrite the actor as "nobody".
     */
    public function find(string $id): ?Actor;
}
