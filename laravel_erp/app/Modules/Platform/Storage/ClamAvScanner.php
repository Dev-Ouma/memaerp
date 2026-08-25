<?php

declare(strict_types=1);

namespace App\Modules\Platform\Storage;

use Illuminate\Support\Facades\Log;

/**
 * Talks to clamd over its INSTREAM protocol.
 *
 * INSTREAM is used rather than SCAN because the daemon frequently runs in a different container from
 * the application and cannot see its filesystem. A daemon that is unreachable yields `ERROR`, never
 * `CLEAN` — an outage must not turn into an implicit approval.
 */
final class ClamAvScanner implements MalwareScanner
{
    private const CHUNK = 8192;

    public function __construct(
        private readonly string $socket,
        private readonly int $timeout = 30,
    ) {}

    public function scan(string $absolutePath): ScanResult
    {
        $errorNumber = 0;
        $errorMessage = '';
        $connection = @stream_socket_client($this->socket, $errorNumber, $errorMessage, $this->timeout);

        if ($connection === false) {
            Log::warning('Malware scanner unreachable.', ['socket' => $this->socket, 'error' => $errorMessage]);

            return ScanResult::error('The malware scanner could not be reached.');
        }

        $file = fopen($absolutePath, 'rb');

        if ($file === false) {
            fclose($connection);

            return ScanResult::error('The uploaded file could not be read for scanning.');
        }

        try {
            stream_set_timeout($connection, $this->timeout);
            fwrite($connection, "zINSTREAM\0");

            while (! feof($file)) {
                $chunk = (string) fread($file, self::CHUNK);

                if ($chunk === '') {
                    continue;
                }

                fwrite($connection, pack('N', strlen($chunk)).$chunk);
            }

            fwrite($connection, pack('N', 0));
            $response = trim((string) stream_get_contents($connection));
        } finally {
            fclose($file);
            fclose($connection);
        }

        if ($response === '') {
            return ScanResult::error('The malware scanner returned no result.');
        }

        if (str_contains($response, 'OK') && ! str_contains($response, 'FOUND')) {
            return ScanResult::clean();
        }

        if (str_contains($response, 'FOUND')) {
            preg_match('/stream: (.+) FOUND/', $response, $matches);

            return ScanResult::infected($matches[1] ?? 'unknown');
        }

        return ScanResult::error('The malware scanner reported an unexpected result.');
    }

    public function name(): string
    {
        return 'clamav';
    }
}
