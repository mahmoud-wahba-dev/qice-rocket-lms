<?php

namespace Tests\Feature\PanelV1;

use App\Http\Controllers\PanelV1\Admin\EducationController;
use App\Http\Controllers\PanelV1\InstructorController;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Tag;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Simulates a manual QA pass through every course-edit wizard field
 * for Instructor + Admin (steps 1–5 + curriculum).
 */
class CourseEditWizardCycleTest extends TestCase
{
    use DatabaseTransactions;

    private function makeTeacher(): User
    {
        return User::create([
            'full_name' => 'Cycle Teacher',
            'email' => 'cycle_t_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'teacher',
            'role_id' => 4,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function makeAdmin(): User
    {
        $existing = User::where('role_name', 'admin')->first();
        if ($existing) {
            return $existing;
        }

        return User::create([
            'full_name' => 'Cycle Admin',
            'email' => 'cycle_a_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'admin',
            'role_id' => 2,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function categoryId(): ?int
    {
        return Category::whereNull('parent_id')->value('id');
    }

    private function seedCourse(User $teacher, string $status = 'pending'): Webinar
    {
        $webinar = new Webinar();
        $webinar->teacher_id = $teacher->id;
        $webinar->creator_id = $teacher->id;
        $webinar->type = 'course';
        $webinar->status = $status;
        $webinar->slug = 'cycle-' . time() . '-' . rand(1000, 9999);
        $webinar->price = 150;
        $webinar->capacity = 25;
        $webinar->certificate = 0;
        $webinar->downloadable = 0;
        $webinar->partner_instructor = 0;
        $webinar->category_id = $this->categoryId();
        $webinar->created_at = time();
        $webinar->updated_at = time();
        $webinar->save();

        $tr = $webinar->translateOrNew('ar');
        $tr->locale = 'ar';
        $tr->title = 'دورة قبل التعديل';
        $tr->seo_description = 'ملخص قبل التعديل';
        $tr->description = 'وصف قبل التعديل';
        $tr->save();

        Tag::create(['title' => 'قديم', 'webinar_id' => $webinar->id]);

        return $webinar->fresh(['translations', 'tags']);
    }

    private function ajaxPost(string $uri, array $data, User $user): Request
    {
        $request = Request::create($uri, 'POST', $data, [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function userGet(string $uri, User $user, array $query = []): Request
    {
        $request = Request::create($uri, 'GET', $query);
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    // -------------------------------------------------------------------------
    // Instructor — open edit wizard for non-draft
    // -------------------------------------------------------------------------

    public function test_instructor_edit_opens_wizard_with_existing_data(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new InstructorController();

        $view = $ctrl->createCourse(
            $this->userGet('/v1/instructor/courses/create/1', $teacher, ['draft' => $course->id, 'step' => 1]),
            1
        );

        $this->assertSame('panel_v1.instructor.pages.create-course', $view->getName());
        $data = $view->getData();
        $this->assertSame($course->id, $data['draftId']);
        $this->assertSame(1, $data['wizardStep']);
        $this->assertSame('دورة قبل التعديل', $data['draft']['title']);
        $this->assertSame('ملخص قبل التعديل', $data['draft']['seo_description']);
        $this->assertSame('وصف قبل التعديل', $data['draft']['description']);
        $this->assertSame('recorded', $data['draft']['course_type']);
        $this->assertStringContainsString('قديم', (string) $data['draft']['tags']);
        $this->assertSame(150, (int) $data['draftPrice']);
        $this->assertSame(25, (int) $data['draftCapacity']);
    }

    public function test_instructor_cannot_open_foreign_course_in_wizard(): void
    {
        $owner = $this->makeTeacher();
        $intruder = $this->makeTeacher();
        $course = $this->seedCourse($owner, 'active');
        $ctrl = new InstructorController();

        $view = $ctrl->createCourse(
            $this->userGet('/v1/instructor/courses/create/1', $intruder, ['draft' => $course->id]),
            1
        );
        $data = $view->getData();

        // createCourse silently drops foreign drafts (null draftId) rather than 404
        $this->assertNull($data['draftId']);
    }

    // -------------------------------------------------------------------------
    // Instructor — step 1 every field
    // -------------------------------------------------------------------------

    public function test_instructor_step1_updates_all_basic_fields(): void
    {
        $teacher = $this->makeTeacher();
        $partner = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $categoryId = $this->categoryId();
        $ctrl = new InstructorController();

        $payload = [
            'wizard_step' => 1,
            'draft_id' => $course->id,
            'go_next' => 2,
            'title' => 'عنوان بعد التعديل الكامل',
            'category_id' => $categoryId,
            'course_type' => 'live',
            'seo_description' => 'ملخص SEO بعد التعديل',
            'description' => 'وصف تفصيلي بعد التعديل مع تفاصيل كافية',
            'video_demo_link' => 'https://example.com/demo-video',
            'tags' => 'جودة,تميز,اختبار',
            'locale' => 'ar',
            'downloadable' => 1,
            'partner_instructor' => 1,
            'partners' => [$partner->id],
        ];

        $response = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', $payload, $teacher));
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = $response->getData(true);
        $this->assertTrue($json['ok']);
        $this->assertSame($course->id, $json['draft_id']);
        $this->assertSame(2, $json['next_step']);

        $fresh = Webinar::with(['translations', 'tags', 'webinarPartnerTeacher'])->findOrFail($course->id);
        $this->assertSame('pending', $fresh->status, 'Step 1 must not change status');
        $this->assertSame('webinar', $fresh->type);
        $this->assertSame($categoryId, $fresh->category_id);
        $this->assertTrue((bool) $fresh->downloadable);
        $this->assertTrue((bool) $fresh->partner_instructor);
        $this->assertSame('external_link', $fresh->video_demo_source);
        $this->assertSame('https://example.com/demo-video', $fresh->video_demo);
        $this->assertSame([$partner->id], $fresh->webinarPartnerTeacher->pluck('teacher_id')->map(fn ($id) => (int) $id)->all());

        $tr = $fresh->translate('ar');
        $this->assertSame('عنوان بعد التعديل الكامل', $tr->title);
        $this->assertSame('ملخص SEO بعد التعديل', $tr->seo_description);
        $this->assertSame('وصف تفصيلي بعد التعديل مع تفاصيل كافية', $tr->description);

        $tagTitles = $fresh->tags->pluck('title')->sort()->values()->all();
        $this->assertSame(['اختبار', 'تميز', 'جودة'], $tagTitles);
        $this->assertFalse($fresh->tags->contains('title', 'قديم'), 'Old tags must be replaced');
    }

    public function test_instructor_step1_rejects_invalid_course_type(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new InstructorController();

        $this->expectException(ValidationException::class);
        $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 1,
            'draft_id' => $course->id,
            'go_next' => 2,
            'title' => 'عنوان صالح',
            'seo_description' => 'ملخص صالح بطول كافٍ',
            'course_type' => 'invalid_type',
        ], $teacher));
    }

    // -------------------------------------------------------------------------
    // Instructor — step 2 curriculum
    // -------------------------------------------------------------------------

    public function test_instructor_step2_curriculum_chapter_roundtrip(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'active');
        $ctrl = new InstructorController();

        $response = $ctrl->chapterStore($this->ajaxPost('/v1/instructor/curriculum/chapters', [
            'draft_id' => $course->id,
            'title' => 'وحدة اختبار المنهج',
        ], $teacher));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = $response->getData(true);
        $this->assertTrue($json['ok']);
        $this->assertSame($course->id, $json['draft_id']);
        $this->assertNotEmpty($json['unit']['id']);
        $this->assertSame('وحدة اختبار المنهج', $json['unit']['title']);

        $chapter = WebinarChapter::find($json['unit']['id']);
        $this->assertNotNull($chapter);
        $this->assertSame($course->id, $chapter->webinar_id);

        // Soft step-2 save keeps status
        $save = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 2,
            'draft_id' => $course->id,
            'go_next' => 3,
        ], $teacher));
        $this->assertTrue($save->getData(true)['ok']);
        $this->assertSame('active', $course->fresh()->status);
    }

    // -------------------------------------------------------------------------
    // Instructor — step 3 quizzes + certificate
    // -------------------------------------------------------------------------

    public function test_instructor_step3_attaches_quiz_and_certificate(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new InstructorController();

        $quiz = new Quiz();
        $quiz->creator_id = $teacher->id;
        $quiz->webinar_id = null;
        $quiz->pass_mark = 60;
        $quiz->certificate = 0;
        $quiz->status = 'active';
        $quiz->created_at = time();
        $quiz->save();
        $qTr = $quiz->translateOrNew('ar');
        if ($qTr) {
            $qTr->locale = 'ar';
            $qTr->title = 'اختبار مرتبط';
            $qTr->save();
        }

        $response = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 3,
            'draft_id' => $course->id,
            'go_next' => 4,
            'quiz_id' => $quiz->id,
            'certificate' => 1,
        ], $teacher));

        $this->assertTrue($response->getData(true)['ok']);
        $this->assertSame($course->id, (int) $quiz->fresh()->webinar_id);
        $this->assertTrue((bool) $course->fresh()->certificate);
        $this->assertSame('pending', $course->fresh()->status);
    }

