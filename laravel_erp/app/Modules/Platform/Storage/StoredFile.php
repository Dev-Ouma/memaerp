<?php

declare(strict_types=1);

namespace App\Modules\Platform\Storage;

final class StoredFile
{
    public function __construct(
        public readonly string $disk,
        public readonly string $path,
        public readonly string $originalName,
        public readonly string $mimeType,
        public readonly int $sizeBytes,
        public readonly string $sha256,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'storage_disk' => $this->disk,
            'storage_path' => $this->path,
            'original_name' => $this->originalName,
            'mime_type' => $this->mimeType,
            'size_bytes' => $this->sizeBytes,
            'sha256' => $this->sha256,
        ];
    }
}
