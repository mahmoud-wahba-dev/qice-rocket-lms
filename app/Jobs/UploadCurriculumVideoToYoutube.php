<?php

namespace App\Jobs;

use App\Models\File;
use App\Services\Youtube\YoutubeUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadCurriculumVideoToYoutube implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 7200;

    public function __construct(public int $fileId)
    {
    }

    public function handle(YoutubeUploader $uploader): void
    {
        $file = File::find($this->fileId);
        if (!$file) {
            return;
        }

        if ($file->storage === 'youtube' && empty($file->processing_status)) {
            return;
        }

        $relative = (string) ($file->file ?? '');
        // Stored as youtube-temp/... on local disk during processing
        $disk = Storage::disk(config('youtube.temp_disk', 'local'));
        $absolute = $disk->path($relative);

        if (!is_file($absolute)) {
            // Fallback: public store path leftover
            $publicCandidate = public_path(ltrim($relative, '/'));
            if (is_file($publicCandidate)) {
                $absolute = $publicCandidate;
            } else {
                $file->processing_status = 'failed';
                $file->processing_error = 'الملف المؤقت غير موجود.';
                $file->status = File::$Inactive;
                $file->updated_at = time();
                $file->save();

                return;
            }
        }

        try {
            $title = method_exists($file, 'getTitleAttribute') ? (string) $file->title : 'Lesson';
            $videoId = $uploader->uploadUnlisted($absolute, $title);

            // Delete temp
            if ($disk->exists($relative)) {
                $disk->delete($relative);
            } elseif (is_file($absolute) && str_contains($absolute, 'youtube-temp')) {
                @unlink($absolute);
            }

            $file->storage = 'youtube';
            $file->file = 'https://www.youtube.com/watch?v=' . $videoId;
            $file->volume = '';
            $file->file_type = 'video';
            $file->downloadable = 0;
            $file->status = File::$Active;
            $file->processing_status = null;
            $file->processing_error = null;
            $file->updated_at = time();
            $file->save();
        } catch (\Throwable $e) {
            Log::error('UploadCurriculumVideoToYoutube failed', [
                'file_id' => $this->fileId,
                'message' => $e->getMessage(),
            ]);

            $file->processing_status = 'failed';
            $file->processing_error = mb_substr($e->getMessage(), 0, 1000);
            $file->status = File::$Inactive;
            $file->updated_at = time();
            $file->save();

            throw $e;
        }
    }
}
