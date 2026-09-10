<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddProductIdToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): idempotent + anchor fallbacks (order_items has no product_id column).
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'product_order_id')) {
                if (Schema::hasColumn('sales', 'promotion_id')) {
                    $table->integer('product_order_id')->unsigned()->nullable()->after('promotion_id');
                } else {
                    $table->integer('product_order_id')->unsigned()->nullable();
                }
            }
        });
        try {
            DB::statement("ALTER TABLE `sales` MODIFY COLUMN `type` enum('webinar','meeting','subscribe','promotion','registration_package','product') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `registration_package_id`");
        } catch (\Throwable $e) {
            try {
                DB::statement("ALTER TABLE `sales` MODIFY COLUMN `type` enum('webinar','meeting','subscribe','promotion','registration_package','product') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
            } catch (\Throwable $e2) {
            }
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_order_id')) {
                // original anchored after product_id but that column was commented out upstream
                $anchor = Schema::hasColumn('order_items', 'product_id')
                    ? 'product_id'
                    : (Schema::hasColumn('order_items', 'registration_package_id') ? 'registration_package_id' : null);
                if ($anchor) {
                    $table->integer('product_order_id')->unsigned()->nullable()->after($anchor);
                } else {
                    $table->integer('product_order_id')->unsigned()->nullable();
                }
            }
        });

        Schema::table('accounting', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting', 'product_id')) {
                if (Schema::hasColumn('accounting', 'registration_package_id')) {
                    $table->integer('product_id')->unsigned()->nullable()->after('registration_package_id');
                } else {
                    $table->integer('product_id')->unsigned()->nullable();
                }
            }
        });
    }
}
