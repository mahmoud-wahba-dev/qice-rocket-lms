<?php

namespace Tests\Unit\PanelV1;

use App\Http\Controllers\PanelV1\Support\CourseWizardTrait;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Webinar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

/**
 * Unit coverage for CourseWizardTrait — ownership, view payload, status rules.
 */
class CourseWizardTraitTest extends TestCase
{
    use DatabaseTransactions;

    private function harness()
    {
        return new class {
            use CourseWizardTrait;

            public function callWizardWebinarOrFail($user, $id)
            {
                return $this->wizardWebinarOrFail($user, $id);
            }

            public function callBuild(Request $request, $draft, int $step, $user): array
            {
                return $this->buildCourseWizardViewData($request, $draft, $step, $user);
            }

            public function callPersist(Request $request, $user, $draft, array $opts = []): array
            {
                return $this->persistCourseWizardStep($request, $user, $draft, $opts);
            }

            public function callStepsMeta(): array
            {
                return $this->courseWizardStepsMeta();
            }

            public function callFieldNames(): array
            {
                return $this->courseWizardFieldNames();
            }
        };
    }

    private function makeTeacher(): User
    {
        return User::create([
            'full_name' => 'Wizard Unit Teacher',
            'email' => 'wiz_unit_t_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'teacher',
            'role_id' => 4,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function makeAdmin(): User
    {
        return User::create([
            'full_name' => 'Wizard Unit Admin',
            'email' => 'wiz_unit_a_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'admin',
            'role_id' => 2,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function makeCourse(User $teacher, string $status = 'pending', array $extra = []): Webinar
    {
        $webinar = new Webinar();
        $webinar->teacher_id = $teacher->id;
        $webinar->creator_id = $teacher->id;
        $webinar->type = $extra['type'] ?? 'course';
        $webinar->status = $status;
        $webinar->slug = 'wiz-unit-' . time() . '-' . rand(1000, 9999);
        $webinar->price = $extra['price'] ?? 100;
        $webinar->capacity = $extra['capacity'] ?? 20;
        $webinar->certificate = $extra['certificate'] ?? 0;
        $webinar->downloadable = $extra['downloadable'] ?? 0;
        $webinar->partner_instructor = $extra['partner_instructor'] ?? 0;
        $webinar->access_days = $extra['access_days'] ?? null;
        $webinar->category_id = $extra['category_id'] ?? null;
        $webinar->created_at = time();
        $webinar->updated_at = time();
        $webinar->save();

        $tr = $webinar->translateOrNew('ar');
        $tr->locale = 'ar';
        $tr->title = $extra['title'] ?? 'عنوان وحدة اختبار';
        $tr->seo_description = $extra['seo'] ?? 'وصف SEO قصير للاختبار';
        $tr->description = $extra['description'] ?? 'وصف تفصيلي';
        $tr->save();

        return $webinar->fresh(['translations', 'tags']);
    }

    public function test_wizard_exposes_all_five_steps_and_field_labels(): void
    {
        $h = $this->harness();
        $steps = $h->callStepsMeta();
        $this->assertCount(5, $steps);
        foreach ([1, 2, 3, 4, 5] as $n) {
            $this->assertArrayHasKey($n, $steps);
            $this->assertNotEmpty($steps[$n]['label']);
            $this->assertNotEmpty($steps[$n]['title']);
        }

        $fields = $h->callFieldNames();
        foreach ([
            'title', 'category_id', 'course_type', 'seo_description', 'description',
            'video_demo_link', 'tags', 'locale', 'downloadable', 'partner_instructor', 'partners',
            'quiz_id', 'certificate', 'price', 'capacity', 'access_duration', 'access_days',
            'confirm_rights', 'confirm_terms',
        ] as $field) {
            $this->assertArrayHasKey($field, $fields, "Missing wizard field label: {$field}");
        }
    }

    public function test_partner_teacher_can_load_shared_course(): void
    {
        $owner = $this->makeTeacher();
        $partner = $this->makeTeacher();
        $course = $this->makeCourse($owner, 'active');
        \App\Models\WebinarPartnerTeacher::create([
            'webinar_id' => $course->id,
            'teacher_id' => $partner->id,
        ]);

        $loaded = $this->harness()->callWizardWebinarOrFail($partner, $course->id);
        $this->assertSame($course->id, $loaded->id);
    }

    public function test_teacher_can_load_non_draft_owned_course(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher, 'pending');

        $loaded = $this->harness()->callWizardWebinarOrFail($teacher, $course->id);
        $this->assertSame($course->id, $loaded->id);
        $this->assertSame('pending', $loaded->status);
    }

    public function test_teacher_cannot_load_foreign_course(): void
    {
        $owner = $this->makeTeacher();
        $intruder = $this->makeTeacher();
        $course = $this->makeCourse($owner, 'active');

        $this->expectException(ModelNotFoundException::class);
        $this->harness()->callWizardWebinarOrFail($intruder, $course->id);
    }

    public function test_admin_can_load_any_course_regardless_of_teacher(): void
    {
        $teacher = $this->makeTeacher();
        $admin = $this->makeAdmin();
        $course = $this->makeCourse($teacher, 'active');

        $loaded = $this->harness()->callWizardWebinarOrFail($admin, $course->id);
        $this->assertSame($course->id, $loaded->id);
    }

    public function test_build_view_data_prefills_every_step_one_field(): void
    {
        $teacher = $this->makeTeacher();
        $category = Category::whereNull('parent_id')->first();
        $course = $this->makeCourse($teacher, 'pending', [
            'title' => 'دورة حقول كاملة',
            'seo' => 'ملخص SEO مميز',
            'description' => 'وصف كامل للاختبار',
            'category_id' => $category?->id,
            'type' => 'webinar',
            'downloadable' => 1,
            'partner_instructor' => 1,
            'price' => 250,
            'capacity' => 40,
            'certificate' => 1,
            'access_days' => 90,
        ]);
        Tag::create(['title' => 'جودة', 'webinar_id' => $course->id]);
        Tag::create(['title' => 'تميز', 'webinar_id' => $course->id]);
        $course = $course->fresh(['translations', 'tags']);

        $data = $this->harness()->callBuild(Request::create('/'), $course, 1, $teacher);

        $this->assertSame($course->id, $data['draftId']);
        $this->assertSame(1, $data['wizardStep']);
        $this->assertSame('دورة حقول كاملة', $data['draft']['title']);
        $this->assertSame('ملخص SEO مميز', $data['draft']['seo_description']);
        $this->assertSame('وصف كامل للاختبار', $data['draft']['description']);
        $this->assertSame('live', $data['draft']['course_type']);
        $this->assertTrue($data['draft']['downloadable']);
        $this->assertTrue($data['draft']['partner_instructor']);
        $this->assertSame([], $data['draft']['partners']);
        $this->assertArrayHasKey('availableInstructors', $data);
        $this->assertSame($category?->id, $data['draft']['category_id']);
        $this->assertStringContainsString('جودة', $data['draft']['tags']);
        $this->assertStringContainsString('تميز', $data['draft']['tags']);
        $this->assertSame(250, (int) $data['draftPrice']);
        $this->assertSame(40, (int) $data['draftCapacity']);
        $this->assertTrue($data['draftCertificate']);
        $this->assertSame(90, (int) $data['draftAccessDays']);
        $this->assertArrayHasKey('curriculumUnits', $data);
        $this->assertArrayHasKey('teacherQuizzes', $data);
        $this->assertArrayHasKey('categories', $data);
    }

    public function test_step5_done_preserves_active_status(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher, 'active');

        $request = Request::create('/store', 'POST', [
            'wizard_step' => 5,
            'draft_id' => $course->id,
            'go_next' => 'done',
            'confirm_rights' => '1',
            'confirm_terms' => '1',
        ]);
        $request->setUserResolver(fn () => $teacher);

        $result = $this->harness()->callPersist($request, $teacher, $course->fresh(), ['allowCreate' => false]);

        $this->assertTrue($result['isDone']);
        $this->assertSame('active', $result['draft']->fresh()->status);
        $this->assertSame('تم حفظ التعديلات', $result['doneMessage']);
    }

    public function test_step5_done_sets_pending_for_draft(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher, 'is_draft');

        $request = Request::create('/store', 'POST', [
            'wizard_step' => 5,
            'draft_id' => $course->id,
            'go_next' => 'done',
            'confirm_rights' => '1',
            'confirm_terms' => '1',
        ]);
        $request->setUserResolver(fn () => $teacher);

        $result = $this->harness()->callPersist($request, $teacher, $course->fresh(), ['allowCreate' => false]);

        $this->assertTrue($result['isDone']);
        $this->assertSame('pending', $result['draft']->fresh()->status);
        $this->assertSame('تم إرسال الدورة للمراجعة', $result['doneMessage']);
    }

    public function test_intermediate_save_does_not_change_status(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher, 'pending');

        $request = Request::create('/store', 'POST', [
            'wizard_step' => 4,
            'draft_id' => $course->id,
            'go_next' => 'stay',
            'price' => 333,
            'capacity' => 12,
            'access_duration' => 'lifetime',
        ]);
        $request->setUserResolver(fn () => $teacher);

        $result = $this->harness()->callPersist($request, $teacher, $course->fresh(), ['allowCreate' => false]);

        $this->assertFalse($result['isDone']);
        $fresh = $result['draft']->fresh();
        $this->assertSame('pending', $fresh->status);
        $this->assertSame(333, (int) $fresh->price);
        $this->assertSame(12, (int) $fresh->capacity);
        $this->assertNull($fresh->access_days);
    }
}
