<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

final class PublicStorage
{
    private static bool $checkedLink = false;

    public static function ensureLinked(): void
    {
        if (self::$checkedLink) {
            return;
        }

        self::$checkedLink = true;

        $target = storage_path('app/public');
        $link = public_path('storage');

        if (! is_dir($target) && ! @mkdir($target, 0755, true) && ! is_dir($target)) {
            Log::warning('Unable to create the public storage directory.', [
                'target' => $target,
            ]);

            return;
        }

        if (self::pointsToTarget($link, $target)) {
            return;
        }

        try {
            if (is_link($link)) {
                @unlink($link);
            } elseif (is_dir($link) && self::isEmptyDirectory($link)) {
                @rmdir($link);
            } elseif (file_exists($link)) {
                Log::warning('public/storage exists but does not point to storage/app/public. The /storage fallback route will serve public files.', [
                    'link' => $link,
                    'target' => $target,
                ]);

                return;
            }

            app('files')->link($target, $link);
        } catch (Throwable $exception) {
            Log::warning('Unable to create the public storage symlink. The /storage fallback route will serve public files.', [
                'link' => $link,
                'target' => $target,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^storage/#', '', $path);

        return asset('storage/'.$path);
    }

    private static function pointsToTarget(string $link, string $target): bool
    {
        if (! file_exists($link) && ! is_link($link)) {
            return false;
        }

        $linkRealPath = realpath($link);
        $targetRealPath = realpath($target);

        if ($linkRealPath === false || $targetRealPath === false) {
            return false;
        }

        return self::normalizePath($linkRealPath) === self::normalizePath($targetRealPath);
    }

    private static function isEmptyDirectory(string $path): bool
    {
        $files = scandir($path);

        return $files !== false && count(array_diff($files, ['.', '..'])) === 0;
    }

    private static function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
