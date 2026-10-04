<?php

namespace App\Services\Youtube;

use App\Models\YoutubeIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class YoutubeOAuthService
{
    public function isConfigured(): bool
    {
        return !empty(config('youtube.client_id'))
            && !empty(config('youtube.client_secret'));
    }

    public function redirectUri(): string
    {
        $configured = trim((string) config('youtube.redirect_uri'));
        if ($configured !== '') {
            return $configured;
        }

        return route('panel.v1.admin.system.youtube.callback');
    }

    public function authorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => config('youtube.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', config('youtube.scopes', [])),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $query;
    }

    public function makeState(): string
    {
        return Str::random(40);
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('youtube.client_id'),
            'client_secret' => config('youtube.client_secret'),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('تعذر إكمال ربط الحساب: ' . ($response->json('error_description') ?: $response->body()));
        }

        return $response->json();
    }

    public function refreshAccessToken(YoutubeIntegration $integration): string
    {
        if ($integration->accessTokenIsValid()) {
            return (string) $integration->getAccessTokenPlain();
        }

        $refresh = $integration->getRefreshTokenPlain();
        if (!$refresh) {
            throw new \RuntimeException('حساب الفيديو غير مربوط.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('youtube.client_id'),
            'client_secret' => config('youtube.client_secret'),
            'refresh_token' => $refresh,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('تعذر تجديد صلاحية الرفع: ' . ($response->json('error_description') ?: $response->body()));
        }

        $data = $response->json();
        $access = (string) ($data['access_token'] ?? '');
        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        if ($access === '') {
            throw new \RuntimeException('استجابة غير صالحة من مزود الفيديو.');
        }

        $integration->setAccessTokenPlain($access, time() + $expiresIn);
        if (!empty($data['refresh_token'])) {
            $integration->setRefreshTokenPlain((string) $data['refresh_token']);
        }
        $integration->save();

        return $access;
    }

    public function fetchPrimaryChannel(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet',
                'mine' => 'true',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('تعذر قراءة قناة الفيديو: ' . ($response->json('error.message') ?: $response->body()));
        }

        $item = $response->json('items.0');
        if (empty($item['id'])) {
            throw new \RuntimeException('لا توجد قناة مرتبطة بهذا الحساب.');
        }

        return [
            'channel_id' => (string) $item['id'],
            'channel_title' => (string) ($item['snippet']['title'] ?? 'YouTube Channel'),
        ];
    }

    public function connectFromCode(string $code, int $userId): YoutubeIntegration
    {
        $tokenPayload = $this->exchangeCode($code);
        $refresh = (string) ($tokenPayload['refresh_token'] ?? '');
        $access = (string) ($tokenPayload['access_token'] ?? '');
        $expiresIn = (int) ($tokenPayload['expires_in'] ?? 3600);

        if ($refresh === '') {
            // Re-consent may omit refresh_token if already granted — keep existing if present.
            $existing = YoutubeIntegration::current();
            $refresh = $existing?->getRefreshTokenPlain() ?: '';
        }

        if ($refresh === '' || $access === '') {
            throw new \RuntimeException('لم يُرجع الحساب صلاحية الرفع. أعد الربط بموافقة كاملة (offline access).');
        }

        $channel = $this->fetchPrimaryChannel($access);

        $integration = YoutubeIntegration::current() ?: new YoutubeIntegration();
        $integration->connected_by = $userId;
        $integration->channel_id = $channel['channel_id'];
        $integration->channel_title = $channel['channel_title'];
        $integration->setRefreshTokenPlain($refresh);
        $integration->setAccessTokenPlain($access, time() + $expiresIn);
        $integration->connected_at = time();
        $integration->save();

        return $integration;
    }
}
