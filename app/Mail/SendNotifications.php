<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNotifications extends Mailable
{
    use SerializesModels;

    public $notification;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $notification = $this->notification;

        if (!empty($notification)) {
            $generalSettings = getGeneralSettings();

            // From must match Hostinger SMTP mailbox (MAIL_FROM_ADDRESS / MAIL_USERNAME).
            $fromAddress = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS');
            $fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME') ?: ($generalSettings['site_name'] ?? 'QIEC Training');
            $smtpUser = config('mail.mailers.smtp.username') ?: env('MAIL_USERNAME');
            if (!empty($smtpUser) && !empty($fromAddress) && strcasecmp((string) $fromAddress, (string) $smtpUser) !== 0) {
                $fromAddress = $smtpUser;
            }

            return $this->subject($notification['title'])
                ->from($fromAddress, $fromName)
                ->cc(!empty($notification['cc']) ? $notification['cc'] : [])
                ->view('design_1.web.emails.notification', [
                    'notification' => $notification,
                    'generalSettings' => $generalSettings
                ]);
        }
    }
}
