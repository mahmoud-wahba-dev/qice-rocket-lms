<?php

namespace Tests\Feature\PanelV1;

use App\Http\Controllers\PanelV1\CoursePlayerController;
use App\Http\Controllers\PanelV1\InstructorController;
use App\Http\Controllers\PanelV1\OrganizationController;
use App\Http\Controllers\PanelV1\StudentController;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;


class StudentGuardTest extends GuardTestCase
{
    public function test_guest_is_sent_to_login(): void
    {
        $result = $this->callPrivate(new StudentController(), 'resolveStudent', [$this->requestAs(null)]);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/login', $result->getTargetUrl());
    }

    public function test_teacher_is_sent_to_instructor_home(): void
    {
        $result = $this->callPrivate(
            new StudentController(), 'resolveStudent', [$this->requestAs($this->makeUser('teacher', 4))]
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(route('panel.v1.instructor.home'), $result->getTargetUrl());
    }

    public function test_admin_is_sent_to_admin_home(): void
    {
        $result = $this->callPrivate(
            new StudentController(), 'resolveStudent', [$this->requestAs($this->makeUser('admin', 2))]
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(route('panel.v1.admin.home'), $result->getTargetUrl());
    }

    public function test_student_passes_through(): void
    {
        $user = $this->makeUser('user', 1);
        $result = $this->callPrivate(new StudentController(), 'resolveStudent', [$this->requestAs($user)]);

        $this->assertSame($user->id, $result->id);
    }
}

