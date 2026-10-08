<?php

namespace App\Services\GoogleDrive;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleDriveClient
{
    private ?Drive $drive = null;

    public function isConfigured(): bool
    {
        $folder = $this->folderId();
        $creds = $this->credentialsPath();

        return $folder !== '' && $creds !== null && is_readable($creds);
    }

    public function folderId(): string
    {
        $stored = $this->storedConfig();
        if (!empty($stored['folder_id'])) {
            return trim((string) $stored['folder_id']);
        }

        return trim((string) config('google_drive.folder_id'));
    }

    public function folderUrl(): string
    {
        $id = $this->folderId();
        if ($id === '') {
            return 'https://drive.google.com/drive/my-drive';
        }

        return 'https://drive.google.com/drive/folders/' . rawurlencode($id);
    }

    public function credentialsPath(): ?string
    {
        $default = storage_path('app/google-drive/service-account.json');
        if (is_readable($default)) {
            return $default;
        }

        $path = trim((string) config('google_drive.credentials'));
        if ($path === '') {
            return null;
        }

        if (!str_starts_with($path, DIRECTORY_SEPARATOR) && !preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
            $path = base_path($path);
        }

        return is_readable($path) ? $path : null;
    }

    public function serviceAccountEmail(): ?string
    {
        $path = $this->credentialsPath();
        if (!$path) {
            return null;
        }

        $json = json_decode((string) @file_get_contents($path), true);
        $email = trim((string) ($json['client_email'] ?? ''));

        return $email !== '' ? $email : null;
    }

    /**
     * @return array{folder_id?:string}
     */
    public function storedConfig(): array
    {
        $path = storage_path('app/google-drive/config.json');
        if (!is_readable($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    public function saveFolderId(string $folderIdOrUrl): string
    {
        $id = self::extractFolderId($folderIdOrUrl) ?: self::extractFileId($folderIdOrUrl);
        if (!$id) {
            throw new \InvalidArgumentException('رابط أو معرّف المجلد غير صالح.');
        }

        $dir = storage_path('app/google-drive');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config = $this->storedConfig();
        $config['folder_id'] = $id;
        file_put_contents(
            $dir . DIRECTORY_SEPARATOR . 'config.json',
            json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        Cache::forget('google_drive_sa_access_token');

        return $id;
    }

    public function storeCredentialsUpload(\Illuminate\Http\UploadedFile $file): string
    {
        $raw = file_get_contents($file->getRealPath());
        $json = json_decode((string) $raw, true);
        if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            throw new \InvalidArgumentException('ملف JSON غير صالح — اختر ملف مفتاح حساب الخدمة من Google Cloud.');
        }

        $dir = storage_path('app/google-drive');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . 'service-account.json';
        file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        Cache::forget('google_drive_sa_access_token');
        $this->drive = null;

        return (string) $json['client_email'];
    }

    public static function extractFolderId(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        if (preg_match('~/folders/([A-Za-z0-9_-]{10,})~', $input, $m)) {
            return $m[1];
        }

        if (preg_match('~^[A-Za-z0-9_-]{10,}$~', $input)) {
            return $input;
        }

        return null;
    }

    /**
     * Extract a Drive file id from a pasted URL or raw id.
     */
    public static function extractFileId(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        if (preg_match('~^[A-Za-z0-9_-]{10,}$~', $input)) {
            return $input;
        }

        if (preg_match('~/file/d/([A-Za-z0-9_-]{10,})~', $input, $m)) {
            return $m[1];
        }

        if (preg_match('~[?&]id=([A-Za-z0-9_-]{10,})~', $input, $m)) {
            return $m[1];
        }

        if (preg_match('~/d/([A-Za-z0-9_-]{10,})~', $input, $m)) {
            return $m[1];
        }

        return null;
    }

    public function drive(): Drive
    {
        if ($this->drive) {
            return $this->drive;
        }

        $creds = $this->credentialsPath();
        if (!$creds || !is_readable($creds)) {
            throw new \RuntimeException('ملف اعتماد Google Drive غير موجود.');
        }

        $client = new GoogleClient();
        $client->setAuthConfig($creds);
        $client->setScopes(config('google_drive.scopes', [Drive::DRIVE_READONLY]));
        $client->setAccessType('offline');

        $this->drive = new Drive($client);

        return $this->drive;
    }

    public function accessToken(): string
    {
        return Cache::remember('google_drive_sa_access_token', 3000, function () {
            $client = $this->drive()->getClient();
            $token = $client->fetchAccessTokenWithAssertion();
            if (!empty($token['error'])) {
                throw new \RuntimeException('تعذر الحصول على صلاحية Drive: ' . ($token['error_description'] ?? $token['error']));
            }
            $access = (string) ($token['access_token'] ?? '');
            if ($access === '') {
                throw new \RuntimeException('استجابة Drive فارغة من التوكن.');
            }

            return $access;
        });
    }

    /**
     * @return array{id:string,name:?string,mimeType:?string,size:?string}
     */
    public function getFileMeta(string $fileId): array
    {
        $file = $this->drive()->files->get($fileId, [
            'fields' => 'id,name,mimeType,size,trashed',
            'supportsAllDrives' => true,
        ]);

        if (!empty($file->getTrashed())) {
            throw new \RuntimeException('ملف Drive محذوف.');
        }

        return [
            'id' => (string) $file->getId(),
            'name' => $file->getName(),
            'mimeType' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Stream Drive media to the client (Range-aware) without buffering the whole file in PHP.
     */
    public function streamToResponse(string $fileId, ?string $rangeHeader = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $token = $this->accessToken();
        $url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?alt=media&supportsAllDrives=true';

        $headers = ['Authorization' => 'Bearer ' . $token];
        if ($rangeHeader) {
            $headers['Range'] = $rangeHeader;
        }

        $client = new \GuzzleHttp\Client([
            'http_errors' => false,
            'timeout' => 0,
            'read_timeout' => 0,
            'connect_timeout' => 30,
        ]);

        $upstream = $client->request('GET', $url, [
            'headers' => $headers,
            'stream' => true,
        ]);

        $status = $upstream->getStatusCode();
        if ($status < 200 || $status >= 400) {
            throw new \RuntimeException('فشل جلب الفيديو من Drive (' . $status . ').');
        }

        $passHeaders = [];
        foreach (['Content-Type', 'Content-Length', 'Content-Range', 'Accept-Ranges'] as $h) {
            if ($upstream->hasHeader($h)) {
                $passHeaders[$h] = $upstream->getHeaderLine($h);
            }
        }
        if (empty($passHeaders['Accept-Ranges'])) {
            $passHeaders['Accept-Ranges'] = 'bytes';
        }
        $passHeaders['Cache-Control'] = 'private, no-store';
        $passHeaders['X-Content-Type-Options'] = 'nosniff';

        $body = $upstream->getBody();

        return response()->stream(function () use ($body) {
            while (!$body->eof()) {
                echo $body->read(1024 * 256);
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            }
        }, $status, $passHeaders);
    }
}
