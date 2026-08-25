export const applicationStatuses = {
    LEAD: ['Prospect', 'Your interest has been recorded.', 'neutral', 2],
    ACCOUNT_CREATED: ['Account created', 'Verify your email to continue.', 'info', 5],
    EMAIL_VERIFIED: ['Email verified', 'Your account is ready.', 'success', 8],
    DRAFT: ['Application in progress', 'Complete the remaining sections.', 'warning', 18],
    VALIDATION_FAILED: ['Action required', 'Some information needs correction.', 'danger', 20],
    SUBMITTED: ['Application submitted', 'Your application is safely received.', 'info', 34],
    UNDER_REVIEW: ['Under review', 'The admissions team is reviewing your evidence.', 'info', 48],
    INFO_REQUESTED: ['Information requested', 'Please respond to the admissions team.', 'warning', 50],
    VERIFIED: ['Documents verified', 'Your evidence has passed verification.', 'success', 58],
    SHORTLISTED: ['Shortlisted', 'Your application is progressing to approval.', 'success', 68],
    APPROVAL_PENDING: ['Approval pending', 'A final admissions decision is being prepared.', 'warning', 74],
    ADMITTED_CONDITIONAL: ['Conditional offer', 'Review and meet the offer conditions.', 'success', 84],
    ADMITTED: ['Offer issued', 'Review your admission offer.', 'success', 88],
    WAITLISTED: ['Waitlisted', 'We will notify you when a place becomes available.', 'warning', 72],
    ACCEPTED: ['Offer accepted', 'Complete your enrolment preparation.', 'success', 94],
    READY_TO_ENROL: ['Ready to enrol', 'Your onboarding checklist is ready.', 'success', 98],
    ENROLLED: ['Enrolled', 'Welcome to Mema College.', 'success', 100],
    REJECTED: ['Application unsuccessful', 'Contact support if you need clarification.', 'danger', 100],
    DEFER_REQUESTED: ['Deferral requested', 'Your request is awaiting review.', 'warning', 90],
    DEFERRED: ['Offer deferred', 'Your place has moved to a future intake.', 'info', 92],
    WITHDRAWN: ['Application withdrawn', 'No further action will be taken.', 'neutral', 100],
    REVOKED: ['Offer revoked', 'Contact admissions for details.', 'danger', 100],
    CLOSED: ['Application closed', 'This application cycle is complete.', 'neutral', 100],
} as const;

export const paymentStatuses = {
    NOT_STARTED: ['Not paid', 'Start the mandatory KES 1,000 payment.', 'neutral'],
    INITIATED: ['Payment initiated', 'Complete the payment with your provider.', 'info'],
    PENDING: ['Confirmation pending', 'We are waiting for the provider callback.', 'warning'],
    PAID: ['Payment confirmed', 'The application fee has been received.', 'success'],
    FAILED: ['Payment failed', 'No charge was confirmed. You may retry.', 'danger'],
    CANCELLED: ['Payment cancelled', 'Start a new payment when ready.', 'neutral'],
    EXPIRED: ['Payment expired', 'The payment request expired. Please retry.', 'warning'],
    REVERSED: ['Payment reversed', 'Contact Finance before submitting.', 'danger'],
    REFUNDED: ['Payment refunded', 'A new confirmed payment is required.', 'danger'],
    WAIVED: ['Payment waived', 'Finance has authorised a fee waiver.', 'success'],
} as const;

export type ApplicationStatus = keyof typeof applicationStatuses;
export type PaymentStatus = keyof typeof paymentStatuses;
