<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToPaymentChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): payment_channels has `credentials`, not `settings`; anchor safely + idempotent.
        Schema::table('payment_channels', function (Blueprint $table) {
            if (Schema::hasColumn('payment_channels', 'currencies')) {
                return;
            }
            $anchor = Schema::hasColumn('payment_channels', 'settings')
                ? 'settings'
                : (Schema::hasColumn('payment_channels', 'credentials') ? 'credentials' : null);
            if ($anchor) {
                $table->text('currencies')->nullable()->after($anchor);
            } else {
                $table->text('currencies')->nullable();
            }
        });
    }
}
