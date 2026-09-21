<?php

namespace App\Services\Maintenance;

final class WebsiteCleanupResult
{
    public function __construct(
        public int $filesScanned = 0,
        public int $filesRemoved = 0,
        public int $bytesFreed = 0,
        public int $temporaryRemoved = 0,
        public int $orphansRemoved = 0,
        public bool $cacheCleared = false,
        public bool $failed = false,
        public ?string $error = null,
    ) {}

    public function isClean(): bool
    {
        return $this->filesRemoved === 0 && ! $this->failed;
    }

    public function storageFreedLabel(): string
    {
        $bytes = max(0, $this->bytesFreed);

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        $mb = $bytes / (1024 * 1024);

        if ($mb >= 10) {
            return round($mb).' MB';
        }

        return round($mb, 1).' MB';
    }

    public function notificationTitle(): string
    {
        if ($this->failed) {
            return 'Clean & Optimize failed';
        }

        if ($this->isClean()) {
            return 'Website is already clean. No unnecessary files were removed.';
        }

        return 'Clean & Optimize completed';
    }

    public function notificationBody(): string
    {
        $lines = [
            'Files scanned: '.$this->filesScanned,
            'Files removed: '.$this->filesRemoved,
            'Storage freed: '.$this->storageFreedLabel(),
            'Temporary files removed: '.$this->temporaryRemoved,
            'Orphan files removed: '.$this->orphansRemoved,
            'Safe cache cleared: '.($this->cacheCleared ? 'Yes' : 'No'),
        ];

        if ($this->error !== null && $this->error !== '') {
            $lines[] = 'Error: '.$this->error;
        }

        return implode("\n", $lines);
    }
}
