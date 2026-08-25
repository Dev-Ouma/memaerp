<?php

declare(strict_types=1);

namespace App\Modules\Platform\Storage;

use App\Modules\Platform\Api\ApiException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The only sanctioned way an applicant document enters or leaves the system.
 *
 * Files land on a private disk that is never symlinked into `public/`, under a server-generated path —
 * no part of the stored path comes from the uploader, so `../` and null bytes have nothing to act on.
 * The original filename is kept as metadata for display, sanitised, and never used on disk. Retrieval
 * is always a streamed response from an authenticated controller, so every read can be authorised and
 * logged.
 */
final class DocumentStore
{
    public function __construct(private readonly ContentInspector $inspector) {}

    /**
     * @param  string  $prefix  a server-controlled folder, e.g. `applications/{uuid}`
     */
    public function store(UploadedFile $file, string $prefix, ?string $disk = null): StoredFile
    {
        $disk ??= (string) config('admission.documents.disk');

        if (! $file->isValid()) {
            throw ApiException::unprocessable('UPLOAD_FAILED', 'The file did not upload correctly.', [
                'file' => ['The upload was interrupted. Try again.'],
            ]);
        }

        $maxBytes = (int) config('admission.documents.max_bytes');

        if ($file->getSize() > $maxBytes) {
            throw ApiException::unprocessable('FILE_TOO_LARGE', 'The file is larger than the allowed size.', [
                'file' => ['Files must be '.round($maxBytes / 1048576, 1).' MB or smaller.'],
            ]);
        }

        $allowed = (array) config('admission.documents.allowed_mime_types');
        $mime = (string) ($this->inspector->detectMime($file->getRealPath()) ?? $file->getMimeType());

        if (! array_key_exists($mime, $allowed)) {
            throw ApiException::unprocessable('UNSUPPORTED_FILE_TYPE', 'That file type is not accepted.', [
                'file' => ['Upload a PDF, JPG, PNG or WebP file.'],
            ]);
        }

        $problems = $this->inspector->reject($file->getRealPath(), $mime);

        if ($problems !== []) {
            throw ApiException::unprocessable('FILE_REJECTED', 'The file could not be accepted.', ['file' => $problems]);
        }

        $extension = $allowed[$mime][0];
        $path = trim($prefix, '/').'/'.now()->format('Y/m').'/'.Str::uuid().'.'.$extension;

        $handle = fopen($file->getRealPath(), 'rb');

        try {
            Storage::disk($disk)->put($path, $handle, ['visibility' => 'private']);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        return new StoredFile(
            disk: $disk,
            path: $path,
            originalName: $this->sanitiseName($file->getClientOriginalName(), $extension),
            mimeType: $mime,
            sizeBytes: (int) $file->getSize(),
            sha256: (string) hash_file('sha256', $file->getRealPath()),
        );
    }

    /** Writes bytes the system generated itself (letters, receipts, exports). */
    public function putContents(string $contents, string $path, string $mimeType, ?string $disk = null): StoredFile
    {
        $disk ??= (string) config('admission.documents.generated_disk');

        Storage::disk($disk)->put($path, $contents, ['visibility' => 'private']);

        return new StoredFile(
            disk: $disk,
            path: $path,
            originalName: basename($path),
            mimeType: $mimeType,
            sizeBytes: strlen($contents),
            sha256: hash('sha256', $contents),
        );
    }

    public function exists(string $disk, string $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    public function contents(string $disk, string $path): string
    {
        return (string) Storage::disk($disk)->get($path);
    }

    /**
     * Streams a stored file. `Content-Disposition: attachment` and `nosniff` together stop a browser
     * from rendering an uploaded document in the application's own origin.
     */
    public function download(string $disk, string $path, string $downloadName, string $mimeType): StreamedResponse
    {
        if (! Storage::disk($disk)->exists($path)) {
            throw ApiException::notFound('The document');
        }

        return Storage::disk($disk)->download($path, $downloadName, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
            'Content-Disposition' => 'attachment; filename="'.addslashes($downloadName).'"',
        ]);
    }

    /**
     * Physical deletion. Callers must confirm no legal hold applies — that check lives in the retention
     * service, not here, so that this method has exactly one responsibility.
     */
    public function delete(string $disk, string $path): void
    {
        Storage::disk($disk)->delete($path);
    }

    public function absolutePath(string $disk, string $path): ?string
    {
        $adapter = Storage::disk($disk);

        return method_exists($adapter, 'path') ? $adapter->path($path) : null;
    }

    private function sanitiseName(string $name, string $extension): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $clean = preg_replace('/[^A-Za-z0-9 ._-]/', '', $base) ?? '';
        $clean = trim(Str::limit($clean, 80, ''));

        return ($clean === '' ? 'document' : $clean).'.'.$extension;
    }
}
