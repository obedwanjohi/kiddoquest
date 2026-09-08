<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Turns whatever a content author typed into a media field into something a
 * phone can download.
 *
 * The database holds a mix: absolute Supabase URLs, /storage paths, bare
 * filenames that live in public/images, and the occasional emoji used instead
 * of a picture. Each one has to come out of here as a stable path key plus a
 * URL, or be reported as missing so the studio can fix it.
 */
class MediaResolver
{
    /** Directories under public/ that are searched for a bare filename. */
    protected const SEARCH_DIRS = ['images', 'audio', 'videos', 'img', 'media'];

    protected array $cache = [];

    /**
     * @return array{path:string,url:string,sha256:?string,bytes:int,kind:string}|null
     */
    public function resolve(string $reference, string $kind): ?array
    {
        $reference = trim($reference);

        if ($reference === '' || $this->looksLikeEmoji($reference)) {
            return null;
        }

        if (isset($this->cache[$reference])) {
            return $this->cache[$reference];
        }

        $local = $this->localPathFor($reference);

        if ($local !== null && is_file($local)) {
            $sha = hash_file('sha256', $local);
            $extension = strtolower(pathinfo($local, PATHINFO_EXTENSION)) ?: $this->defaultExtension($kind);

            return $this->cache[$reference] = [
                'path'   => 'media/' . substr($sha, 0, 2) . '/' . $sha . '.' . $extension,
                'url'    => $this->publicUrlForLocal($local),
                'sha256' => $sha,
                'bytes'  => (int) filesize($local),
                'kind'   => $kind,
            ];
        }

        // Remote asset (Supabase, CDN). We cannot hash it without downloading it,
        // so the app verifies the download by size and the pack records the URL.
        if (Str::startsWith($reference, ['http://', 'https://'])) {
            $extension = strtolower(pathinfo(parse_url($reference, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION))
                ?: $this->defaultExtension($kind);

            return $this->cache[$reference] = [
                'path'   => 'media/remote/' . md5($reference) . '.' . $extension,
                'url'    => $reference,
                'sha256' => null,
                'bytes'  => 0,
                'kind'   => $kind,
            ];
        }

        return null;
    }

    /**
     * Where a reference lives on this machine, if anywhere.
     */
    protected function localPathFor(string $reference): ?string
    {
        if (Str::startsWith($reference, ['http://', 'https://'])) {
            $host = parse_url($reference, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if ($host === null || $appHost === null || $host !== $appHost) {
                return null;
            }

            $reference = (string) parse_url($reference, PHP_URL_PATH);
        }

        $reference = ltrim(urldecode($reference), '/');

        if ($reference === '') {
            return null;
        }

        if (Str::startsWith($reference, 'storage/')) {
            $candidate = storage_path('app/public/' . Str::after($reference, 'storage/'));

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $candidate = public_path($reference);

        if (is_file($candidate)) {
            return $candidate;
        }

        // A bare filename: look in the usual places.
        if (! str_contains($reference, '/')) {
            foreach (self::SEARCH_DIRS as $dir) {
                $candidate = public_path($dir . '/' . $reference);

                if (is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    protected function publicUrlForLocal(string $absolutePath): string
    {
        $public = str_replace('\\', '/', public_path());
        $normalized = str_replace('\\', '/', $absolutePath);

        if (Str::startsWith($normalized, $public)) {
            return rtrim((string) config('app.url'), '/') . '/' . ltrim(Str::after($normalized, $public), '/');
        }

        return rtrim((string) config('app.url'), '/') . '/' . ltrim($normalized, '/');
    }

    /**
     * The URL a device should fetch a published pack file from: the CDN when one
     * is configured, otherwise whatever the storage disk exposes.
     */
    public function publicUrl(string $path): string
    {
        $cdn = config('kiddoquest.content.cdn_url');

        if ($cdn) {
            return rtrim($cdn, '/') . '/' . ltrim($path, '/');
        }

        try {
            return Storage::disk(config('kiddoquest.content.disk', 'public'))->url($path);
        } catch (\Throwable) {
            return rtrim((string) config('app.url'), '/') . '/storage/' . ltrim($path, '/');
        }
    }

    /**
     * Several hundred questions use an emoji where an illustration will go later.
     * Those are rendered as text, not downloaded.
     */
    protected function looksLikeEmoji(string $value): bool
    {
        return mb_strlen($value) <= 4 && ! preg_match('/[a-z0-9]/i', $value);
    }

    protected function defaultExtension(string $kind): string
    {
        return match ($kind) {
            'audio' => 'mp3',
            'video' => 'mp4',
            default => 'webp',
        };
    }
}
