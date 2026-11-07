<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "archived" course status. Archived courses are hidden from the public site
 * (every public query filters status = 'active'); students who bought them keep access
 * until their own access_days end.
 */
return new class extends Migration
{
    public function up(): void
    {
        $column = DB::selectOne("SHOW COLUMNS FROM webinars WHERE Field = 'status'");

        if ($column && stripos($column->Type, "'archived'") === false) {
            DB::statement("ALTER TABLE webinars MODIFY status ENUM('active','pending','is_draft','inactive','archived') NOT NULL");
        }
    }

    public function down(): void
    {
        DB::table('webinars')->where('status', 'archived')->update(['status' => 'inactive']);

        DB::statement("ALTER TABLE webinars MODIFY status ENUM('active','pending','is_draft','inactive') NOT NULL");
    }
};
