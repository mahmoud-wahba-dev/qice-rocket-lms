<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('webinars')) {
            return;
        }

        Schema::table('webinars', function (Blueprint $table) {
            if (!Schema::hasColumn('webinars', 'certificate_accredited_by_ar')) {
                $table->string('certificate_accredited_by_ar', 255)->nullable()->after('certificate');
            }
            if (!Schema::hasColumn('webinars', 'certificate_accredited_by_en')) {
                $table->string('certificate_accredited_by_en', 255)->nullable()->after('certificate_accredited_by_ar');
            }
            if (!Schema::hasColumn('webinars', 'certificate_accreditation_number')) {
                $table->string('certificate_accreditation_number', 120)->nullable()->after('certificate_accredited_by_en');
            }
        });

        try {
            Schema::table('webinars', function (Blueprint $table) {
                $table->unique('certificate_accreditation_number', 'webinars_certificate_accreditation_number_unique');
            });
        } catch (\Throwable $e) {
            // Index may already exist.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('webinars')) {
            return;
        }

        try {
            Schema::table('webinars', function (Blueprint $table) {
                $table->dropUnique('webinars_certificate_accreditation_number_unique');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('webinars', function (Blueprint $table) {
            foreach ([
                'certificate_accreditation_number',
                'certificate_accredited_by_en',
                'certificate_accredited_by_ar',
            ] as $col) {
                if (Schema::hasColumn('webinars', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
