<?php

namespace Database\Seeders;

use App\Models\Accounting;
use App\Models\Favorite;
use App\Models\File;
use App\Models\Payout;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\Session;
use App\Models\Sale;
use App\Models\Subscribe;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\UserBank;
use App\Models\UserBankSpecification;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data for testing the student v1 panel end-to-end:
 * regions + banks (settings), enrollments, accounting, subscribe plan,
 * payouts, notifications, support tickets, favorites, notes and
 * course content (sessions/files/quiz/assignment) for the enrolled course.
 *
 * Idempotent: safe to run multiple times.
 * Run: php artisan db:seed --class=StudentV1DemoDataSeeder --force
 */
class StudentV1DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();

        $student = DB::table('users')->where('email', 'user@gmail.com')->first();
        if (empty($student)) {
            $student = DB::table('users')->where('role_id', 1)->orderBy('id')->first();
        }

        if (empty($student)) {
            $this->command?->warn('لا يوجد مستخدم طالب — شغّل UsersTableSeeder أولاً.');

            return;
        }
        $studentId = (int) $student->id;

        $this->seedRegions();
        $this->seedBanks();
        $this->seedEnrollments($studentId, $now);
        $this->seedFinance($studentId, $now);
        $this->seedNotifications($studentId, $now);
        $this->seedSupport($studentId, $now);
        $this->seedFavoritesAndNotes($studentId, $now);
        $this->seedCourseContent($now);

        $this->command?->info("تم حقن البيانات التجريبية لقسم الطالب (user id: {$studentId}).");
    }

    private function seedRegions(): void
    {
        if (DB::table('regions')->count() > 0) {
            return;
        }

        $now = time();
        $insert = function (array $row) use ($now) {
            return DB::table('regions')->insertGetId($row + ['created_at' => $now]);
        };

        $countryId = $insert(['type' => 'country', 'title' => 'السعودية']);

        $riyadhProv = $insert(['type' => 'province', 'title' => 'الرياض', 'country_id' => $countryId]);
        $makkahProv = $insert(['type' => 'province', 'title' => 'مكة المكرمة', 'country_id' => $countryId]);
        $eastProv = $insert(['type' => 'province', 'title' => 'المنطقة الشرقية', 'country_id' => $countryId]);

        $riyadhCity = $insert(['type' => 'city', 'title' => 'مدينة الرياض', 'country_id' => $countryId, 'province_id' => $riyadhProv]);
        $jeddahCity = $insert(['type' => 'city', 'title' => 'جدة', 'country_id' => $countryId, 'province_id' => $makkahProv]);
        $dammamCity = $insert(['type' => 'city', 'title' => 'الدمام', 'country_id' => $countryId, 'province_id' => $eastProv]);

        foreach (['النرجس', 'الملقا', 'الياسمين'] as $district) {
            $insert(['type' => 'district', 'title' => $district, 'country_id' => $countryId, 'province_id' => $riyadhProv, 'city_id' => $riyadhCity]);
        }
        foreach (['الروضة', 'الشاطئ'] as $district) {
            $insert(['type' => 'district', 'title' => $district, 'country_id' => $countryId, 'province_id' => $makkahProv, 'city_id' => $jeddahCity]);
        }
        foreach (['الفيصلية', 'الشاطئ الغربي'] as $district) {
            $insert(['type' => 'district', 'title' => $district, 'country_id' => $countryId, 'province_id' => $eastProv, 'city_id' => $dammamCity]);
        }
    }

    private function seedBanks(): void
    {
        if (UserBank::count() > 0) {
            return;
        }

        $banks = [
            'مصرف الراجحي' => 'Al Rajhi Bank',
            'البنك الأهلي السعودي' => 'Saudi National Bank',
            'بنك الرياض' => 'Riyad Bank',
        ];

        foreach ($banks as $ar => $en) {
            $bank = UserBank::create(['logo' => '', 'created_at' => time()]);
            DB::table('user_bank_translations')->insert([
                ['user_bank_id' => $bank->id, 'locale' => 'ar', 'title' => $ar],
                ['user_bank_id' => $bank->id, 'locale' => 'en', 'title' => $en],
            ]);

            foreach ([
                'رقم الآيبان (IBAN)' => 'IBAN',
                'رقم الحساب' => 'Account number',
            ] as $specAr => $specEn) {
                $spec = UserBankSpecification::create(['user_bank_id' => $bank->id]);
                DB::table('user_bank_specification_translations')->insert([
                    ['user_bank_specification_id' => $spec->id, 'locale' => 'ar', 'name' => $specAr],
                    ['user_bank_specification_id' => $spec->id, 'locale' => 'en', 'name' => $specEn],
                ]);
            }
        }
    }

    private function seedEnrollments(int $studentId, int $now): void
    {
        $enrolledIds = Sale::where('buyer_id', $studentId)->whereNotNull('webinar_id')->pluck('webinar_id')->all();

        $extra = Webinar::where('status', 'active')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('id')
            ->limit(2)
            ->get();

        foreach ($extra as $webinar) {
            Sale::firstOrCreate([
                'buyer_id' => $studentId,
                'webinar_id' => $webinar->id,
            ], [
                'seller_id' => $webinar->teacher_id,
                'type' => Sale::$webinar,
                'amount' => $webinar->price ?? 0,
                'total_amount' => $webinar->price ?? 0,
                'created_at' => $now,
            ]);
        }
    }

    private function seedFinance(int $studentId, int $now): void
    {
        $rows = [
            ['amount' => 2500, 'type' => Accounting::$addiction, 'type_account' => 'income', 'description' => 'شحن رصيد تجريبي [demo]'],
            ['amount' => 350, 'type' => Accounting::$addiction, 'type_account' => 'income', 'description' => 'مكافأة إحالة [demo]'],
            ['amount' => 99, 'type' => Accounting::$deduction, 'type_account' => 'asset', 'description' => 'شراء دورة [demo]'],
        ];

        foreach ($rows as $row) {
            $exists = Accounting::where('user_id', $studentId)
                ->where('description', $row['description'])
                ->exists();
            if (!$exists) {
                Accounting::create($row + ['user_id' => $studentId, 'created_at' => $now]);
            }
        }

        if (Subscribe::count() === 0) {
            $plan = Subscribe::create([
                'target_type' => 'courses',
                'usable_count' => 4,
                'days' => 30,
                'price' => 199,
                'icon' => '',
                'infinite_use' => 0,
                'created_at' => $now,
            ]);
            DB::table('subscribe_translations')->insert([
                ['subscribe_id' => $plan->id, 'locale' => 'ar', 'title' => 'الباقة الذهبية', 'subtitle' => '4 دورات شهرياً', 'description' => 'اشتراك شهري للوصول إلى 4 دورات معتمدة.'],
                ['subscribe_id' => $plan->id, 'locale' => 'en', 'title' => 'Gold Plan', 'subtitle' => '4 courses monthly', 'description' => 'Monthly subscription for 4 certified courses.'],
            ]);
        }

        $subscribeId = Subscribe::orderBy('id')->value('id');
        if (!empty($subscribeId)) {
            $enrolledSale = Sale::where('buyer_id', $studentId)->whereNotNull('webinar_id')->orderBy('id')->first();
            if (!empty($enrolledSale)) {
                $useExists = DB::table('subscribe_uses')->where([
                    'user_id' => $studentId,
                    'subscribe_id' => $subscribeId,
                    'webinar_id' => $enrolledSale->webinar_id,
                ])->exists();
                if (!$useExists) {
                    DB::table('subscribe_uses')->insert([
                        'user_id' => $studentId,
                        'subscribe_id' => $subscribeId,
                        'webinar_id' => $enrolledSale->webinar_id,
                        'sale_id' => $enrolledSale->id,
                    ]);
                }
            }
        }

        if (!Payout::where('user_id', $studentId)->exists()) {
            Payout::create(['user_id' => $studentId, 'amount' => 500, 'status' => 'waiting', 'created_at' => $now]);
        }
    }

    private function seedNotifications(int $studentId, int $now): void
    {
        if (DB::table('notifications')->where('title', 'like', '%[تجريبي]%')->exists()) {
            return;
        }

        $rows = [
            ['user_id' => $studentId, 'title' => 'موعد تكليف التسويق الرقمي [تجريبي]', 'message' => 'تكليف "تحليل الحملات" يستحق التسليم غداً الساعة 11:59 مساءً.', 'sender' => 'system', 'type' => 'single'],
            ['user_id' => null, 'title' => 'ورشة مجانية جديدة [تجريبي]', 'message' => 'تم فتح التسجيل في ورشة "قياس أثر التدريب" لأول 20 متدرباً.', 'sender' => 'admin', 'type' => 'students'],
            ['user_id' => null, 'title' => 'صيانة مجدولة [تجريبي]', 'message' => 'ستكون المنصة متوقعة لمدة ساعة الجمعة القادم 2 صباحاً للصيانة.', 'sender' => 'admin', 'type' => 'all_users'],
        ];

        foreach ($rows as $row) {
            DB::table('notifications')->insert($row + ['created_at' => $now]);
        }
    }

    private function seedSupport(int $studentId, int $now): void
    {
        $departmentId = DB::table('support_departments')->orderBy('id')->value('id');

        $ticket = Support::firstOrCreate([
            'user_id' => $studentId,
            'title' => 'استفسار عن شهادة الدورة [تجريبي]',
        ], [
            'department_id' => $departmentId,
            'status' => 'supporter_replied',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($ticket->conversations()->count() === 0) {
            SupportConversation::create([
                'support_id' => $ticket->id,
                'sender_id' => $studentId,
                'message' => 'السلام عليكم، متى تُصدر شهادة إتمام دورة التسويق الرقمي؟',
                'created_at' => $now,
            ]);
            SupportConversation::create([
                'support_id' => $ticket->id,
                'supporter_id' => null,
                'sender_id' => $studentId,
                'message' => 'وعليكم السلام، تُصدر الشهادة تلقائياً بعد اجتياز الاختبار النهائي وحدوث 100% من تقدم الدورة.',
                'created_at' => $now + 3600,
            ]);
        }
    }

    private function seedFavoritesAndNotes(int $studentId, int $now): void
    {
        $webinarIds = Webinar::where('status', 'active')->orderBy('id')->limit(2)->pluck('id');

        foreach ($webinarIds as $webinarId) {
            Favorite::firstOrCreate([
                'user_id' => $studentId,
                'webinar_id' => $webinarId,
            ], ['created_at' => $now]);
        }

        $enrolledWebinarId = Sale::where('buyer_id', $studentId)->whereNotNull('webinar_id')->orderBy('id')->value('webinar_id');
        if (!empty($enrolledWebinarId)) {
            $notes = [
                'مراجعة توزيع الميزانية الإعلانية قبل الاختبار [تجريبي]',
                'أدوات القياس المذكورة في المحاضرة الأولى: GA4 و Meta Pixel [تجريبي]',
            ];
            foreach ($notes as $note) {
                $noteExists = DB::table('course_personal_notes')->where([
                    'user_id' => $studentId,
                    'course_id' => $enrolledWebinarId,
                    'note' => $note,
                ])->exists();
                if (!$noteExists) {
                    DB::table('course_personal_notes')->insert([
                        'user_id' => $studentId,
                        'course_id' => $enrolledWebinarId,
                        'targetable_id' => $enrolledWebinarId,
                        'targetable_type' => 'webinar',
                        'note' => $note,
                        'created_at' => $now,
                    ]);
                }
            }
        }
    }

    private function seedCourseContent(int $now): void
    {
        $sale = Sale::whereNotNull('webinar_id')->orderBy('id')->first();
        $webinar = $sale?->webinar;

        if (empty($webinar)) {
            return;
        }

        $teacherId = $webinar->teacher_id ?: 3;
        $chapterId = DB::table('webinar_chapters')->where('webinar_id', $webinar->id)->orderBy('order')->value('id');

        if (empty($chapterId)) {
            $chapterId = DB::table('webinar_chapters')->insertGetId([
                'user_id' => $teacherId,
                'webinar_id' => $webinar->id,
                'order' => 1,
                'status' => 'active',
                'created_at' => $now,
            ]);
        }

        if (Session::where('webinar_id', $webinar->id)->count() === 0) {
            foreach ([
                ['ar' => 'المحاضرة الأولى: مدخل إلى التسويق الرقمي', 'en' => 'Session 1: Digital Marketing Intro', 'offset' => 2],
                ['ar' => 'المحاضرة الثانية: قنوات الإعلانات المدفوعة', 'en' => 'Session 2: Paid Ads Channels', 'offset' => 5],
            ] as $index => $payload) {
                $session = Session::create([
                    'creator_id' => $teacherId,
                    'webinar_id' => $webinar->id,
                    'chapter_id' => $chapterId,
                    'date' => $now + ($payload['offset'] * 86400),
                    'duration' => 60,
                    'order' => $index + 1,
                    'status' => 'active',
                    'created_at' => $now,
                ]);

                foreach (['ar' => $payload['ar'], 'en' => $payload['en']] as $locale => $title) {
                    DB::table('session_translations')->updateOrInsert([
                        'session_id' => $session->id,
                        'locale' => $locale,
                    ], ['title' => $title]);
                }
            }
        }

        if (File::where('webinar_id', $webinar->id)->count() === 0) {
            foreach ([
                ['ar' => 'ملف مصادر المحاضرة الأولى', 'en' => 'Session 1 Resources', 'file' => 'demo/session-1-resources.pdf'],
                ['ar' => 'قالب خطة الحملة الإعلانية', 'en' => 'Campaign Plan Template', 'file' => 'demo/campaign-plan-template.pdf'],
            ] as $payload) {
                $file = File::create([
                    'creator_id' => $teacherId,
                    'webinar_id' => $webinar->id,
                    'chapter_id' => $chapterId,
                    'accessibility' => 'paid',
                    'downloadable' => 1,
                    'storage' => 'upload',
                    'file' => $payload['file'],
                    'volume' => '1.2MB',
                    'file_type' => 'pdf',
                    'created_at' => $now,
                ]);

                foreach (['ar' => $payload['ar'], 'en' => $payload['en']] as $locale => $title) {
                    DB::table('file_translations')->updateOrInsert([
                        'file_id' => $file->id,
                        'locale' => $locale,
                    ], ['title' => $title, 'description' => $locale === 'ar' ? 'مرفق تجريبي للاختبار' : null]);
                }
            }
        }

        $quiz = Quiz::where('webinar_id', $webinar->id)->orderBy('id')->first();
        if (empty($quiz)) {
            $quiz = Quiz::create([
                'webinar_id' => $webinar->id,
                'creator_id' => $teacherId,
                'chapter_id' => $chapterId,
                'time' => 10,
                'attempt' => 3,
                'pass_mark' => 50,
                'certificate' => 0,
                'status' => 'active',
                'created_at' => $now,
            ]);
        }

        if (QuizzesQuestion::where('quiz_id', $quiz->id)->count() < 2) {
            $this->seedQuizQuestion($quiz->id, $teacherId, 'ما هو مؤشر الأداء الذي يقيس تكلفة الحصول على العميل المحتمل؟', 50, $now, [
                'CTR' => false,
                'CPL' => true,
                'ROAS' => false,
                'CPM' => false,
            ]);
            $this->seedQuizQuestion($quiz->id, $teacherId, 'أي منصة مناسبة لاستهداف جمهور احترافي في قطاع B2B؟', 50, $now, [
                'TikTok Ads' => false,
                'LinkedIn Ads' => true,
                'Snapchat Ads' => false,
                'Pinterest Ads' => false,
            ]);
        }

        foreach (QuizzesQuestion::where('quiz_id', $quiz->id)->get() as $question) {
            if ($question->quizzesQuestionsAnswers()->count() === 0) {
                $this->seedQuizAnswers($question->id, $teacherId, [
                    'الإجابة الأولى' => false,
                    'الإجابة الصحيحة' => true,
                    'الإجابة الثالثة' => false,
                ]);
            }
        }

        if (!WebinarAssignment::where('webinar_id', $webinar->id)->exists()) {
            WebinarAssignment::create([
                'creator_id' => $teacherId,
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapterId,
                'grade' => 100,
                'pass_grade' => 50,
                'attempts' => 1,
                'status' => 'active',
                'created_at' => $now,
            ]);
        }
    }

    private function seedQuizQuestion(int $quizId, int $teacherId, string $titleAr, int $grade, int $now, array $answers): void
    {
        $question = QuizzesQuestion::create([
            'quiz_id' => $quizId,
            'creator_id' => $teacherId,
            'grade' => $grade,
            'type' => 'multiple',
            'order' => QuizzesQuestion::where('quiz_id', $quizId)->count() + 1,
            'created_at' => $now,
        ]);

        DB::table('quiz_question_translations')->updateOrInsert([
            'quizzes_question_id' => $question->id,
            'locale' => 'ar',
        ], ['title' => $titleAr, 'correct' => null]);

        $this->seedQuizAnswers($question->id, $teacherId, $answers, $now);
    }

    private function seedQuizAnswers(int $questionId, int $teacherId, array $answers, int $now): void
    {
        foreach ($answers as $titleAr => $correct) {
            $answer = QuizzesQuestionsAnswer::create([
                'question_id' => $questionId,
                'creator_id' => $teacherId,
                'correct' => $correct ? 1 : 0,
                'created_at' => $now,
            ]);

            DB::table('quizzes_questions_answer_translations')->updateOrInsert([
                'quizzes_questions_answer_id' => $answer->id,
                'locale' => 'ar',
            ], ['title' => $titleAr]);
        }
    }
}
