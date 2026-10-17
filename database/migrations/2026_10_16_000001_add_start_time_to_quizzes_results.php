<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent — safe to run multiple times.
 * Adds start_time (unix timestamp) to quizzes_results for server-side timer enforcement.
 */
class AddStartTimeToQuizzesResults extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('quizzes_results', 'start_time')) {
            Schema::table('quizzes_results', function (Blueprint $table) {
                $table->integer('start_time')->unsigned()->nullable()->after('created_at');
                $table->integer('time_spent')->unsigned()->nullable()->after('start_time'); // seconds
            });
        } elseif (!Schema::hasColumn('quizzes_results', 'time_spent')) {
            Schema::table('quizzes_results', function (Blueprint $table) {
                $table->integer('time_spent')->unsigned()->nullable()->after('start_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quizzes_results', 'start_time')) {
            Schema::table('quizzes_results', function (Blueprint $table) {
                $table->dropColumn('start_time');
            });
        }
    }
}
