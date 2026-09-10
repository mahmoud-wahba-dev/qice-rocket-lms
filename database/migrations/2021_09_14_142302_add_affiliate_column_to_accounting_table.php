<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAffiliateColumnToAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): original anchored is_affiliate_amount after affiliate_user_id,
        // but accounting has no such column (it lives on affiliates). Anchor to referred_user_id instead.
        Schema::table('accounting', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting', 'referred_user_id')) {
                $table->integer('referred_user_id')->unsigned()->nullable()->after('store_type');
            }
        });
        Schema::table('accounting', function (Blueprint $table) {
            $anchor = Schema::hasColumn('accounting', 'affiliate_user_id')
                ? 'affiliate_user_id'
                : (Schema::hasColumn('accounting', 'referred_user_id') ? 'referred_user_id' : null);
            if (!Schema::hasColumn('accounting', 'is_affiliate_amount')) {
                if ($anchor) {
                    $table->boolean('is_affiliate_amount')->after($anchor)->default(false);
                } else {
                    $table->boolean('is_affiliate_amount')->default(false);
                }
            }
            if (!Schema::hasColumn('accounting', 'is_affiliate_commission')) {
                if (Schema::hasColumn('accounting', 'is_affiliate_amount')) {
                    $table->boolean('is_affiliate_commission')->after('is_affiliate_amount')->default(false);
                } else {
                    $table->boolean('is_affiliate_commission')->default(false);
                }
            }
        });
    }
}
