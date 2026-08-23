<?php

declare(strict_types=1);

namespace App\Modules\Audit\Contracts;

/**
 * The audit event taxonomy.
 *
 * A closed vocabulary rather than free-form strings: an auditor filtering for failed sign-ins
 * should not have to guess whether this system wrote 'login-failed', 'login_failed' or
 * 'LOGIN_FAILURE'. Add cases here, never at the call site.
 */
interface AuditEvent
{
    public const string CREATED = 'created';

    public const string UPDATED = 'updated';

    public const string DELETED = 'deleted';

    public const string VIEWED = 'viewed';

    public const string EXPORTED = 'exported';

    public const string LOGIN = 'login';

    public const string LOGIN_FAILED = 'login-failed';

    public const string LOGOUT = 'logout';

    public const string PERMISSION_DENIED = 'permission-denied';

    public const string IMPERSONATION_STARTED = 'impersonation-started';

    public const string IMPERSONATION_ENDED = 'impersonation-ended';
}
