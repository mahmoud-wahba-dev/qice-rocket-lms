<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerificationEmailCode extends Notification
{
    private $notifiable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notifiable)
    {
        $this->notifiable = $notifiable;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $generalSettings = getGeneralSettings();
        $subject = trans('auth.email_confirmation');

        $confirm = [
            'title' => $subject . ' ' . trans('auth.in') . ' ' . $generalSettings['site_name'],
            'message' => trans('auth.email_confirmation_template_body', ['email' => $notifiable->email, 'site' => $generalSettings['site_name']]),
            'code' => $notifiable->code
        ];

        // Hostinger SMTP requires From === authenticated mailbox (MAIL_USERNAME).
        // Do not use Admin "site_email" here — a mismatch causes 535 / silent send failure.
        [$fromAddress, $fromName] = $this->resolveSmtpFrom($generalSettings);

        return (new MailMessage)
            ->subject($subject)
            ->from($fromAddress, $fromName)
            ->view('design_1.web.emails.confirmCode', [
                'confirm' => $confirm,
                'generalSettings' => $generalSettings
            ]);
    }

    /**
     * @param  array<string, mixed>  $generalSettings
     * @return array{0: string, 1: string}
     */
    private function resolveSmtpFrom(array $generalSettings): array
    {
        $smtpUser = (string) (config('mail.mailers.smtp.username') ?: env('MAIL_USERNAME') ?: '');
        $fromAddress = (string) (config('mail.from.address') ?: env('MAIL_FROM_ADDRESS') ?: '');
        $fromName = (string) (config('mail.from.name') ?: env('MAIL_FROM_NAME') ?: ($generalSettings['site_name'] ?? 'QIEC Training'));
        $siteEmail = trim((string) ($generalSettings['site_email'] ?? ''));

        // Prefer env From; allow site_email only when it matches the SMTP mailbox.
        if ($fromAddress === '' && $siteEmail !== '') {
            $fromAddress = $siteEmail;
        }

        if ($smtpUser !== '' && $fromAddress !== '' && strcasecmp($fromAddress, $smtpUser) !== 0) {
            if ($siteEmail !== '' && strcasecmp($siteEmail, $smtpUser) === 0) {
                $fromAddress = $siteEmail;
            } else {
                $fromAddress = $smtpUser;
            }
        }

        if ($fromAddress === '' && $smtpUser !== '') {
            $fromAddress = $smtpUser;
        }

        return [$fromAddress, $fromName];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
