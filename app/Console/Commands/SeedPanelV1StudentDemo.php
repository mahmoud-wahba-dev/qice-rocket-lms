<?php

namespace App\Console\Commands;

use Database\Seeders\PanelV1StudentDemoSeeder;
use Illuminate\Console\Command;

class SeedPanelV1StudentDemo extends Command
{
    protected $signature = 'qiec:seed-student-demo
                            {--email=* : Extra student email(s) to enroll with demo data}
                            {--all-empty : Also enroll student accounts that currently have no course sales}';

    protected $description = 'Seed panel_v1 student demo course, enrollments, and related activity data';

    public function handle(): int
    {
        PanelV1StudentDemoSeeder::$extraEmails = (array) $this->option('email');
        PanelV1StudentDemoSeeder::$enrollEmptyStudents = (bool) $this->option('all-empty');

        // Default: always fill empty student accounts so local logins see data
        if (!$this->option('all-empty') && empty(PanelV1StudentDemoSeeder::$extraEmails)) {
            PanelV1StudentDemoSeeder::$enrollEmptyStudents = true;
        }

        $this->call('db:seed', [
            '--class' => PanelV1StudentDemoSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
