<?php

namespace App\Console\Commands;

use Database\Seeders\LandingV1DemoDataSeeder;
use Illuminate\Console\Command;

class SeedLandingDemoData extends Command
{
    protected $signature = 'qiec:seed-landing-demo';

    protected $description = 'Seed free workshop demo data for the QIEC landing pages';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => LandingV1DemoDataSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
