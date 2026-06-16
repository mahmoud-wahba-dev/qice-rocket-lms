<?php

namespace App\PaymentChannels\Drivers\MyFatoorah;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;

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

        $order_id = session()->get($this->order_session_key, null);
        session()->forget($this->order_session_key);

        $user = auth()->user();

        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->first();

        if ($status == 'success' && !empty($paymentId) && !empty($order)) {
            $apiData = [
                "Key" => $paymentId,
                "KeyType" => "PaymentId"
            ];

            try {
                $response = $this->callApi("/v2/GetPaymentStatus", $apiData);

                if (
                    isset($response['Data']['InvoiceStatus']) &&
                    $response['Data']['InvoiceStatus'] === 'Paid'
                ) {
                    $order->update([
                        'status' => Order::$paying
                    ]);

                    return $order;
                }
            } catch (\Exception $e) {
                // handle failure silently returning logic below.
            }
        }

        if (!empty($order)) {
            $order->update(['status' => Order::$fail]);
        }

        return $order;
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
