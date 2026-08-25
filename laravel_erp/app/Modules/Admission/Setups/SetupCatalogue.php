<?php

declare(strict_types=1);

namespace App\Modules\Admission\Setups;

final class SetupCatalogue
{
    /** @return array<string, array{category:string,name:string,consumer:string,missing:string}> */
    public static function definitions(): array
    {
        $fail = 'Block the affected operation and report CONFIGURATION_MISSING.';

        return [
            'academic.structures' => ['category' => 'Academic structures', 'name' => 'Academic structures', 'consumer' => 'Programme catalogue and organisational scoping', 'missing' => $fail],
            'academic.faculties_departments' => ['category' => 'Academic structures', 'name' => 'Faculties, schools and departments', 'consumer' => 'Programme ownership and review routing', 'missing' => $fail],
            'academic.programmes' => ['category' => 'Academic structures', 'name' => 'Programmes', 'consumer' => 'Programme discovery', 'missing' => $fail],
            'academic.programme_offerings' => ['category' => 'Academic structures', 'name' => 'Programme offerings', 'consumer' => 'Programme selection and applications', 'missing' => $fail],
            'academic.campuses_study_modes' => ['category' => 'Academic structures', 'name' => 'Campuses and study modes', 'consumer' => 'Programme discovery and placement', 'missing' => $fail],
            'intake.cohorts' => ['category' => 'Intakes and cohorts', 'name' => 'Intakes and cohorts', 'consumer' => 'Application periods and cohort assignment', 'missing' => $fail],
            'intake.application_periods' => ['category' => 'Intakes and cohorts', 'name' => 'Application opening and closing dates', 'consumer' => 'Application creation and submission', 'missing' => $fail],
            'form.sections_fields' => ['category' => 'Application design', 'name' => 'Application form sections and fields', 'consumer' => 'Draft validation and completion', 'missing' => $fail],
            'reference.countries_nationalities_counties' => ['category' => 'Reference data', 'name' => 'Countries, nationalities and Kenyan counties', 'consumer' => 'Applicant profile', 'missing' => $fail],
            'reference.gender_demographics' => ['category' => 'Reference data', 'name' => 'Gender and demographic values', 'consumer' => 'Applicant profile and analytics', 'missing' => $fail],
            'reference.acquisition_sources' => ['category' => 'Reference data', 'name' => 'Referral and acquisition sources', 'consumer' => 'Registration and analytics', 'missing' => $fail],
            'reference.education_qualifications' => ['category' => 'Reference data', 'name' => 'Education and qualification types', 'consumer' => 'Education history', 'missing' => $fail],
            'reference.grading_systems' => ['category' => 'Reference data', 'name' => 'Grading systems', 'consumer' => 'Eligibility and scoring', 'missing' => $fail],
            'documents.types_requirements' => ['category' => 'Documents', 'name' => 'Document types and requirements', 'consumer' => 'Upload and submission gates', 'missing' => $fail],
            'eligibility.rules' => ['category' => 'Review and decisions', 'name' => 'Eligibility rules', 'consumer' => 'Eligibility evaluation', 'missing' => $fail],
            'review.scoring_rubrics' => ['category' => 'Review and decisions', 'name' => 'Scoring criteria and rubrics', 'consumer' => 'Academic reviews', 'missing' => $fail],
            'review.checklists' => ['category' => 'Review and decisions', 'name' => 'Review checklists', 'consumer' => 'Operational and document reviews', 'missing' => $fail],
            'review.routing_assignments' => ['category' => 'Review and decisions', 'name' => 'Review routing and assignments', 'consumer' => 'Work queues', 'missing' => $fail],
            'approval.workflows' => ['category' => 'Review and decisions', 'name' => 'Approval workflows', 'consumer' => 'Multi-stage approvals', 'missing' => $fail],
            'workflow.application_statuses' => ['category' => 'Workflow', 'name' => 'Application statuses and transitions', 'consumer' => 'Application state machine', 'missing' => $fail],
            'payment.application_fee' => ['category' => 'Payments', 'name' => 'Application-fee rules', 'consumer' => 'Payment initiation and submission gate', 'missing' => $fail],
            'payment.channels_providers' => ['category' => 'Payments', 'name' => 'Payment channels and providers', 'consumer' => 'Payment initiation', 'missing' => $fail],
            'payment.statuses' => ['category' => 'Payments', 'name' => 'Payment statuses', 'consumer' => 'Payment state machine', 'missing' => $fail],
            'payment.receipt_numbering' => ['category' => 'Payments', 'name' => 'Receipt-numbering formats', 'consumer' => 'Payment receipts', 'missing' => $fail],
            'payment.exceptions' => ['category' => 'Payments', 'name' => 'Waivers, reversals and reconciliation', 'consumer' => 'Finance exceptions', 'missing' => $fail],
            'capacity.waitlist' => ['category' => 'Offers and capacity', 'name' => 'Programme capacity and waitlist rules', 'consumer' => 'Shortlisting and waitlists', 'missing' => $fail],
            'offer.conditions' => ['category' => 'Offers and capacity', 'name' => 'Admission conditions', 'consumer' => 'Offer decisions', 'missing' => $fail],
            'letter.templates' => ['category' => 'Letters and verification', 'name' => 'Admission-letter templates', 'consumer' => 'Admission letters', 'missing' => $fail],
            'letter.cohort_content' => ['category' => 'Letters and verification', 'name' => 'Cohort-specific letter content', 'consumer' => 'Admission letters', 'missing' => $fail],
            'institution.branding' => ['category' => 'Letters and verification', 'name' => 'Institutional branding and logo', 'consumer' => 'Letters, receipts and reports', 'missing' => $fail],
            'institution.signatories' => ['category' => 'Letters and verification', 'name' => 'Authorised signatories', 'consumer' => 'Admission letters and rolls', 'missing' => $fail],
            'verification.qr' => ['category' => 'Letters and verification', 'name' => 'QR-code verification settings', 'consumer' => 'Public document verification', 'missing' => $fail],
            'communication.templates' => ['category' => 'Communications', 'name' => 'Communication templates', 'consumer' => 'Queued notifications', 'missing' => $fail],
            'communication.delivery_rules' => ['category' => 'Communications', 'name' => 'Email and SMS rules', 'consumer' => 'Notification delivery', 'missing' => $fail],
            'communication.reminders' => ['category' => 'Communications', 'name' => 'Reminder schedules', 'consumer' => 'Draft and offer reminders', 'missing' => $fail],
            'sla.calendars_escalations' => ['category' => 'Operations', 'name' => 'SLA calendars and escalation rules', 'consumer' => 'Queues and escalations', 'missing' => $fail],
            'security.roles_permissions' => ['category' => 'Security and governance', 'name' => 'Roles and permissions', 'consumer' => 'Authorisation', 'missing' => $fail],
            'analytics.metric_definitions' => ['category' => 'Analytics and reports', 'name' => 'Dashboard metric definitions', 'consumer' => 'Dashboard calculations', 'missing' => $fail],
            'analytics.dimensions_thresholds' => ['category' => 'Analytics and reports', 'name' => 'Analytics dimensions and thresholds', 'consumer' => 'Analytics filtering and alerts', 'missing' => $fail],
            'reports.definitions' => ['category' => 'Analytics and reports', 'name' => 'Report definitions', 'consumer' => 'Report centre', 'missing' => $fail],
            'reports.export_permissions' => ['category' => 'Analytics and reports', 'name' => 'Export formats and permissions', 'consumer' => 'Export jobs', 'missing' => $fail],
            'governance.consent_policies' => ['category' => 'Security and governance', 'name' => 'Consent and policy versions', 'consumer' => 'Registration and declarations', 'missing' => $fail],
            'governance.retention' => ['category' => 'Security and governance', 'name' => 'Data-retention rules', 'consumer' => 'Retention jobs', 'missing' => $fail],
            'integration.external_services' => ['category' => 'Integrations', 'name' => 'External integrations', 'consumer' => 'Payment, messaging and SIS adapters', 'missing' => $fail],
            'conversion.enrolment_gates' => ['category' => 'Student conversion', 'name' => 'Enrolment gates', 'consumer' => 'Student conversion', 'missing' => $fail],
            'conversion.student_numbering' => ['category' => 'Student conversion', 'name' => 'Student-numbering formats', 'consumer' => 'Student conversion', 'missing' => $fail],
            'conversion.rules' => ['category' => 'Student conversion', 'name' => 'Student-conversion rules', 'consumer' => 'Student conversion', 'missing' => $fail],
        ];
    }
}
