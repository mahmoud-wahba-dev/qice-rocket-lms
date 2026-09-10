<?php

namespace Tests\Feature\Models;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $roleName, int $roleId): User
    {
        return User::create([
            'full_name' => 'Role Test',
            'email' => 'role_' . $roleName . '_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => $roleName,
            'role_id' => $roleId,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    public function test_role_checks_match_role_name(): void
    {
        $this->assertTrue($this->makeUser('user', 1)->isUser());
        $this->assertTrue($this->makeUser('teacher', 4)->isTeacher());
        $this->assertTrue($this->makeUser('organization', 3)->isOrganization());
    }

    public function test_role_checks_are_exclusive(): void
    {
        $user = $this->makeUser('user', 1);

        $this->assertFalse($user->isTeacher());
        $this->assertFalse($user->isOrganization());
        $this->assertFalse($user->isAdmin());
    }

    public function test_admin_role_reports_admin(): void
    {
        $this->assertTrue($this->makeUser('admin', 2)->isAdmin());
    }

    public function test_is_admin_without_role_relation_returns_false(): void
    {
        $user = new User(['role_name' => 'user', 'role_id' => 999999]);

        // Must not throw "Attempt to read property on null".
        $this->assertFalse($user->isAdmin());
    }
}