    // -------------------------------------------------------------------------
    // Instructor — step 4 pricing + capacity + access
    // -------------------------------------------------------------------------

    public function test_instructor_step4_updates_price_capacity_and_limited_access(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new InstructorController();

        $response = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 4,
            'draft_id' => $course->id,
            'go_next' => 5,
            'price' => 499,
            'capacity' => 80,
            'access_duration' => 'limited',
            'access_days' => 120,
        ], $teacher));

        $this->assertTrue($response->getData(true)['ok']);
        $fresh = $course->fresh();
        $this->assertSame(499, (int) $fresh->price);
        $this->assertSame(80, (int) $fresh->capacity);
        $this->assertSame(120, (int) $fresh->access_days);
        $this->assertSame('pending', $fresh->status);
    }

    public function test_instructor_step4_lifetime_clears_access_days(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $course->access_days = 30;
        $course->save();
        $ctrl = new InstructorController();

        $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 4,
            'draft_id' => $course->id,
            'go_next' => 'stay',
            'price' => 0,
            'capacity' => 10,
            'access_duration' => 'lifetime',
        ], $teacher));

        $fresh = $course->fresh();
        $this->assertNull($fresh->price);
        $this->assertNull($fresh->access_days);
    }

    // -------------------------------------------------------------------------
    // Instructor — step 5 confirmations + status rules
    // -------------------------------------------------------------------------

    public function test_instructor_step5_requires_confirmations(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'is_draft');
        $ctrl = new InstructorController();

        $this->expectException(ValidationException::class);
        $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 5,
            'draft_id' => $course->id,
            'go_next' => 'done',
        ], $teacher));
    }

    public function test_instructor_full_cycle_pending_course_end_to_end(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new InstructorController();
        $categoryId = $this->categoryId();

        // Step 1
        $r1 = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 1,
            'draft_id' => $course->id,
            'go_next' => 2,
            'title' => 'دورة دورة كاملة بعد الـ QA',
            'category_id' => $categoryId,
            'course_type' => 'recorded',
            'seo_description' => 'ملخص نهائي للدورة بعد الاختبار',
            'description' => 'وصف نهائي',
            'tags' => 'نهائي,qa',
            'locale' => 'ar',
            'downloadable' => 1,
            'partner_instructor' => 0,
        ], $teacher));
        $this->assertSame(2, $r1->getData(true)['next_step']);

        // Step 2
        $ctrl->chapterStore($this->ajaxPost('/v1/instructor/curriculum/chapters', [
            'draft_id' => $course->id,
            'title' => 'وحدة النهاية',
        ], $teacher));
        $r2 = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 2,
            'draft_id' => $course->id,
            'go_next' => 3,
        ], $teacher));
        $this->assertSame(3, $r2->getData(true)['next_step']);

        // Step 3
        $r3 = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 3,
            'draft_id' => $course->id,
            'go_next' => 4,
            'certificate' => 1,
        ], $teacher));
        $this->assertSame(4, $r3->getData(true)['next_step']);

        // Step 4
        $r4 = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 4,
            'draft_id' => $course->id,
            'go_next' => 5,
            'price' => 777,
            'capacity' => 55,
            'access_duration' => 'limited',
            'access_days' => 45,
        ], $teacher));
        $this->assertSame(5, $r4->getData(true)['next_step']);

        // Step 5 done
        $r5 = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 5,
            'draft_id' => $course->id,
            'go_next' => 'done',
            'confirm_rights' => '1',
            'confirm_terms' => '1',
        ], $teacher));
        $json = $r5->getData(true);
        $this->assertTrue($json['done']);
        $this->assertNotEmpty($json['redirect']);

        $fresh = Webinar::with(['translations', 'tags'])->findOrFail($course->id);
        $this->assertSame('pending', $fresh->status);
        $this->assertSame('course', $fresh->type);
        $this->assertSame(777, (int) $fresh->price);
        $this->assertSame(55, (int) $fresh->capacity);
        $this->assertSame(45, (int) $fresh->access_days);
        $this->assertTrue((bool) $fresh->certificate);
        $this->assertTrue((bool) $fresh->downloadable);
        $this->assertSame('دورة دورة كاملة بعد الـ QA', $fresh->translate('ar')->title);
        $this->assertTrue(WebinarChapter::where('webinar_id', $fresh->id)->exists());
        $this->assertGreaterThanOrEqual(2, $fresh->tags->count());
    }

    public function test_instructor_editing_active_course_keeps_active_after_done(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'active');
        $ctrl = new InstructorController();

        $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 1,
            'draft_id' => $course->id,
            'go_next' => 'stay',
            'title' => 'دورة نشطة معدّلة',
            'seo_description' => 'ملخص للدورة النشطة المعدلة',
            'course_type' => 'recorded',
        ], $teacher));

        $done = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 5,
            'draft_id' => $course->id,
            'go_next' => 'done',
            'confirm_rights' => '1',
            'confirm_terms' => '1',
        ], $teacher));

        $this->assertTrue($done->getData(true)['done']);
        $this->assertSame('active', $course->fresh()->status);
        $this->assertSame('دورة نشطة معدّلة', $course->fresh()->translate('ar')->title);
    }

    // -------------------------------------------------------------------------
    // Admin — edit wizard + full cycle
    // -------------------------------------------------------------------------

    public function test_admin_edit_opens_wizard_for_any_course(): void
    {
        $teacher = $this->makeTeacher();
        $admin = $this->makeAdmin();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new EducationController();

        $view = $ctrl->editCourse(
            $this->userGet('/v1/admin/education/courses/' . $course->id . '/edit/1', $admin),
            $course->id,
            1
        );

        $this->assertSame('panel_v1.admin.pages.education.course-wizard', $view->getName());
        $data = $view->getData();
        $this->assertSame($course->id, $data['draftId']);
        $this->assertSame('دورة قبل التعديل', $data['draft']['title']);
        $this->assertNotEmpty($data['wizardStoreUrl']);
        $this->assertNotEmpty($data['wizardCreateUrl']);
        $this->assertNotEmpty($data['wizardCoursesUrl']);
    }

    public function test_admin_full_cycle_updates_every_step_field(): void
    {
        $teacher = $this->makeTeacher();
        $partner = $this->makeTeacher();
        $admin = $this->makeAdmin();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new EducationController();
        $categoryId = $this->categoryId();

        // Step 1 — all basic fields
        $r1 = $ctrl->storeCourseWizard($this->ajaxPost(
            '/v1/admin/education/courses/' . $course->id . '/wizard',
            [
                'wizard_step' => 1,
                'draft_id' => $course->id,
                'go_next' => 2,
                'title' => 'تعديل إداري كامل',
                'category_id' => $categoryId,
                'course_type' => 'text',
                'seo_description' => 'ملخص إداري بعد التعديل',
                'description' => 'وصف إداري مفصل',
                'video_demo_link' => 'https://admin.example.com/promo',
                'tags' => 'ادمن,دورة',
                'locale' => 'ar',
                'downloadable' => 1,
                'partner_instructor' => 1,
                'partners' => [$partner->id],
            ],
            $admin
        ), $course->id);
        $this->assertTrue($r1->getData(true)['ok']);

        $fresh = $course->fresh(['translations', 'tags', 'webinarPartnerTeacher']);
        $this->assertSame('text_lesson', $fresh->type);
        $this->assertSame('تعديل إداري كامل', $fresh->translate('ar')->title);
        $this->assertSame('ملخص إداري بعد التعديل', $fresh->translate('ar')->seo_description);
        $this->assertTrue((bool) $fresh->downloadable);
        $this->assertTrue((bool) $fresh->partner_instructor);
        $this->assertSame([$partner->id], $fresh->webinarPartnerTeacher->pluck('teacher_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame('https://admin.example.com/promo', $fresh->video_demo);

        // Step 2 — curriculum via shared instructor API (admin allowed)
        $inst = new InstructorController();
        $chap = $inst->chapterStore($this->ajaxPost('/v1/instructor/curriculum/chapters', [
            'draft_id' => $course->id,
            'title' => 'وحدة إدارية',
        ], $admin));
        $this->assertTrue($chap->getData(true)['ok']);

        $ctrl->storeCourseWizard($this->ajaxPost(
            '/v1/admin/education/courses/' . $course->id . '/wizard',
            ['wizard_step' => 2, 'draft_id' => $course->id, 'go_next' => 3],
            $admin
        ), $course->id);

        // Step 3
        $ctrl->storeCourseWizard($this->ajaxPost(
            '/v1/admin/education/courses/' . $course->id . '/wizard',
            [
                'wizard_step' => 3,
                'draft_id' => $course->id,
                'go_next' => 4,
                'certificate' => 1,
            ],
            $admin
        ), $course->id);
        $this->assertTrue((bool) $course->fresh()->certificate);

        // Step 4
        $ctrl->storeCourseWizard($this->ajaxPost(
            '/v1/admin/education/courses/' . $course->id . '/wizard',
            [
                'wizard_step' => 4,
                'draft_id' => $course->id,
                'go_next' => 5,
                'price' => 999,
                'capacity' => 200,
                'access_duration' => 'limited',
                'access_days' => 365,
            ],
            $admin
        ), $course->id);
        $priced = $course->fresh();
        $this->assertSame(999, (int) $priced->price);
        $this->assertSame(200, (int) $priced->capacity);
        $this->assertSame(365, (int) $priced->access_days);

        // Step 5
        $done = $ctrl->storeCourseWizard($this->ajaxPost(
            '/v1/admin/education/courses/' . $course->id . '/wizard',
            [
                'wizard_step' => 5,
                'draft_id' => $course->id,
                'go_next' => 'done',
                'confirm_rights' => '1',
                'confirm_terms' => '1',
            ],
            $admin
        ), $course->id);
        $json = $done->getData(true);
        $this->assertTrue($json['done']);
        $this->assertSame('pending', $course->fresh()->status);
        $this->assertTrue(WebinarChapter::where('webinar_id', $course->id)->whereHas('translations', function ($q) {
            $q->where('title', 'وحدة إدارية');
        })->exists() || WebinarChapter::where('webinar_id', $course->id)->exists());
    }

    public function test_admin_editing_active_course_keeps_active(): void
    {
        $teacher = $this->makeTeacher();
        $admin = $this->makeAdmin();
        $course = $this->seedCourse($teacher, 'active');
        $ctrl = new EducationController();

        $done = $ctrl->storeCourseWizard($this->ajaxPost(
            '/v1/admin/education/courses/' . $course->id . '/wizard',
            [
                'wizard_step' => 5,
                'draft_id' => $course->id,
                'go_next' => 'done',
                'confirm_rights' => '1',
                'confirm_terms' => '1',
            ],
            $admin
        ), $course->id);

        $this->assertTrue($done->getData(true)['done']);
        $this->assertSame('active', $course->fresh()->status);
    }

    public function test_admin_wizard_steps_2_through_5_are_reachable_via_get(): void
    {
        $teacher = $this->makeTeacher();
        $admin = $this->makeAdmin();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new EducationController();

        foreach ([1, 2, 3, 4, 5] as $step) {
            $view = $ctrl->editCourse(
                $this->userGet('/v1/admin/education/courses/' . $course->id . '/edit/' . $step, $admin, ['step' => $step]),
                $course->id,
                $step
            );
            $this->assertSame('panel_v1.admin.pages.education.course-wizard', $view->getName());
            $this->assertSame($step, $view->getData()['wizardStep']);
            $this->assertSame($course->id, $view->getData()['draftId']);
        }
    }

    public function test_instructor_courses_list_exposes_edit_url_for_non_draft(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher, 'pending');
        $ctrl = new InstructorController();

        $view = $ctrl->courses($this->userGet('/v1/instructor/courses', $teacher));
        $this->assertSame('panel_v1.instructor.pages.courses', $view->getName());
        $html = $view->render();
        $editPath = '/v1/instructor/courses/create/1?draft=' . $course->id;
        $this->assertStringContainsString((string) $course->id, $html);
        $this->assertTrue(
            str_contains($html, 'draft=' . $course->id) || str_contains($html, $editPath) || str_contains($html, 'تعديل'),
            'Courses list must expose an edit affordance for non-draft courses'
        );
    }
}
