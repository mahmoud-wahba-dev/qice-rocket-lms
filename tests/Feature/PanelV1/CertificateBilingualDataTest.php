<?php

namespace Tests\Feature\PanelV1;

use App\Http\Controllers\PanelV1\InstructorController;
use App\Http\Controllers\PanelV1\StudentController;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Certificate;
use App\Models\UserMeta;
use App\Models\Webinar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class CertificateBilingualDataTest extends TestCase
{
    use DatabaseTransactions;

    private function makeTeacher(): User
    {
        return User::create([
            'full_name' => 'Cert Teacher',
            'email' => 'cert_t_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'teacher',
            'role_id' => 4,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function makeStudent(): User
    {
        return User::create([
            'full_name' => 'طالب تجريبي',
            'email' => 'cert_s_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'user',
            'role_id' => 1,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function seedCourse(User $teacher): Webinar
    {
        $webinar = new Webinar();
        $webinar->teacher_id = $teacher->id;
        $webinar->creator_id = $teacher->id;
        $webinar->type = 'course';
        $webinar->status = 'active';
        $webinar->slug = 'cert-bi-' . time() . '-' . rand(1000, 9999);
        $webinar->certificate = 0;
        $webinar->created_at = time();
        $webinar->updated_at = time();
        $webinar->save();

        $tr = $webinar->translateOrNew('ar');
        $tr->locale = 'ar';
        $tr->title = 'دورة الجودة';
        $tr->save();

        return $webinar->fresh(['translations']);
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

    public function test_wizard_step3_saves_bilingual_course_titles_when_certificate_enabled(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher);
        $ctrl = new InstructorController();

        $accNo = 'QIEC-ACC-T' . $course->id;
        $response = $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 3,
            'draft_id' => $course->id,
            'go_next' => 4,
            'certificate' => 1,
            'certificate_title_ar' => 'إدارة الجودة الشاملة',
            'certificate_title_en' => 'Total Quality Management',
            'certificate_accredited_by_ar' => 'مركز الجودة والتميز',
            'certificate_accredited_by_en' => 'Quality & Excellence Center',
            'certificate_accreditation_number' => $accNo,
        ], $teacher));

        $this->assertTrue($response->getData(true)['ok']);
        $fresh = $course->fresh(['translations']);
        $this->assertTrue((bool) $fresh->certificate);
        $this->assertSame('إدارة الجودة الشاملة', (string) optional($fresh->translate('ar'))->title);
        $this->assertSame('Total Quality Management', (string) optional($fresh->translate('en'))->title);
        $this->assertSame('مركز الجودة والتميز', (string) $fresh->certificate_accredited_by_ar);
        $this->assertSame('Quality & Excellence Center', (string) $fresh->certificate_accredited_by_en);
        $this->assertSame($accNo, (string) $fresh->certificate_accreditation_number);
    }

    public function test_wizard_step3_rejects_duplicate_accreditation_number(): void
    {
        $teacher = $this->makeTeacher();
        $courseA = $this->seedCourse($teacher);
        $courseB = $this->seedCourse($teacher);
        $ctrl = new InstructorController();

        $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 3,
            'draft_id' => $courseA->id,
            'go_next' => 4,
            'certificate' => 1,
            'certificate_title_ar' => 'دورة أ',
            'certificate_title_en' => 'Course A',
            'certificate_accredited_by_ar' => 'مركز الجودة والتميز',
            'certificate_accredited_by_en' => 'Quality & Excellence Center',
            'certificate_accreditation_number' => 'QIEC-ACC-DUP-1',
        ], $teacher));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 3,
            'draft_id' => $courseB->id,
            'go_next' => 4,
            'certificate' => 1,
            'certificate_title_ar' => 'دورة ب',
            'certificate_title_en' => 'Course B',
            'certificate_accredited_by_ar' => 'مركز الجودة والتميز',
            'certificate_accredited_by_en' => 'Quality & Excellence Center',
            'certificate_accreditation_number' => 'QIEC-ACC-DUP-1',
        ], $teacher));
    }

    public function test_wizard_step3_requires_titles_when_certificate_enabled(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher);
        $ctrl = new InstructorController();

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $ctrl->storeCourse($this->ajaxPost('/v1/instructor/courses/store', [
            'wizard_step' => 3,
            'draft_id' => $course->id,
            'go_next' => 4,
            'certificate' => 1,
        ], $teacher));
    }

    public function test_complete_certificate_names_saves_meta_and_user_metas(): void
    {
        $student = $this->makeStudent();
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher);
        $course->certificate = 1;
        $course->save();

        $certificate = Certificate::create([
            'type' => 'course',
            'student_id' => $student->id,
            'webinar_id' => $course->id,
            'created_at' => time(),
        ]);

        $this->assertTrue($certificate->needsTraineeNames($student));

        $ctrl = new StudentController();
        $req = $this->ajaxPost('/v1/student/certificates/' . $certificate->id . '/complete-names', [
            'trainee_name_ar' => 'محمد العتيبي',
            'trainee_name_en' => 'Mohammed Alotaibi',
        ], $student);

        $response = $ctrl->completeCertificateNames($req, $certificate->id);
        $payload = $response->getData(true);

        $this->assertTrue($payload['ok']);
        $this->assertNotEmpty($payload['download_url'] ?? null);

        $fresh = $certificate->fresh();
        $this->assertSame('محمد العتيبي', $fresh->meta['trainee_name_ar'] ?? null);
        $this->assertSame('Mohammed Alotaibi', $fresh->meta['trainee_name_en'] ?? null);
        $this->assertFalse($fresh->needsTraineeNames($student));

        $this->assertDatabaseHas('users_metas', [
            'user_id' => $student->id,
            'name' => 'full_name_ar',
            'value' => 'محمد العتيبي',
        ]);
        $this->assertDatabaseHas('users_metas', [
            'user_id' => $student->id,
            'name' => 'full_name_en',
            'value' => 'Mohammed Alotaibi',
        ]);
    }

    public function test_download_redirects_when_trainee_names_missing(): void
    {
        $student = $this->makeStudent();
        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher);

        $certificate = Certificate::create([
            'type' => 'course',
            'student_id' => $student->id,
            'webinar_id' => $course->id,
            'created_at' => time(),
        ]);

        $ctrl = new StudentController();
        $req = Request::create('/v1/student/certificates/' . $certificate->id . '/download', 'GET');
        $req->setUserResolver(fn () => $student);

        $response = $ctrl->downloadCertificate($req, $certificate->id);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('open_cert_names=' . $certificate->id, $response->headers->get('Location'));
    }

    public function test_build_qiec_data_uses_user_metas_fallback(): void
    {
        $student = $this->makeStudent();
        UserMeta::create(['user_id' => $student->id, 'name' => 'full_name_ar', 'value' => 'سارة أحمد']);
        UserMeta::create(['user_id' => $student->id, 'name' => 'full_name_en', 'value' => 'Sara Ahmed']);

        $teacher = $this->makeTeacher();
        $course = $this->seedCourse($teacher);
        $trEn = $course->translateOrNew('en');
        $trEn->locale = 'en';
        $trEn->title = 'Quality Course';
        $trEn->save();

        $course->certificate_accredited_by_ar = 'مركز الجودة والتميز';
        $course->certificate_accredited_by_en = 'Quality & Excellence Center';
        $course->certificate_accreditation_number = 'QIEC-ACC-META-' . $course->id;
        $course->save();

        $certificate = Certificate::create([
            'type' => 'course',
            'student_id' => $student->id,
            'webinar_id' => $course->id,
            'created_at' => time(),
        ]);

        $make = new MakeCertificate();
        $data = $make->buildQiecCertificateData($certificate->fresh(['student.userMetas', 'webinar.translations']));

        $this->assertSame('سارة أحمد', $data['trainee_name_ar']);
        $this->assertSame('Sara Ahmed', $data['trainee_name_en']);
        $this->assertSame('دورة الجودة', $data['course_title_ar']);
        $this->assertSame('Quality Course', $data['course_title_en']);
        $this->assertSame('مركز الجودة والتميز', $data['accredited_by_ar']);
        $this->assertSame('Quality & Excellence Center', $data['accredited_by_en']);
        $this->assertSame('QIEC-ACC-META-' . $course->id, $data['accreditation_number']);
    }
}
