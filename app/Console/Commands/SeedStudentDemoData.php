<?php

namespace App\Console\Commands;

use Database\Seeders\StudentV1DemoDataSeeder;
use Illuminate\Console\Command;

class SeedStudentDemoData extends Command
{
    protected $signature = 'qiec:seed-student-demo';

    protected $description = 'Seed demo data for testing the student v1 panel (regions, banks, enrollments, finance, notifications, support, course content)';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => StudentV1DemoDataSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
