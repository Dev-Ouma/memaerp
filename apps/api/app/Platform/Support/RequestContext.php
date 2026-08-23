<?php

declare(strict_types=1);

namespace App\Platform\Support;

use App\Modules\Iam\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

/**
 * Ambient facts about the current request or job: who is acting, from where, and under which
 * correlation id.
 *
 * This exists so that the audit recorder does not reach into the request or the auth facade
 * directly. In a queued job there IS no request — the context is populated from the job payload
 * instead, and everything downstream keeps working unchanged.
 *
 * A single mutable singleton per request/job lifecycle. Registered in the container, never
 * instantiated ad hoc.
 */
final class RequestContext
{
    private ?string $correlationId = null;

    private ?string $requestId = null;

    private ?string $institutionId = null;

    private ?User $actor = null;

    private ?string $impersonatedUserId = null;

    private ?string $impersonationSessionId = null;

    private ?string $ipAddress = null;

    private ?string $userAgent = null;

    public function __construct(private readonly AuthFactory $auth) {}

    public function hydrateFromRequest(Request $request): void
    {
        // An inbound correlation id is honoured so that a chain crossing services stays one
        // story; it is validated as a UUID first, because it lands in an indexed column and
        // comes from the client.
        $inbound = $request->header('X-Correlation-Id');

        $this->correlationId = $this->isUuid($inbound) ? $inbound : Uuid7::generate();
        $this->requestId = Uuid7::generate();
        $this->ipAddress = $request->ip();
        $this->userAgent = mb_substr((string) $request->userAgent(), 0, 1024);
    }

    /** For queued jobs and console commands, which continue a story that began in a request. */
    public function hydrateFromJob(?string $correlationId, ?string $institutionId, ?string $actorId): void
    {
        $this->correlationId = $this->isUuid($correlationId) ? $correlationId : Uuid7::generate();
        $this->requestId = Uuid7::generate();
        $this->institutionId = $institutionId;

        if ($actorId !== null) {
            $this->actor = User::query()->withTrashed()->find($actorId);
        }
    }

    public function correlationId(): ?string
    {
        return $this->correlationId;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * The user whose credentials are in play. Under impersonation this is the SUPPORT staff
     * member, not the person being impersonated — the trail must attribute the action to whoever
     * actually performed it.
     */
    public function actor(): ?User
    {
        if ($this->actor !== null) {
            return $this->actor;
        }

        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }

    public function setActor(?User $actor): void
    {
        $this->actor = $actor;
    }

    public function institutionId(): ?string
    {
        return $this->institutionId ?? $this->actor()?->institution_id;
    }

    public function setInstitutionId(?string $institutionId): void
    {
        $this->institutionId = $institutionId;
    }

    public function impersonatedUserId(): ?string
    {
        return $this->impersonatedUserId;
    }

    public function impersonationSessionId(): ?string
    {
        return $this->impersonationSessionId;
    }

    public function beginImpersonation(string $subjectUserId, string $sessionId): void
    {
        $this->impersonatedUserId = $subjectUserId;
        $this->impersonationSessionId = $sessionId;
    }

    public function isImpersonating(): bool
    {
        return $this->impersonationSessionId !== null;
    }

    private function isUuid(?string $value): bool
    {
        return $value !== null
            && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
