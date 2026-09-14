<?php

use App\Models\Sale;
use App\Models\Session;
use App\Models\TimeSpentOnCourse;
use App\Models\Translation\SessionTranslation;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures دوراتي course-card meta is stored as real DB rows:
 * webinars.type/duration, sessions (lectures), time_spent_on_courses, sales.created_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('webinars') || !Schema::hasTable('sessions')) {
            return;
        }

        $locale = 'ar';
        $now = time();
        // 22 July 2026 12:00 Asia/Riyadh-ish as unix (UTC midday)
        $registeredAt = strtotime('2026-07-22 12:00:00');

        $slugs = [
            'panel-v1-student-demo-course',
            'panel-v1-student-demo-course-2',
        ];

        foreach ($slugs as $index => $slug) {
            $webinar = Webinar::where('slug', $slug)->first();
            if (!$webinar) {
                continue;
            }

            // النوع + مدة (stored on webinars)
            $webinar->type = $index === 0 ? Webinar::$webinar : Webinar::$course;
            $webinar->duration = $index === 0 ? 30 : 90;
            $webinar->updated_at = $now;
            $webinar->save();

            $chapter = WebinarChapter::where('webinar_id', $webinar->id)->orderBy('id')->first();
            if (!$chapter) {
                continue;
            }

            $teacherId = (int) ($webinar->teacher_id ?: $webinar->creator_id);
            $targetLectures = $index === 0 ? 12 : 6;
            $existingSessions = Session::where('webinar_id', $webinar->id)->count();

            for ($i = $existingSessions + 1; $i <= $targetLectures; $i++) {
                $sessionId = Session::insertGetId([
                    'webinar_id' => $webinar->id,
                    'chapter_id' => $chapter->id,
                    'creator_id' => $teacherId,
                    'date' => $now + ($i * 86400),
                    'duration' => max(30, 180 - ($i * 5)),
                    'link' => null,
                    'session_api' => 'local',
                    'api_secret' => null,
                    'check_previous_parts' => false,
                    'status' => Session::$Active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if (Schema::hasTable('session_translations')) {
                    SessionTranslation::updateOrCreate(
                        ['session_id' => $sessionId, 'locale' => $locale],
                        [
                            'title' => 'محاضرة ' . $i,
                            'description' => 'محاضرة ديناميكية لبطاقة دوراتي',
                        ]
                    );
                }

                if (Schema::hasTable('webinar_chapter_items') && method_exists(WebinarChapterItem::class, 'makeItem')) {
                    WebinarChapterItem::makeItem($teacherId, $chapter->id, $sessionId, WebinarChapterItem::$chapterSession);
                }
            }

            // تاريخ التسجيل on sales + ساعات النشاط via time_spent_on_courses
            $saleIds = Sale::where('webinar_id', $webinar->id)->pluck('buyer_id', 'id');
            foreach ($saleIds as $saleId => $buyerId) {
                Sale::where('id', $saleId)->update([
                    'created_at' => $registeredAt + $index,
                ]);

                if (!Schema::hasTable('time_spent_on_courses')) {
                    continue;
                }

                $existingSpent = TimeSpentOnCourse::where('user_id', $buyerId)
                    ->where('course_id', $webinar->id)
                    ->sum('seconds_spent');

                $targetSeconds = $index === 0 ? 60 : 120; // 0:01 / 0:02
                if ((int) $existingSpent < $targetSeconds) {
                    TimeSpentOnCourse::create([
                        'user_id' => (int) $buyerId,
                        'course_id' => $webinar->id,
                        'page' => 'learning_page',
                        'entry_time' => $now - $targetSeconds,
                        'exit_time' => $now,
                        'seconds_spent' => $targetSeconds - (int) $existingSpent,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: keep seeded learning data.
    }
};
