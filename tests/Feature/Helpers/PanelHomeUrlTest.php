<?php

namespace Tests\Feature\Helpers;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PanelHomeUrlTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $roleName, int $roleId): User
    {
        return User::create([
            'full_name' => 'Test ' . $roleName,
            'email' => $roleName . time() . rand(1000, 9999) . '@test.local',
            'role_name' => $roleName,
            'role_id' => $roleId,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    public function test_guest_gets_legacy_panel_url(): void
    {
        $this->assertSame('/panel', panelV1HomeUrl());
        $this->assertSame('/panel', panelV1HomeUrl(null));
    }

    public function test_student_gets_student_home(): void
    {
        $user = $this->makeUser('user', 1);

        $this->assertSame(route('panel.v1.student.home'), panelV1HomeUrl($user));
    }

    public function test_teacher_gets_instructor_home(): void
    {
        $user = $this->makeUser('teacher', 4);

        $this->assertSame(route('panel.v1.instructor.home'), panelV1HomeUrl($user));
    }

    public function test_organization_gets_organization_home(): void
    {
        $user = $this->makeUser('organization', 3);

        $this->assertSame(route('panel.v1.organization.home'), panelV1HomeUrl($user));
    }

    public function test_admin_keeps_legacy_admin_url(): void
    {
        $user = $this->makeUser('admin', 2);

        $this->assertSame(getAdminPanelUrl('/'), panelV1HomeUrl($user));
    }
}
