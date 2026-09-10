<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMeetingIdToMeetingTimeIdInAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): original assumed FK accounting_meeting_id_foreign exists,
        // but create_accounting migration never created it. Drop only if present.
        $fkExists = collect(\Illuminate\Support\Facades\DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accounting' AND CONSTRAINT_NAME = 'accounting_meeting_id_foreign'"
        ))->isNotEmpty();
        if ($fkExists) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `accounting` DROP FOREIGN KEY `accounting_meeting_id_foreign`;");
        }
        // Rename/change only if the old column still exists and new one doesn't
        if (\Illuminate\Support\Facades\Schema::hasColumn('accounting', 'meeting_id') && !\Illuminate\Support\Facades\Schema::hasColumn('accounting', 'meeting_time_id')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `accounting` CHANGE COLUMN `meeting_id` `meeting_time_id` INTEGER UNSIGNED NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting', function (Blueprint $table) {
            //
        });
    }
}
