<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * Applicant documents. Deliberately NOT the `public` disk and never symlinked into
         * public/: certificates and identity documents are served only through an authenticated,
         * permission-checked, audited controller. `visibility` is private and `serve` is off so
         * nothing in the framework can hand out a direct URL by accident.
         */
        'documents' => [
            'driver' => env('DOCUMENT_DISK_DRIVER', 'local'),
            'root' => env('DOCUMENT_DISK_ROOT', storage_path('app/private/documents')),
            'visibility' => 'private',
            'serve' => false,
            'throw' => true,
            'report' => false,
            'bucket' => env('DOCUMENT_S3_BUCKET'),
            'key' => env('DOCUMENT_S3_KEY'),
            'secret' => env('DOCUMENT_S3_SECRET'),
            'region' => env('DOCUMENT_S3_REGION'),
            'endpoint' => env('DOCUMENT_S3_ENDPOINT'),
            'use_path_style_endpoint' => env('DOCUMENT_S3_PATH_STYLE', false),
        ],

        /*
         * Generated admission letters, receipts and export files. Same rules as `documents`.
         */
        'generated' => [
            'driver' => env('GENERATED_DISK_DRIVER', 'local'),
            'root' => env('GENERATED_DISK_ROOT', storage_path('app/private/generated')),
            'visibility' => 'private',
            'serve' => false,
            'throw' => true,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
