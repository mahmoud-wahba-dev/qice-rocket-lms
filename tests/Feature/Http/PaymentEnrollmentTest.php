<?php

namespace Tests\Feature\Http;

use App\Http\Controllers\Web\PaymentController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Order;
use App\Models\Sale;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;
use Tests\TestCase;

class PaymentEnrollmentTest extends TestCase
{
    use DatabaseTransactions;

    private function makeStudent(): User
    {
        return User::create([
            'full_name' => 'Payment Test Student',
            'email' => 'pay_test_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'user',
            'role_id' => 1,
            'password' => bcrypt('secret'),
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function makeOrder(User $user, string $status = 'paying'): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'status' => $status,
            'amount' => 100,
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' => 100,
            'payment_method' => Order::$paymentChannel,
            'created_at' => time(),
        ]);
    }

    public function test_set_payment_accounting_is_idempotent_when_sale_exists(): void
    {
        $user = $this->makeStudent();
        $order = $this->makeOrder($user, Order::$paying);

        Sale::create([
            'buyer_id' => $user->id,
            'seller_id' => $user->id,
            'order_id' => $order->id,
            'type' => Order::$webinar,
            'payment_method' => Order::$paymentChannel,
            'amount' => 100,
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'total_amount' => 100,
            'created_at' => time(),
        ]);

        $controller = new PaymentController();
        $controller->setPaymentAccounting($order);

        $this->assertSame(1, Sale::where('order_id', $order->id)->count());
    }

    public function test_payment_status_blade_uses_panel_v1_home_url(): void
    {
        $blade = file_get_contents(resource_path('views/landing_v1/pages/payment-status.blade.php'));

        $this->assertStringContainsString('panelV1HomeUrl(auth()->user())', $blade);
        $this->assertStringNotContainsString('href="/panel"', $blade);
        $this->assertStringContainsString('landing_v1.layouts.app', $blade);
    }

    public function test_payment_status_fallback_redirects_to_v1_home(): void
    {
        $user = $this->makeStudent();
        Auth::login($user);

        $controller = new PaymentController();
        $response = $controller->payStatus(Request::create('/payments/status?t=999999991', 'GET'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(route('panel.v1.student.home'), $response->headers->get('Location'));
    }

    public function test_csrf_except_includes_tamara_tabby_stripe(): void
    {
        $middleware = new VerifyCsrfToken(app(), app('encrypter'));
        $ref = new ReflectionClass($middleware);
        $prop = $ref->getProperty('except');
        $prop->setAccessible(true);
        $except = $prop->getValue($middleware);

        $this->assertContains('/payments/verify/Tamara', $except);
        $this->assertContains('/payments/verify/Tabby', $except);
        $this->assertContains('/payments/verify/Stripe', $except);
    }
}
