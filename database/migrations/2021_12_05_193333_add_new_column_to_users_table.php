<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): original ran raw ADD level_of_training AFTER location inside the same
        // Schema::table closure — but DB::statement executes immediately, before $table adds.
        // Split into ordered steps + idempotent guards.
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'country_id')) {
                $table->integer('country_id')->unsigned()->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'province_id')) {
                $table->integer('province_id')->unsigned()->nullable()->after('country_id');
            }
            if (!Schema::hasColumn('users', 'city_id')) {
                $table->integer('city_id')->unsigned()->nullable()->after('province_id');
            }
            if (!Schema::hasColumn('users', 'district_id')) {
                $table->integer('district_id')->unsigned()->nullable()->after('city_id');
            }
            if (!Schema::hasColumn('users', 'location')) {
                try {
                    $table->point('location')->nullable()->after('district_id');
                } catch (\Throwable $e) {
                    // MySQL spatial may be unavailable; fall back to nullable string-ish via raw
                    if (!Schema::hasColumn('users', 'location')) {
                        DB::statement("ALTER TABLE `users` ADD COLUMN `location` POINT NULL AFTER `district_id`");
                    }
                }
            }
            if (!Schema::hasColumn('users', 'group_meeting')) {
                if (Schema::hasColumn('users', 'location')) {
                    $table->boolean('group_meeting')->default(false)->after('location');
                } else {
                    $table->boolean('group_meeting')->default(false);
                }
            }
        });

        if (!Schema::hasColumn('users', 'level_of_training')) {
            if (Schema::hasColumn('users', 'location')) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `level_of_training` bit(3) NULL AFTER `location`");
            } else {
                DB::statement("ALTER TABLE `users` ADD COLUMN `level_of_training` bit(3) NULL");
            }
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'meeting_type')) {
                if (Schema::hasColumn('users', 'level_of_training')) {
                    $table->enum('meeting_type', ['all', 'in_person', 'online'])->default('all')->after('level_of_training');
                } else {
                    $table->enum('meeting_type', ['all', 'in_person', 'online'])->default('all');
                }
            }
        });
    }
}
