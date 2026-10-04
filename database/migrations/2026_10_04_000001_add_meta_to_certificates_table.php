<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('certificates')) {
            return;
        }
        if (!Schema::hasColumn('certificates', 'meta')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->longText('meta')->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'meta')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('meta');
            });
        }
    }
};
