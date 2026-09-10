<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): split raws from schema adds; templates has no `type` col → add it.
        try {
            DB::statement("ALTER TABLE `certificates` MODIFY COLUMN `type` enum('quiz', 'course', 'bundle') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `user_grade`");
        } catch (\Throwable $e) {
            try {
                DB::statement("ALTER TABLE `certificates` MODIFY COLUMN `type` enum('quiz', 'course', 'bundle') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
            } catch (\Throwable $e2) {
            }
        }

        if (!Schema::hasColumn('certificates_templates', 'type')) {
            Schema::table('certificates_templates', function (Blueprint $table) {
                if (Schema::hasColumn('certificates_templates', 'image')) {
                    $table->enum('type', ['quiz', 'course', 'bundle'])->default('course')->after('image');
                } else {
                    $table->enum('type', ['quiz', 'course', 'bundle'])->default('course');
                }
            });
        } else {
            try {
                DB::statement("ALTER TABLE `certificates_templates` MODIFY COLUMN `type` enum('quiz', 'course', 'bundle') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `image`");
            } catch (\Throwable $e) {
            }
        }

        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'bundle_id')) {
                if (Schema::hasColumn('certificates', 'webinar_id')) {
                    $table->integer('bundle_id')->unsigned()->nullable()->after('webinar_id');
                } else {
                    $table->integer('bundle_id')->unsigned()->nullable();
                }
            }
        });

        $fkExists = collect(DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certificates' AND CONSTRAINT_NAME = 'certificates_bundle_id_foreign'"
        ))->isNotEmpty();
        if (!$fkExists && Schema::hasColumn('certificates', 'bundle_id')) {
            try {
                Schema::table('certificates', function (Blueprint $table) {
                    $table->foreign('bundle_id')->on('bundles')->references('id')->cascadeOnDelete();
                });
            } catch (\Throwable $e) {
            }
        }
    }

};
