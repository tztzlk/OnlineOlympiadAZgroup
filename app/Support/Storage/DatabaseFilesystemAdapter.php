<?php

namespace App\Support\Storage;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\Visibility;
use Symfony\Component\Mime\MimeTypes;

/**
 * Диск Flysystem поверх таблицы stored_files.
 *
 * Нужен там, где у приложения нет постоянного диска (Vercel): загруженные
 * в админке картинки иначе пропадали бы при перезапуске функции.
 * Каталогов как таковых нет — это префиксы путей.
 */
class DatabaseFilesystemAdapter implements FilesystemAdapter
{
    public function __construct(
        private readonly string $table = 'stored_files',
        private readonly ?string $connection = null,
        private readonly string $url = '/media',
    ) {
    }

    private function query()
    {
        return DB::connection($this->connection)->table($this->table);
    }

    private function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }

    public function getUrl(string $path): string
    {
        $segments = array_map('rawurlencode', explode('/', $this->normalize($path)));

        return rtrim($this->url, '/') . '/' . implode('/', $segments);
    }

    public function fileExists(string $path): bool
    {
        return $this->query()->where('path', $this->normalize($path))->exists();
    }

    public function directoryExists(string $path): bool
    {
        $prefix = rtrim($this->normalize($path), '/') . '/';

        return $this->query()->where('path', 'like', $this->escapeLike($prefix) . '%')->exists();
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $path = $this->normalize($path);
        $now = Carbon::now();

        $this->query()->upsert([[
            'path' => $path,
            'contents' => base64_encode($contents),
            'mime_type' => $config->get('mimetype') ?: $this->guessMimeType($path, $contents),
            'size' => strlen($contents),
            'created_at' => $now,
            'updated_at' => $now,
        ]], ['path'], ['contents', 'mime_type', 'size', 'updated_at']);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, stream_get_contents($contents), $config);
    }

    public function read(string $path): string
    {
        $row = $this->query()->where('path', $this->normalize($path))->first(['contents']);

        if (!$row) {
            throw UnableToReadFile::fromLocation($path, 'File not found');
        }

        return base64_decode($row->contents, true) ?: '';
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        $this->query()->where('path', $this->normalize($path))->delete();
    }

    public function deleteDirectory(string $path): void
    {
        $prefix = rtrim($this->normalize($path), '/') . '/';
        $this->query()->where('path', 'like', $this->escapeLike($prefix) . '%')->delete();
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Каталоги — это префиксы путей, создавать нечего.
    }

    public function setVisibility(string $path, string $visibility): void
    {
        if ($visibility !== Visibility::PUBLIC) {
            throw UnableToSetVisibility::atLocation($path, 'Database disk serves public files only');
        }
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, Visibility::PUBLIC);
    }

    public function mimeType(string $path): FileAttributes
    {
        return $this->attributes($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->attributes($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        return $this->attributes($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = trim($this->normalize($path), '/');
        $prefix = $prefix === '' ? '' : $prefix . '/';
        $directories = [];

        $rows = $this->query()
            ->where('path', 'like', $this->escapeLike($prefix) . '%')
            ->orderBy('path')
            ->get(['path', 'mime_type', 'size', 'updated_at']);

        foreach ($rows as $row) {
            $relative = substr($row->path, strlen($prefix));

            if (!$deep && str_contains($relative, '/')) {
                $directory = $prefix . strtok($relative, '/');
                if (!isset($directories[$directory])) {
                    $directories[$directory] = true;
                    yield new DirectoryAttributes($directory);
                }
                continue;
            }

            yield $this->toAttributes($row);
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $updated = $this->query()
            ->where('path', $this->normalize($source))
            ->update(['path' => $this->normalize($destination), 'updated_at' => Carbon::now()]);

        if ($updated === 0) {
            throw UnableToMoveFile::fromLocationTo($source, $destination);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->write($destination, $this->read($source), $config);
        } catch (UnableToReadFile $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $e);
        }
    }

    private function attributes(string $path): FileAttributes
    {
        $row = $this->query()
            ->where('path', $this->normalize($path))
            ->first(['path', 'mime_type', 'size', 'updated_at']);

        if (!$row) {
            throw UnableToRetrieveMetadata::create($path, 'metadata', 'File not found');
        }

        return $this->toAttributes($row);
    }

    private function toAttributes(object $row): FileAttributes
    {
        return new FileAttributes(
            $row->path,
            (int) $row->size,
            Visibility::PUBLIC,
            $row->updated_at ? Carbon::parse($row->updated_at)->getTimestamp() : null,
            $row->mime_type,
        );
    }

    private function guessMimeType(string $path, string $contents): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->buffer($contents) ?: null;

        if ($detected && $detected !== 'application/octet-stream') {
            return $detected;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? 'application/octet-stream';
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
