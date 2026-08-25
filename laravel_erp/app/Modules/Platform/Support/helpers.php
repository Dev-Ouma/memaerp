<?php

declare(strict_types=1);

use Illuminate\Support\Str;

if (! function_exists('correlation_id')) {
    /**
     * The identifier that ties an HTTP request, its audit events, its outbox events and its log lines
     * together. Set by the CorrelationId middleware; generated lazily for console and queue work.
     */
    function correlation_id(): string
    {
        if (! app()->bound('platform.correlation_id')) {
            app()->instance('platform.correlation_id', (string) Str::uuid());
        }

        return app('platform.correlation_id');
    }
}

if (! function_exists('set_correlation_id')) {
    function set_correlation_id(string $id): string
    {
        app()->instance('platform.correlation_id', $id);

        return $id;
    }
}
