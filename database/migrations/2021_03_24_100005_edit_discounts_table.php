<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

class EditDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): make idempotent — original assumed columns exist; guard each change.
        if (Schema::hasColumn('discounts', 'name')) {
            DB::statement("ALTER TABLE `discounts` DROP COLUMN `name`");
        }
        if (Schema::hasColumn('discount_users', 'count')) {
            DB::statement("ALTER TABLE `discount_users` DROP COLUMN `count`");
        }
        if (Schema::hasColumn('discounts', 'started_at')) {
            DB::statement("ALTER TABLE `discounts` DROP COLUMN `started_at`");
        }
        // Ensure created_at is positioned after expired_at (only if both exist)
        if (Schema::hasColumn('discounts', 'created_at') && Schema::hasColumn('discounts', 'expired_at')) {
            try {
                DB::statement("ALTER TABLE `discounts` MODIFY COLUMN `created_at` int UNSIGNED NOT NULL AFTER `expired_at`");
            } catch (\Throwable $e) {
            }
        }

        Schema::table('discounts', function (Blueprint $table) {
            if (!Schema::hasColumn('discounts', 'title')) {
                $table->string('title')->after('creator_id');
            }
            if (!Schema::hasColumn('discounts', 'code')) {
                $table->string('code', 64)->after('title')->unique();
            }
            if (!Schema::hasColumn('discounts', 'type')) {
                $table->enum('type', ['all_users', 'special_users'])->after('count');
            }
        });
    }
}
