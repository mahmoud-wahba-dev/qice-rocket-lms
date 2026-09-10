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


class InstructorGuardTest extends GuardTestCase
{
    public function test_guest_is_sent_to_login(): void
    {
        $result = $this->callPrivate(new InstructorController(), 'resolveInstructor', [$this->requestAs(null)]);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function test_student_is_sent_to_student_home(): void
    {
        $result = $this->callPrivate(
            new InstructorController(), 'resolveInstructor', [$this->requestAs($this->makeUser('user', 1))]
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(route('panel.v1.student.home'), $result->getTargetUrl());
    }

    public function test_teacher_passes_through(): void
    {
        $user = $this->makeUser('teacher', 4);
        $result = $this->callPrivate(new InstructorController(), 'resolveInstructor', [$this->requestAs($user)]);

        $this->assertSame($user->id, $result->id);
    }
}

