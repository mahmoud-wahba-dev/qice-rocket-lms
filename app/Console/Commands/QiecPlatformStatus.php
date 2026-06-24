<?php

namespace App\Console\Commands;

use App\Models\OfflineBank;
use App\Models\PaymentChannel;
use App\Models\Setting;
use Illuminate\Console\Command;

class QiecPlatformStatus extends Command
{
    protected $signature = 'qiec:platform-status
                            {--enable-offline-banks : Enable bank transfer at checkout for testing}';

    protected $description = 'Show mail, verification, and payment gateway status';

    public function handle(): int
    {
        if ($this->option('enable-offline-banks')) {
            $this->enableOfflineBanks();
        }

        $this->line('');
        $this->info('=== QIEC Platform Status ===');
        $this->line('');

        $this->printMailStatus();
        $this->printVerificationStatus();
        $this->printPaymentStatus();

        $this->line('');
        $this->comment('Next: php artisan qiec:test-mail your@email.com');

        return self::SUCCESS;
    }

    private function printMailStatus(): void
    {
        $mailer = env('MAIL_MAILER', config('mail.default'));
        $configured = !empty(env('MAIL_HOST'))
            && !empty(env('MAIL_PASSWORD'))
            && !in_array($mailer, ['log', 'array'], true);

        $this->info('Mail');
        $this->table(
            ['Setting', 'Value'],
            [
                ['MAIL_MAILER', $mailer],
                ['MAIL_HOST', env('MAIL_HOST', '(empty)')],
                ['MAIL_USERNAME', env('MAIL_USERNAME', '(empty)')],
                ['MAIL_PASSWORD', empty(env('MAIL_PASSWORD')) ? '(empty)' : '********'],
                ['MAIL_FROM', env('MAIL_FROM_ADDRESS', '(empty)')],
                ['site_email (admin)', getGeneralSettings('site_email') ?? '(empty)'],
                ['Ready to send', $configured ? 'YES' : 'NO'],
            ]
        );
    }

    private function printVerificationStatus(): void
    {
        $opts = getGeneralOptionsSettings();
        $disabled = !empty($opts['disable_registration_verification_process']);

        $this->info('Registration verification');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Verification required', $disabled ? 'NO (disabled in admin)' : 'YES'],
                ['Resend cooldown (min)', $opts['duration_of_resend_verification_code'] ?? 2],
            ]
        );
    }

    private function printPaymentStatus(): void
    {
        $channels = PaymentChannel::orderBy('status')->orderBy('title')->get();
        $rows = [];

        foreach ($channels as $channel) {
            if ($channel->status !== 'active') {
                continue;
            }

            $creds = (array) ($channel->credentials ?? []);
            $hasCreds = count(array_filter($creds, fn ($v) => $v !== null && $v !== '')) > 0;
            $testMode = !empty($creds['test_mode']) && $creds['test_mode'] !== 'off' ? 'test' : 'live';

            $rows[] = [
                $channel->title,
                $channel->class_name,
                $channel->status,
                $hasCreds ? 'yes' : 'NO',
                json_encode($channel->currencies ?? []),
                $hasCreds ? $testMode : '-',
            ];
        }

        $this->info('Active payment gateways');
        if (empty($rows)) {
            $this->warn('  No active gateways — enable one in Admin → Settings → Payment channels');
        } else {
            $this->table(['Title', 'Class', 'Status', 'Credentials', 'Currencies', 'Mode'], $rows);
        }

        $offline = getOfflineBankSettings('offline_banks_status');
        $this->line('Offline bank transfer: ' . (!empty($offline) ? 'ENABLED (' . OfflineBank::count() . ' accounts)' : 'disabled'));
    }

    private function enableOfflineBanks(): void
    {
        $setting = Setting::where('name', Setting::$offlineBanksName)->first();

        if (empty($setting)) {
            $this->error('offline_banks setting not found in database.');

            return;
        }

        $locale = Setting::$defaultSettingsLocale;
        $translation = $setting->translateOrNew($locale);
        $value = json_decode($translation->value ?? '{}', true) ?: [];
        $value['offline_banks_status'] = '1';
        $translation->value = json_encode($value);
        $translation->save();

        Setting::$offlineBanks = null;

        $this->info('Offline bank transfer enabled at checkout.');
    }
}
