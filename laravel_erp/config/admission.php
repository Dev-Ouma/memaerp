<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Admission module configuration
|--------------------------------------------------------------------------
|
| Every value that an operator may legitimately need to change lives here and
| is read from the environment. No credential has a working default: an unset
| provider secret makes that provider unavailable rather than silently
| insecure.
|
*/

return [

    'institution_code' => env('INSTITUTION_CODE', 'MEMA'),

    /*
    | The mandatory application fee. The amount is authoritative on the server;
    | a client-supplied amount is never trusted, and the live figure is read
    | from the `payment_fee_setups` table so a change is versioned and audited.
    | This value seeds that table and is the fallback if no setup is active.
    */
    'fee' => [
        'amount' => env('APPLICATION_FEE_AMOUNT', '1000.00'),
        'currency' => env('APPLICATION_FEE_CURRENCY', 'KES'),
        'code' => 'APPLICATION_FEE',
        'refundable' => false,
    ],

    'documents' => [
        'disk' => env('DOCUMENT_DISK', 'documents'),
        'generated_disk' => env('GENERATED_DISK', 'generated'),
        'max_bytes' => (int) env('DOCUMENT_MAX_BYTES', 10 * 1024 * 1024),
        'allowed_mime_types' => [
            'application/pdf' => ['pdf'],
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
        ],
        // Downloads are refused unless the file has a clean scan result. Relaxed
        // outside production so a developer without clamd can still work.
        'require_clean_scan' => filter_var(
            env('DOCUMENT_REQUIRE_CLEAN_SCAN', env('APP_ENV', 'production') === 'production'),
            FILTER_VALIDATE_BOOL,
        ),
        'download_url_ttl_minutes' => (int) env('DOCUMENT_URL_TTL_MINUTES', 10),
    ],

    'scanner' => [
        'driver' => env('MALWARE_SCANNER', 'null'), // null | clamav
        'clamav' => [
            'socket' => env('CLAMAV_SOCKET', 'tcp://127.0.0.1:3310'),
            'timeout' => (int) env('CLAMAV_TIMEOUT', 30),
        ],
    ],

    'identity' => [
        // Deterministic blind index over identity numbers, so duplicates can be
        // detected without the plaintext ever being queryable. Keep this key out
        // of the application key's blast radius: rotating one must not silently
        // invalidate the other.
        'index_key' => env('IDENTITY_INDEX_KEY'),
    ],

    'submission' => [
        'required_completion_percent' => (int) env('SUBMISSION_MIN_COMPLETION', 100),
        'lock_after_submission' => true,
    ],

    'offers' => [
        'default_acceptance_days' => (int) env('OFFER_ACCEPTANCE_DAYS', 21),
        'verification_base_url' => env('OFFER_VERIFICATION_URL', env('APP_URL', 'http://localhost').'/verify/admission'),
        'qr_token_ttl_days' => (int) env('QR_TOKEN_TTL_DAYS', 3650),
    ],

    'payments' => [
        'default_provider' => env('PAYMENT_DEFAULT_PROVIDER', 'mpesa_stk'),
        'callback_tolerance_seconds' => (int) env('PAYMENT_CALLBACK_TOLERANCE', 300),
        'providers' => [
            'mpesa_stk' => [
                'enabled' => filter_var(env('MPESA_ENABLED', false), FILTER_VALIDATE_BOOL),
                'base_url' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
                'consumer_key' => env('MPESA_CONSUMER_KEY'),
                'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
                'shortcode' => env('MPESA_SHORTCODE'),
                'passkey' => env('MPESA_PASSKEY'),
                'callback_url' => env('MPESA_CALLBACK_URL'),
                'callback_secret' => env('MPESA_CALLBACK_SECRET'),
                'allowed_ips' => array_filter(explode(',', (string) env('MPESA_ALLOWED_IPS', ''))),
            ],
            'mpesa_c2b' => [
                'enabled' => filter_var(env('MPESA_C2B_ENABLED', false), FILTER_VALIDATE_BOOL),
                'shortcode' => env('MPESA_C2B_SHORTCODE'),
                'callback_secret' => env('MPESA_C2B_CALLBACK_SECRET'),
                'allowed_ips' => array_filter(explode(',', (string) env('MPESA_C2B_ALLOWED_IPS', ''))),
            ],
            'card' => [
                'enabled' => filter_var(env('CARD_ENABLED', false), FILTER_VALIDATE_BOOL),
                'base_url' => env('CARD_BASE_URL'),
                'public_key' => env('CARD_PUBLIC_KEY'),
                'secret_key' => env('CARD_SECRET_KEY'),
                'callback_secret' => env('CARD_CALLBACK_SECRET'),
            ],
            'bank_transfer' => [
                'enabled' => filter_var(env('BANK_TRANSFER_ENABLED', true), FILTER_VALIDATE_BOOL),
                'account_name' => env('BANK_ACCOUNT_NAME', 'Mema College'),
                'account_number' => env('BANK_ACCOUNT_NUMBER'),
                'bank_name' => env('BANK_NAME'),
                'branch' => env('BANK_BRANCH'),
            ],
            'cashier' => [
                'enabled' => filter_var(env('CASHIER_ENABLED', true), FILTER_VALIDATE_BOOL),
            ],
        ],
    ],

    'notifications' => [
        'sms_enabled' => filter_var(env('SMS_ENABLED', false), FILTER_VALIDATE_BOOL),
        'sms_sender_id' => env('SMS_SENDER_ID', 'MEMA'),
        'from_email' => env('MAIL_FROM_ADDRESS', 'admissions@mema.ac.ke'),
        'support_email' => env('ADMISSIONS_SUPPORT_EMAIL', 'admissions@mema.ac.ke'),
        'support_phone' => env('ADMISSIONS_SUPPORT_PHONE', '+254700000000'),
    ],

    'retention' => [
        'unsubmitted_draft_days' => (int) env('RETENTION_DRAFT_DAYS', 365),
        'rejected_application_years' => (int) env('RETENTION_REJECTED_YEARS', 3),
        'admitted_application_years' => (int) env('RETENTION_ADMITTED_YEARS', 7),
        'audit_years' => (int) env('RETENTION_AUDIT_YEARS', 7),
    ],

    'reports' => [
        // Suppress cells below this count in aggregate exports so a "1 applicant
        // with a declared disability in Programme X" row cannot re-identify them.
        'small_cell_threshold' => (int) env('REPORT_SMALL_CELL_THRESHOLD', 5),
        'max_export_rows' => (int) env('REPORT_MAX_EXPORT_ROWS', 100000),
    ],

    'verification' => [
        'email_token_ttl_minutes' => (int) env('EMAIL_TOKEN_TTL_MINUTES', 60 * 24),
        'password_reset_ttl_minutes' => (int) env('PASSWORD_RESET_TTL_MINUTES', 60),
        'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_minutes' => (int) env('LOGIN_LOCKOUT_MINUTES', 15),
    ],

];
