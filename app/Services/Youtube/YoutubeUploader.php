<?php

namespace App\Services\Youtube;

use App\Models\YoutubeIntegration;
use Illuminate\Support\Facades\Http;

class YoutubeUploader
{
    public function __construct(private YoutubeOAuthService $oauth)
    {
    }

    /**
     * Upload a local video file as Unlisted and return the YouTube video id.
     */
    public function uploadUnlisted(string $absolutePath, string $title, ?string $description = null): string
    {
        $integration = YoutubeIntegration::current();
        if (!$integration || !$integration->isConnected()) {
            throw new \RuntimeException('حساب بث الفيديو غير مربوط من إعدادات النظام.');
        }

        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            throw new \RuntimeException('ملف الفيديو المؤقت غير موجود.');
        }

        $accessToken = $this->oauth->refreshAccessToken($integration);
        $size = filesize($absolutePath);
        $mime = mime_content_type($absolutePath) ?: 'video/mp4';

        $init = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json; charset=UTF-8',
                'X-Upload-Content-Length' => (string) $size,
                'X-Upload-Content-Type' => $mime,
            ])
            ->post(
                'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status',
                [
                    'snippet' => [
                        'title' => mb_substr($title !== '' ? $title : 'Lesson video', 0, 100),
                        'description' => (string) ($description ?? ''),
                        'categoryId' => '27',
                    ],
                    'status' => [
                        'privacyStatus' => config('youtube.privacy_status', 'unlisted'),
                        'selfDeclaredMadeForKids' => false,
                    ],
                ]
            );

        if (!$init->successful()) {
            throw new \RuntimeException('تعذر بدء رفع الفيديو: ' . ($init->json('error.message') ?: $init->body()));
        }

        $uploadUrl = $init->header('Location');
        if (empty($uploadUrl)) {
            throw new \RuntimeException('لم يُرجع مزود الفيديو رابط الرفع المجزأ.');
        }

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('تعذر فتح ملف الفيديو للرفع.');
        }

        try {
            $chunkSize = 8 * 1024 * 1024; // 8MB chunks
            $offset = 0;

            while ($offset < $size) {
                $length = (int) min($chunkSize, $size - $offset);
                $data = fread($handle, $length);
                if ($data === false || $data === '') {
                    throw new \RuntimeException('تعذر قراءة جزء من ملف الفيديو.');
                }

                $end = $offset + strlen($data) - 1;
                $response = Http::withBody($data, $mime)
                    ->withHeaders([
                        'Content-Length' => (string) strlen($data),
                        'Content-Range' => "bytes {$offset}-{$end}/{$size}",
                    ])
                    ->put($uploadUrl);

                $status = $response->status();

                // 308 Resume Incomplete — continue
                if ($status === 308) {
                    $range = $response->header('Range');
                    if ($range && preg_match('/bytes=0-(\d+)/', $range, $m)) {
                        $offset = ((int) $m[1]) + 1;
                    } else {
                        $offset = $end + 1;
                    }
                    continue;
                }

                if ($status >= 200 && $status < 300) {
                    $videoId = (string) ($response->json('id') ?? '');
                    if ($videoId === '') {
                        throw new \RuntimeException('اكتمل الرفع دون معرّف فيديو.');
                    }

                    return $videoId;
                }

                throw new \RuntimeException('فشل رفع جزء من الفيديو (' . $status . '): ' . ($response->json('error.message') ?: $response->body()));
            }
        } finally {
            fclose($handle);
        }

        throw new \RuntimeException('انتهى الرفع دون استجابة نجاح.');
    }
}
