<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            if (!Schema::hasColumn('files', 'processing_status')) {
                $table->string('processing_status', 32)->nullable()->after('status');
            }
            if (!Schema::hasColumn('files', 'processing_error')) {
                $table->text('processing_error')->nullable()->after('processing_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            if (Schema::hasColumn('files', 'processing_error')) {
                $table->dropColumn('processing_error');
            }
            if (Schema::hasColumn('files', 'processing_status')) {
                $table->dropColumn('processing_status');
            }
        });
    }
};
