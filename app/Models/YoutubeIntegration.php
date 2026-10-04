<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class YoutubeIntegration extends Model
{
    protected $table = 'youtube_integrations';

    protected $guarded = ['id'];

    protected $hidden = [
        'refresh_token',
        'access_token',
    ];

    public static function current(): ?self
    {
        return static::query()->orderByDesc('id')->first();
    }

    public function isConnected(): bool
    {
        return !empty($this->getRefreshTokenPlain())
            && !empty($this->channel_id);
    }

    public function getRefreshTokenPlain(): ?string
    {
        if (empty($this->refresh_token)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->refresh_token);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setRefreshTokenPlain(string $token): void
    {
        $this->refresh_token = Crypt::encryptString($token);
    }

    public function getAccessTokenPlain(): ?string
    {
        if (empty($this->access_token)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->access_token);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setAccessTokenPlain(?string $token, ?int $expiresAt = null): void
    {
        $this->access_token = $token ? Crypt::encryptString($token) : null;
        $this->access_token_expires_at = $expiresAt;
    }

    public function accessTokenIsValid(): bool
    {
        $token = $this->getAccessTokenPlain();
        if (!$token || empty($this->access_token_expires_at)) {
            return false;
        }

        return (int) $this->access_token_expires_at > (time() + 60);
    }
}
