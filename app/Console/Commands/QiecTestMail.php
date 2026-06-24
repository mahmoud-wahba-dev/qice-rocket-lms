<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class QiecTestMail extends Command
{
    protected $signature = 'qiec:test-mail {email? : Recipient address (defaults to MAIL_USERNAME)}';

    protected $description = 'Send a test email to verify SMTP configuration';

    public function handle(): int
    {
        $to = $this->argument('email') ?: env('MAIL_USERNAME') ?: env('MAIL_FROM_ADDRESS');

        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('Provide a valid email or set MAIL_USERNAME / MAIL_FROM_ADDRESS in .env');

            return self::FAILURE;
        }

        $mailer = env('MAIL_MAILER', config('mail.default'));
        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn('MAIL_MAILER is "' . $mailer . '" — emails are not sent over SMTP.');
            $this->line('Set MAIL_MAILER=smtp and configure MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD in .env');
        }

        if (empty(env('MAIL_PASSWORD'))) {
            $this->error('MAIL_PASSWORD is empty in .env — add your Hostinger email password first.');

            return self::FAILURE;
        }

        $this->info('Sending test email to ' . $to . ' via ' . config('mail.default') . '...');

        try {
            Mail::raw(
                'QIEC mail test — if you received this, SMTP is working.',
                function ($message) use ($to) {
                    $message->to($to)
                        ->subject('QIEC SMTP test — ' . now()->format('Y-m-d H:i'));
                }
            );

            $this->info('Test email sent successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
