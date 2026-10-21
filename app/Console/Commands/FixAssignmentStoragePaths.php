<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAssignmentStoragePaths extends Command
{
    protected $signature = 'qiec:fix-assignment-storage-paths
                            {--dry-run : Show how many rows would be updated without writing}';

    protected $description = 'Rewrite mistaken /storage/assignments/ paths to /store/assignments/ in assignment history messages';

    public function handle(): int
    {
        $count = DB::table('webinar_assignment_history_messages')
            ->where(function ($q) {
                $q->where('file_path', 'like', '/storage/assignments/%')
                    ->orWhere('file_path', 'like', 'storage/assignments/%');
            })
            ->count();

        if ($count === 0) {
            $this->info('No assignment file_path rows need rewriting.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} row(s) with /storage/assignments/ prefix.");

        if ($this->option('dry-run')) {
            $this->comment('Dry run only — no changes written.');

            return self::SUCCESS;
        }

        $updatedSlash = DB::table('webinar_assignment_history_messages')
            ->where('file_path', 'like', '/storage/assignments/%')
            ->update([
                'file_path' => DB::raw("REPLACE(file_path, '/storage/assignments/', '/store/assignments/')"),
            ]);

        $updatedBare = DB::table('webinar_assignment_history_messages')
            ->where('file_path', 'like', 'storage/assignments/%')
            ->update([
                'file_path' => DB::raw("REPLACE(file_path, 'storage/assignments/', '/store/assignments/')"),
            ]);

        $this->info('Updated ' . ($updatedSlash + $updatedBare) . ' row(s).');
        $this->comment('Confirm files exist under public/store/assignments/ on the server.');

        return self::SUCCESS;
    }
}
