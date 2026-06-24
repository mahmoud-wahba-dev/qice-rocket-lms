<?php

namespace App\PaymentChannels\Drivers\Paymob;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PayMob\Facades\PayMob;

class Channel extends BasePaymentChannel implements IChannel
{
    use PaymobTrait;

    protected $currency;
    protected $test_mode;
    protected $order_session_key;

    protected $api_key;
    protected $username;
    protected $password;
    protected $integration_id;
    protected $iframe_id;
    protected $HMAC;


    protected array $credentialItems = [
        'api_key',
        'username',
        'password',
        'integration_id',
        'iframe_id',
        'HMAC',
    ];

    // https://github.com/samir-hussein/paymob
    // https://github.com/ctf0/laravel-paymob
    // https://acceptdocs.paymobsolutions.com/docs/card-payments

    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency(); // EGP
        $this->setCredentialItems($paymentChannel);
        $this->order_session_key = 'paymob.payments.order_id';
    }

    public function paymentRequest(Order $order)
    {
        $user = $order->user;
        $amount = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $amountCents = (int) round($amount * 100);

        if ($amountCents <= 0) {
            return $this->paymobFailureResponse('Invalid order amount');
        }

        $errorMsg = null;

        try {
            $paymentAuth = $this->AuthenticationRequest();

            if (empty($paymentAuth->token)) {
                throw new \RuntimeException($this->paymobErrorMessage($paymentAuth));
            }

            $paymentOrder = $this->OrderRegistrationAPI([
                'auth_token' => $paymentAuth->token,
                'amount_cents' => $amountCents,
                'currency' => 'SAR',
                'delivery_needed' => false,
                'merchant_order_id' => $order->id,
                'items' => [],
            ]);

            if (empty($paymentOrder->id)) {
                throw new \RuntimeException($this->paymobErrorMessage($paymentOrder));
            }

            $nameParts = preg_split('/\s+/', trim($user->full_name ?? 'QIEC User'), 2);
            $firstName = $nameParts[0] ?? 'QIEC';
            $lastName = $nameParts[1] ?? 'User';
            $phone = preg_replace('/\D+/', '', $user->mobile ?? '') ?: '0500000000';

            $paymentKey = $this->PaymentKeyRequest([
                'auth_token' => $paymentAuth->token,
                'amount_cents' => $amountCents,
                'currency' => 'SAR',
                'order_id' => $paymentOrder->id,
                'billing_data' => [
                    'apartment' => 'NA',
                    'email' => $user->email,
                    'floor' => 'NA',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'street' => 'NA',
                    'building' => 'NA',
                    'phone_number' => $phone,
                    'shipping_method' => 'NA',
                    'postal_code' => '00000',
                    'city' => $user->getRegionByTypeId($user->city_id) ?: 'Riyadh',
                    'country' => 'SA',
                    'state' => $user->getRegionByTypeId($user->province_id) ?: 'Riyadh',
                ],
            ]);

            if (!empty($paymentKey->token)) {
                return view('design_1.web.cart.payment.channels.paymob', [
                    'token' => $paymentKey->token,
                    'iframeId' => $this->iframe_id,
                ]);
            }

            $errorMsg = $this->paymobErrorMessage($paymentKey);
        } catch (\Throwable $e) {
            \Log::error('Paymob paymentRequest failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            $errorMsg = $e->getMessage();
        }

        return $this->paymobFailureResponse($errorMsg);
    }

    private function paymobFailureResponse(?string $errorMsg)
    {
        $toastData = [
            'title' => trans('cart.fail_purchase'),
            'msg' => $errorMsg ?: trans('update.gateway_error_please_contact_support'),
            'status' => 'error',
        ];

        return redirect()->back()->with(['toast' => $toastData])->withInput();
    }

    private function makeCallbackUrl()
    {
        return url("/payments/verify/Paymob");
    }

    public function verify(Request $request)
    {
        $data = $request->all();

        if (!empty($data)) {
            $requestHmac = !empty($data['hmac']) ? $data['hmac'] : '';

            try {
                $calcHmac = $this->calcHMAC($request);

                if ($requestHmac == $calcHmac) {
                    $orderId = $data['merchant_order_id'];
                    $amount_cents = $data['amount_cents'];
                    $data['transaction_id'] = $data['id'];

                    $user = auth()->user();

                    $order = Order::where('id', $orderId)
                        ->where('user_id', $user->id)
                        ->first();

                    if (!empty($order)) {
                        $orderStatus = Order::$fail;

                        if ($data['success'] and ($order->total_amount * 100) == $amount_cents) {
                            $orderStatus = Order::$paying;
                        }

                        $order->update([
                            'status' => $orderStatus,
                            'payment_data' => json_encode($data)
                        ]);
                    }

                    return $order;
                }
            } catch (\Exception $e) {
                \Log::error('Paymob verify failed', ['message' => $e->getMessage()]);
            }
        }

        return null;
    }

}
