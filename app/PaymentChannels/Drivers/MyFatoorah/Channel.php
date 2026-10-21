<?php

namespace App\PaymentChannels\Drivers\MyFatoorah;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $api_key;
    protected $base_url;
    protected $order_session_key;

    protected array $credentialItems = [
        'api_key',
        'base_url',
    ];

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        $this->order_session_key = 'myfatoorah.payments.order_id';
        $this->setCredentialItems($paymentChannel);
    }

    public function paymentRequest(Order $order)
    {
        $price = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $generalSettings = getGeneralSettings();

        $data = [
            "InvoiceValue" => $price,
            "CustomerName" => $order->user->name ?? 'Customer',
            "CustomerReference" => (string) $order->id,
            "UserDefinedField" => (string) $order->id,
            "NotificationOption" => "ALL",
            "InvoiceItems" => [
                [
                    "ItemName" => $generalSettings['site_name'] . ' payment',
                    "Quantity" => 1,
                    "UnitPrice" => $price
                ]
            ],
            "CallBackUrl" => $this->makeCallbackUrl('success'),
            "ErrorUrl" => $this->makeCallbackUrl('cancel'),
        ];

        $response = $this->callApi("/v2/SendPayment", $data);

        if (!empty($response['Data']['InvoiceURL'])) {
            session()->put($this->order_session_key, $order->id);
            return redirect($response['Data']['InvoiceURL']);
        }

        throw new \Exception($response['Message'] ?? 'Payment failed');
    }

    private function makeCallbackUrl($status)
    {
        return url("/payments/verify/MyFatoorah?status=$status");
    }

    public function verify(Request $request)
    {
        $data = $request->all();
        $status = $data['status'] ?? null;
        $paymentId = $request->input('paymentId');

        $sessionOrderId = session()->get($this->order_session_key, null);
        session()->forget($this->order_session_key);

        $order = null;

        // Prefer gateway API + CustomerReference over fragile browser session after redirect.
        if ($status == 'success' && !empty($paymentId)) {
            try {
                $response = $this->callApi("/v2/GetPaymentStatus", [
                    "Key" => $paymentId,
                    "KeyType" => "PaymentId",
                ]);

                $invoiceData = $response['Data'] ?? null;
                $orderId = $this->resolveOrderIdFromInvoice($invoiceData, $sessionOrderId);

                if (!empty($orderId)) {
                    $order = Order::where('id', $orderId)->first();
                }

                if (
                    !empty($order) &&
                    isset($invoiceData['InvoiceStatus']) &&
                    $invoiceData['InvoiceStatus'] === 'Paid'
                ) {
                    if (!Auth::check() || (int) Auth::id() !== (int) $order->user_id) {
                        Auth::loginUsingId($order->user_id);
                    }

                    $order->update([
                        'status' => Order::$paying,
                    ]);

                    return $order;
                }
            } catch (\Exception $e) {
                // Fall through to session/auth fallback below.
            }
        }

        // Legacy fallback: session + authenticated user (pre-hardening path).
        if (empty($order) && !empty($sessionOrderId) && Auth::check()) {
            $order = Order::where('id', $sessionOrderId)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!empty($order)) {
            $order->update(['status' => Order::$fail]);
        }

        return $order;
    }

    /**
     * Resolve local order id from MyFatoorah invoice payload, then session.
     */
    private function resolveOrderIdFromInvoice($invoiceData, $sessionOrderId)
    {
        if (!empty($invoiceData) && is_array($invoiceData)) {
            foreach (['CustomerReference', 'UserDefinedField'] as $key) {
                if (!empty($invoiceData[$key]) && is_numeric($invoiceData[$key])) {
                    return (int) $invoiceData[$key];
                }
            }
        }

        if (!empty($sessionOrderId) && is_numeric($sessionOrderId)) {
            return (int) $sessionOrderId;
        }

        return null;
    }

    private function callApi($endpoint, $data)
    {
        $baseUrl = rtrim($this->base_url ?? '', '/');
        $ch = curl_init($baseUrl . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->api_key,
                "Content-Type: application/json"
            ],
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        return json_decode($result, true);
    }
}
