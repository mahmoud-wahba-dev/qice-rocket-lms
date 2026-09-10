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


class OrganizationGuardTest extends GuardTestCase
{
    public function test_non_organization_is_redirected_away(): void
    {
        $result = $this->callPrivate(
            new OrganizationController(), 'resolveOrganization', [$this->requestAs($this->makeUser('user', 1))]
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(route('panel.v1.student.home'), $result->getTargetUrl());
    }

    public function test_organization_passes_through(): void
    {
        $user = $this->makeUser('organization', 3);
        $result = $this->callPrivate(new OrganizationController(), 'resolveOrganization', [$this->requestAs($user)]);

        $this->assertSame($user->id, $result->id);
    }
}

