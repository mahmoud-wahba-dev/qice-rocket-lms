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


class CoursePlayerGuardTest extends GuardTestCase
{
    public function test_teacher_hitting_player_is_sent_to_instructor_home(): void
    {
        $controller = new CoursePlayerController();
        $ref = new \ReflectionMethod($controller, 'resolveStudent');
        $ref->setAccessible(true);
        $result = $ref->invoke($controller, $this->requestAs($this->makeUser('teacher', 4)));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(route('panel.v1.instructor.home'), $result->getTargetUrl());
    }
}
