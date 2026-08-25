<?php

declare(strict_types=1);

namespace App\Modules\Platform\Storage;

/**
 * Cheap, dependency-free content checks that run before anything is written to disk.
 *
 * This is not a substitute for a malware scanner — it is the layer that catches the common cases a
 * scanner should never have to see: a `.pdf` that is really HTML, a polyglot image with a script
 * payload, or a PDF carrying an embedded file or auto-executing JavaScript. Checking declared type
 * against actual bytes matters because the browser, not the extension, decides how to interpret a file.
 */
final class ContentInspector
{
    private const MAGIC = [
        'application/pdf' => ['%PDF-'],
        'image/jpeg' => ["\xFF\xD8\xFF"],
        'image/png' => ["\x89PNG\r\n\x1a\n"],
        'image/webp' => ['RIFF'],
    ];

    /** Byte sequences that must never appear at the head of an uploaded document. */
    private const FORBIDDEN_PREFIXES = [
        '<!DOCTYPE', '<html', '<?php', '<script', '#!', 'MZ', "\x7fELF", "PK\x03\x04",
    ];

    private const PDF_ACTIVE_CONTENT = ['/JavaScript', '/JS', '/Launch', '/EmbeddedFile', '/OpenAction', '/AA'];

    /**
     * @return list<string> the reasons the file is unacceptable; empty means it passed
     */
    public function reject(string $absolutePath, string $declaredMime): array
    {
        $problems = [];
        $head = (string) file_get_contents($absolutePath, false, null, 0, 4096);

        if ($head === '') {
            return ['The file is empty.'];
        }

        $detected = $this->detectMime($absolutePath);

        if ($detected !== null && $detected !== $declaredMime && ! $this->equivalent($detected, $declaredMime)) {
            $problems[] = 'The file content does not match its declared type.';
        }

        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with(ltrim($head), $prefix)) {
                $problems[] = 'The file contains markup or executable content and cannot be accepted.';
                break;
            }
        }

        if (isset(self::MAGIC[$declaredMime])) {
            $matched = false;
            foreach (self::MAGIC[$declaredMime] as $signature) {
                if (str_starts_with($head, $signature)) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                $problems[] = 'The file does not look like a valid '.$declaredMime.'.';
            }
        }

        if ($declaredMime === 'application/pdf' && $this->pdfCarriesActiveContent($absolutePath)) {
            $problems[] = 'The PDF contains embedded files or active content, which is not permitted.';
        }

        return array_values(array_unique($problems));
    }

    public function detectMime(string $absolutePath): ?string
    {
        if (! function_exists('finfo_open')) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return null;
        }

        $mime = finfo_file($finfo, $absolutePath);
        finfo_close($finfo);

        return $mime === false ? null : $mime;
    }

    private function equivalent(string $detected, string $declared): bool
    {
        // finfo reports some JPEG variants and WebP containers under near-synonyms.
        $synonyms = [
            'image/jpeg' => ['image/jpg', 'image/pjpeg'],
            'image/webp' => ['image/webp', 'application/octet-stream'],
        ];

        return in_array($detected, $synonyms[$declared] ?? [], true);
    }

    private function pdfCarriesActiveContent(string $absolutePath): bool
    {
        $handle = fopen($absolutePath, 'rb');

        if ($handle === false) {
            return false;
        }

        $carryOver = '';

        try {
            while (! feof($handle)) {
                $chunk = $carryOver.(string) fread($handle, 262144);

                foreach (self::PDF_ACTIVE_CONTENT as $marker) {
                    if (str_contains($chunk, $marker)) {
                        return true;
                    }
                }

                // Keep a tail so a marker split across two reads is still found.
                $carryOver = substr($chunk, -32);
            }
        } finally {
            fclose($handle);
        }

        return false;
    }
}
