<?php

namespace Tests\Feature\PanelV1;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

abstract class GuardTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function makeUser(string $roleName, int $roleId): User
    {
        return User::create([
            'full_name' => 'Guard Test',
            'email' => 'guard_' . $roleName . '_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => $roleName,
            'role_id' => $roleId,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    protected function requestAs(?User $user): Request
    {
        $request = Request::create('/v1/test', 'GET');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    protected function callPrivate(object $controller, string $method, array $args)
    {
        $ref = new \ReflectionMethod($controller, $method);
        $ref->setAccessible(true);

        return $ref->invokeArgs($controller, $args);
    }
}
