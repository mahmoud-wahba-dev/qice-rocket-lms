<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_channel_id')) {
                $table->unsignedInteger('payment_channel_id')->nullable()->after('payment_method');
                $table->index('payment_channel_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_channel_id')) {
                $table->dropColumn('payment_channel_id');
            }
        });
    }
};
