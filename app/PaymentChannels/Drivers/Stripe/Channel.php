<?php

namespace App\PaymentChannels\Drivers\Stripe;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $api_key;
    protected $api_secret;
    protected $order_session_key;


    protected array $credentialItems = [
        'api_key',
        'api_secret',
    ];

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        $this->order_session_key = 'strip.payments.order_id';
        $this->setCredentialItems($paymentChannel);
    }

    public function paymentRequest(Order $order)
    {
        $price = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $generalSettings = getGeneralSettings();
        $currency = currency();

        Stripe::setApiKey($this->api_secret);

        $checkout = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount_decimal' => $price * 100,
                    'product_data' => [
                        'name' => $generalSettings['site_name'] . ' payment',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'client_reference_id' => (string) $order->id,
            'metadata' => [
                'order_id' => (string) $order->id,
                'user_id' => (string) $order->user_id,
            ],
            'success_url' => $this->makeCallbackUrl('success'),
            'cancel_url' => $this->makeCallbackUrl('cancel'),
        ]);

        session()->put($this->order_session_key, $order->id);

        $Html = '<script src="https://js.stripe.com/v3/"></script>';
        $Html .= '<script type="text/javascript">let stripe = Stripe("' . $this->api_key . '");';
        $Html .= 'stripe.redirectToCheckout({ sessionId: "' . $checkout->id . '" }); </script>';

        echo $Html;
    }

    private function makeCallbackUrl($status)
    {
        return url("/payments/verify/Stripe?status=$status&session_id={CHECKOUT_SESSION_ID}");
    }

    public function verify(Request $request)
    {
        $status = $request->get('status');
        $sessionOrderId = session()->get($this->order_session_key, null);
        session()->forget($this->order_session_key);

        $order = null;

        try {
            if (!empty($request->session_id)) {
                Stripe::setApiKey($this->api_secret);
                $session = Session::retrieve($request->session_id);

                $orderId = $session->metadata->order_id
                    ?? $session->client_reference_id
                    ?? $sessionOrderId;

                if (!empty($orderId)) {
                    $order = Order::where('id', $orderId)->first();
                }

                if (!empty($order)) {
                    if ($order->status === Order::$paid) {
                        $this->ensureBuyerLoggedIn($order);
                        return $order;
                    }

                    if ($status === 'success' && $session->payment_status === 'paid') {
                        $this->ensureBuyerLoggedIn($order);
                        $order->update(['status' => Order::$paying]);
                        return $order;
                    }

                    if (!in_array($order->status, [Order::$paid, Order::$paying], true)) {
                        $order->update(['status' => Order::$fail]);
                    }
                } else {
                    \Log::error('Stripe verify: local order not found', [
                        'session_id' => $request->session_id,
                        'resolved_order_id' => $orderId ?? null,
                        'session_order_id' => $sessionOrderId,
                    ]);
                }
            } elseif (!empty($sessionOrderId) && Auth::check()) {
                $order = Order::where('id', $sessionOrderId)
                    ->where('user_id', Auth::id())
                    ->first();

                if (!empty($order) && !in_array($order->status, [Order::$paid, Order::$paying], true)) {
                    $order->update(['status' => Order::$fail]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Stripe verify exception', [
                'message' => $e->getMessage(),
                'session_id' => $request->session_id,
            ]);
        }

        return $order;
    }

    private function ensureBuyerLoggedIn(Order $order): void
    {
        if (!Auth::check() || (int) Auth::id() !== (int) $order->user_id) {
            Auth::loginUsingId($order->user_id);
        }
    }
}
