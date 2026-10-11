<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Отдаёт файлы публичного диска, хранящиеся в базе (см. DatabaseFilesystemAdapter).
 * Отдаются только картинки — остальное считается отсутствующим, чтобы через
 * этот маршрут нельзя было раздать HTML или скрипт со своего домена.
 */
class MediaController extends Controller
{
    private const ALLOWED_TYPES = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/webp',
        'image/avif',
        'image/bmp',
    ];

    public function show(string $path): Response
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $file = DB::table('stored_files')->where('path', $path)->first(['contents', 'mime_type', 'size', 'updated_at']);

        if (!$file || !in_array($file->mime_type, self::ALLOWED_TYPES, true)) {
            abort(404);
        }

        $etag = '"' . md5($path . '|' . $file->size . '|' . $file->updated_at) . '"';

        if (request()->headers->get('If-None-Match') === $etag) {
            return response('', 304)->setEtag($etag, false);
        }

        return response(base64_decode($file->contents, true) ?: '', 200, [
            'Content-Type' => $file->mime_type,
            'Content-Length' => (string) $file->size,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'ETag' => $etag,
        ]);
    }
}
