<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Log;

/**
 * Rewrites MP4/MOV moov atom to the front so browsers can seek/start
 * without downloading the whole file (HTTP progressive / Range friendly).
 * No-op when ffmpeg is missing or the file is not a supported container.
 */
class VideoFaststart
{
    public static function optimizePublicStorePath(string $relativeOrUrlPath): bool
    {
        $relative = ltrim((string) $relativeOrUrlPath, '/');
        if (str_starts_with($relative, 'store/')) {
            $relative = substr($relative, strlen('store/'));
        }

        $absolute = public_path('store/' . ltrim($relative, '/'));
        if (!is_file($absolute) || !is_readable($absolute)) {
            return false;
        }

        if (!preg_match('/\.(mp4|m4v|mov)$/i', $absolute)) {
            return false;
        }

        return self::optimizeAbsolutePath($absolute);
    }

    public static function optimizeAbsolutePath(string $absolutePath): bool
    {
        $ffmpeg = self::ffmpegBinary();
        if ($ffmpeg === null) {
            return false;
        }

        $dir = dirname($absolutePath);
        $tmp = $dir . DIRECTORY_SEPARATOR . '.faststart_' . uniqid('', true) . '.mp4';

        $cmd = sprintf(
            '%s -y -i %s -c copy -movflags +faststart %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($absolutePath),
            escapeshellarg($tmp)
        );

        $output = [];
        $code = 0;
        @exec($cmd, $output, $code);

        if ($code !== 0 || !is_file($tmp) || filesize($tmp) < 1) {
            @unlink($tmp);
            Log::debug('VideoFaststart skipped/failed', [
                'path' => $absolutePath,
                'code' => $code,
                'out' => implode("\n", array_slice($output, -5)),
            ]);

            return false;
        }

        // Replace original atomically when possible
        if (!@rename($tmp, $absolutePath)) {
            if (!@copy($tmp, $absolutePath)) {
                @unlink($tmp);

                return false;
            }
            @unlink($tmp);
        }

        return true;
    }

    public static function ffmpegBinary(): ?string
    {
        $configured = trim((string) env('FFMPEG_PATH', ''));
        if ($configured !== '' && is_executable($configured)) {
            return $configured;
        }

        // Common Hostinger / Linux paths + Windows local (where.exe)
        foreach (['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', 'ffmpeg'] as $candidate) {
            if ($candidate === 'ffmpeg') {
                $which = [];
                $code = 1;
                if (DIRECTORY_SEPARATOR === '\\') {
                    @exec('where ffmpeg 2>NUL', $which, $code);
                } else {
                    @exec('command -v ffmpeg 2>/dev/null', $which, $code);
                }
                if ($code === 0 && !empty($which[0])) {
                    $bin = trim($which[0]);
                    if ($bin !== '' && (is_executable($bin) || DIRECTORY_SEPARATOR === '\\')) {
                        return $bin;
                    }
                }
                continue;
            }
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
