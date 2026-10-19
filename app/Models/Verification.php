<?php

namespace App\Models;

use App\Notifications\SendVerificationEmailCode;
use App\Notifications\SendVerificationSMSCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Verification extends Model
{
    use Notifiable;

    protected $table = 'verifications';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    const EXPIRE_TIME = 3600; // second => 1 hour

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    protected function shouldSendVerificationEmail(): bool
    {
        if (app()->environment('production')) {
            return true;
        }

        $mailer = env('MAIL_MAILER', config('mail.default'));

        return !empty(env('MAIL_HOST'))
            && !in_array($mailer, ['log', 'array'], true);
    }

    public function sendEmailCode()
    {
        if ($this->shouldSendVerificationEmail()) {
            $mailer = (string) (env('MAIL_MAILER', config('mail.default')) ?: '');
            $host = (string) (env('MAIL_HOST', config('mail.mailers.smtp.host')) ?: '');
            $user = (string) (env('MAIL_USERNAME', config('mail.mailers.smtp.username')) ?: '');
            $password = (string) (env('MAIL_PASSWORD', config('mail.mailers.smtp.password')) ?: '');

            if ($mailer === 'smtp' && (empty($host) || empty($user) || empty($password))) {
                \Log::error('Verification email skipped: incomplete SMTP config', [
                    'email' => $this->email,
                    'mailer' => $mailer,
                    'host_set' => $host !== '',
                    'user_set' => $user !== '',
                    'password_set' => $password !== '',
                ]);
                session()->flash('verification_mail_failed', true);
            } else {
                try {
                    $this->notify(new SendVerificationEmailCode($this));
                    session()->forget('verification_mail_failed');
                    \Log::info('Verification email sent', [
                        'email' => $this->email,
                        'mailer' => $mailer,
                        'host' => $host,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Verification email failed: ' . $e->getMessage(), [
                        'email' => $this->email,
                        'mailer' => $mailer,
                        'host' => $host,
                        'user' => $user,
                    ]);
                    session()->flash('verification_mail_failed', true);
                }
            }
        } else {
            session()->flash('verification_mail_failed', true);
        }

        if (app()->environment('local') && !empty($this->email)) {
            \Log::info('[Verification] Code for ' . $this->email . ': ' . $this->code);
        }

        return true;
    }

    public function sendSMSCode()
    {
        if (app()->environment('production')) {
            $this->notify(new SendVerificationSMSCode($this));
        }
    }
}
