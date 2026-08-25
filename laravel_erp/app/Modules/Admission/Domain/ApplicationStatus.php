<?php

declare(strict_types=1);

namespace App\Modules\Admission\Domain;

enum ApplicationStatus: string
{
    case Lead = 'LEAD';
    case AccountCreated = 'ACCOUNT_CREATED';
    case EmailVerified = 'EMAIL_VERIFIED';
    case Draft = 'DRAFT';
    case ValidationFailed = 'VALIDATION_FAILED';
    case Submitted = 'SUBMITTED';
    case UnderReview = 'UNDER_REVIEW';
    case InfoRequested = 'INFO_REQUESTED';
    case Verified = 'VERIFIED';
    case Shortlisted = 'SHORTLISTED';
    case ApprovalPending = 'APPROVAL_PENDING';
    case AdmittedConditional = 'ADMITTED_CONDITIONAL';
    case Admitted = 'ADMITTED';
    case Waitlisted = 'WAITLISTED';
    case Accepted = 'ACCEPTED';
    case ReadyToEnrol = 'READY_TO_ENROL';
    case Enrolled = 'ENROLLED';
    case Rejected = 'REJECTED';
    case DeferRequested = 'DEFER_REQUESTED';
    case Deferred = 'DEFERRED';
    case Withdrawn = 'WITHDRAWN';
    case Revoked = 'REVOKED';
    case Closed = 'CLOSED';
}
