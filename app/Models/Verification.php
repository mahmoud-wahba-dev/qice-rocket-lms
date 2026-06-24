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
            try {
                $this->notify(new SendVerificationEmailCode($this));
            } catch (\Throwable $e) {
                \Log::error('Verification email failed: ' . $e->getMessage(), [
                    'email' => $this->email,
                ]);
            }
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
