<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Argon2id is the memory-hard default for this application. It resists the
    | GPU and ASIC attacks that make bcrypt's fixed 4 KB working set a weaker
    | choice for a credential store that will hold tens of thousands of
    | applicant passwords. Existing bcrypt hashes keep verifying, and are
    | rehashed transparently on the owner's next successful login.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER', 'argon2id'),

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => true,
        'limit' => null,
    ],

    /*
    | OWASP's minimum for Argon2id is 19 MiB with two iterations and one degree
    | of parallelism; the defaults below sit at 64 MiB / 3 iterations, which
    | costs roughly 60 ms per verification on the target hardware. Tune with
    | environment variables rather than by editing this file.
    */

    'argon' => [
        'memory' => (int) env('ARGON_MEMORY', 65536),
        'threads' => (int) env('ARGON_THREADS', 1),
        'time' => (int) env('ARGON_TIME', 3),
        'verify' => true,
    ],

    'rehash_on_login' => true,

];
