<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

class AddNewStatusInReserveMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): reserve_meetings.meeting_id was renamed to meeting_time_id by an earlier
        // migration; anchor new columns to whichever exists. All ops idempotent.
        $anchor = Schema::hasColumn('reserve_meetings', 'meeting_id')
            ? 'meeting_id'
            : (Schema::hasColumn('reserve_meetings', 'meeting_time_id') ? 'meeting_time_id' : null);

        try {
            DB::statement("ALTER TABLE `reserve_meetings` MODIFY COLUMN `status` enum('pending','open','finished','canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `password`");
        } catch (\Throwable $e) {
        }

        Schema::table('reserve_meetings', function (Blueprint $table) use ($anchor) {
            if (!Schema::hasColumn('reserve_meetings', 'sale_id')) {
                if ($anchor) {
                    $table->integer('sale_id')->unsigned()->after($anchor)->nullable();
                } else {
                    $table->integer('sale_id')->unsigned()->nullable();
                }
            }
            if (!Schema::hasColumn('reserve_meetings', 'date')) {
                if (Schema::hasColumn('reserve_meetings', 'day')) {
                    $table->integer('date')->unsigned()->after('day');
                } else {
                    $table->integer('date')->unsigned()->nullable();
                }
            }
        });

        // FK idempotent
        $fkExists = collect(\Illuminate\Support\Facades\DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reserve_meetings' AND CONSTRAINT_NAME = 'reserve_meetings_sale_id_foreign'"
        ))->isNotEmpty();
        if (!$fkExists && Schema::hasColumn('reserve_meetings', 'sale_id')) {
            try {
                Schema::table('reserve_meetings', function (Blueprint $table) {
                    $table->foreign('sale_id')->on('sales')->references('id')->onDelete('cascade');
                });
            } catch (\Throwable $e) {
            }
        }
    }
}
