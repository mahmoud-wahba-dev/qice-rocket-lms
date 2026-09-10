<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

class AddRegistrationPackageIdToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): raw MODIFY ... AFTER new column ran before the column existed.
        // Add columns first, then modify enums. All idempotent.
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'registration_package_id')) {
                if (Schema::hasColumn('sales', 'promotion_id')) {
                    $table->integer('registration_package_id')->unsigned()->nullable()->after('promotion_id');
                } else {
                    $table->integer('registration_package_id')->unsigned()->nullable();
                }
            }
        });
        try {
            DB::statement("ALTER TABLE `sales` MODIFY COLUMN `type` enum('webinar','meeting','subscribe','promotion','registration_package') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `registration_package_id`");
        } catch (\Throwable $e) {
            try {
                DB::statement("ALTER TABLE `sales` MODIFY COLUMN `type` enum('webinar','meeting','subscribe','promotion','registration_package') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
            } catch (\Throwable $e2) {
            }
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'registration_package_id')) {
                if (Schema::hasColumn('order_items', 'promotion_id')) {
                    $table->integer('registration_package_id')->unsigned()->nullable()->after('promotion_id');
                } else {
                    $table->integer('registration_package_id')->unsigned()->nullable();
                }
            }
        });

        Schema::table('accounting', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting', 'registration_package_id')) {
                if (Schema::hasColumn('accounting', 'promotion_id')) {
                    $table->integer('registration_package_id')->unsigned()->nullable()->after('promotion_id');
                } else {
                    $table->integer('registration_package_id')->unsigned()->nullable();
                }
            }
        });
        try {
            DB::statement("ALTER TABLE `accounting` MODIFY COLUMN `type_account` enum('income','asset','subscribe','promotion','registration_package') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `type`");
        } catch (\Throwable $e) {
        }
    }
}
