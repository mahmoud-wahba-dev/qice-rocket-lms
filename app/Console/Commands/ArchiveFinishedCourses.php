<?php

namespace App\Console\Commands;

use App\Models\Webinar;
use Illuminate\Console\Command;

class ArchiveFinishedCourses extends Command
{
    protected $signature = 'courses:archive-finished {--dry-run : List the courses that would be archived without changing anything}';

    protected $description = 'Move live courses whose last session has already ended to the archive (status = archived)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now = time();
        $rows = [];

        Webinar::query()
            ->where('type', Webinar::$webinar)
            ->whereIn('status', [Webinar::$active, Webinar::$inactive])
            ->orderBy('id')
            ->chunkById(100, function ($courses) use ($now, $dryRun, &$rows) {
                foreach ($courses as $course) {
                    $endsAt = $course->getEndTimestamp();

                    if (empty($endsAt) || $endsAt >= $now) {
                        continue;
                    }

                    $rows[] = [$course->id, $course->slug, date('Y-m-d H:i', $endsAt)];

                    if (!$dryRun) {
                        Webinar::where('id', $course->id)->update([
                            'status' => Webinar::$archived,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });

        if (empty($rows)) {
            $this->info('No finished courses to archive.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Slug', 'Ended at'], $rows);
        $this->info(($dryRun ? 'Would archive ' : 'Archived ') . count($rows) . ' course(s).');

        return self::SUCCESS;
    }
}
