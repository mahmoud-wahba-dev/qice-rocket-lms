<?php

namespace Database\Seeders;

use App\Models\Webinar;
use Illuminate\Database\Seeder;

/**
 * Seeds/updates webinars.start_date so landing "تاريخ البرنامج" can be tested.
 *
 * php artisan db:seed --class=LandingProgramDatesSeeder --force
 */
class LandingProgramDatesSeeder extends Seeder
{
    public function run(): void
    {
        // Explicit demo / screenshot slugs first.
        $fixed = [
            'p1-inst-courses-recorded-healthcare-quality' => '2026-11-05 10:00:00',
            'p1-inst-courses-live-quality-kickoff' => '2026-11-12 19:00:00',
            'qiec-demo-quality-management' => '2026-11-08 18:00:00',
            'qiec-demo-digital-marketing' => '2026-11-15 17:00:00',
            'qiec-demo-project-management' => '2026-11-20 16:30:00',
            'qiec-demo-healthcare-leadership' => '2026-11-22 10:00:00',
            'qiec-demo-institutional-excellence-pro' => '2026-11-25 11:00:00',
            'qiec-demo-compliance-program' => '2026-11-28 14:00:00',
            'qiec-demo-corporate-governance-basics' => '2026-11-03 09:00:00',
            'qiec-demo-health-administration-intro' => '2026-11-04 09:30:00',
            'qiec-demo-institutional-excellence' => '2026-11-06 10:00:00',
            'qiec-demo-professional-communication' => '2026-11-07 11:00:00',
            'qiec-demo-training-development-intro' => '2026-11-09 12:00:00',
            'qiec-demo-labor-law-for-training-centers' => '2026-11-10 13:00:00',
            'qiec-demo-high-performance-teams' => '2026-11-11 15:00:00',
        ];

        $updated = 0;
        $missing = 0;

        foreach ($fixed as $slug => $localDateTime) {
            $webinar = Webinar::query()->where('slug', $slug)->first();
            if (empty($webinar)) {
                $missing++;
                continue;
            }

            $webinar->start_date = $this->toUnix($localDateTime, $webinar->timezone);
            $webinar->updated_at = time();
            $webinar->save();
            $updated++;
        }

        // Fill remaining active public courses that still lack start_date (staggered dummy dates).
        $extras = Webinar::query()
            ->where('status', Webinar::$active)
            ->where('private', false)
            ->whereNull('start_date')
            ->orderBy('id')
            ->limit(40)
            ->get();

        $day = 0;
        foreach ($extras as $webinar) {
            $local = date('Y-m-d H:i:s', strtotime('+'.(3 + $day).' days 10:00:00'));
            $webinar->start_date = $this->toUnix($local, $webinar->timezone);
            $webinar->updated_at = time();
            $webinar->save();
            $updated++;
            $day++;
        }

        $this->command?->info("Landing program dates: {$updated} webinars updated, {$missing} fixed slugs not found.");
    }

    private function toUnix(string $localDateTime, ?string $timezone = null): int
    {
        $timezone = $timezone ?: (function_exists('getTimezone') ? getTimezone() : 'Asia/Riyadh');

        if (function_exists('convertTimeToUTCzone')) {
            try {
                return (int) convertTimeToUTCzone($localDateTime, $timezone)->getTimestamp();
            } catch (\Throwable $e) {
                // fall through
            }
        }

        return (int) (strtotime($localDateTime) ?: time());
    }
}
