<?php

namespace App\PaymentChannels\Drivers\Tamara;

use AlazziAz\Tamara\Tamara\Client;
use AlazziAz\Tamara\Tamara\Configuration;
use AlazziAz\Tamara\Tamara\Model\Order\Discount;
use AlazziAz\Tamara\Tamara\Model\Order\Order as TamaraOrder;
use AlazziAz\Tamara\Tamara\Model\Order\MerchantUrl;
use AlazziAz\Tamara\Tamara\Model\Money;
use AlazziAz\Tamara\Tamara\Model\Order\Consumer as TamaraConsumer;
use AlazziAz\Tamara\Tamara\Model\Order\OrderItemCollection as TamaraOrderItemCollection;
use AlazziAz\Tamara\Tamara\Model\Order\OrderItem as TamaraOrderItem;
use AlazziAz\Tamara\Tamara\Request\Checkout\CreateCheckoutRequest;
use AlazziAz\Tamara\Tamara\Model\Order\Address as TamaraAddress;
use AlazziAz\Tamara\Tamara\Request\Order\AuthoriseOrderRequest;
use AlazziAz\Tamara\Tamara\Request\Order\GetOrderRequest;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use \AlazziAz\Tamara\Facades\Tamara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class Channel extends BasePaymentChannel implements IChannel
{
    protected $country_code;
    protected $currency;
    protected $test_mode;
    protected $base_url;
    protected $api_token;
    protected $notification_token;
    protected $request_timeout;
    protected $order_session_key;
    protected $checkout_response_order_session_key;


    protected array $credentialItems = [
        'base_url',
        'api_token',
        'notification_token',
        'country_code',
    ];

    /**
     * @url https://github.com/alazzi-az/laravel-tamara
     *
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = strtoupper(currency()); // Ensure ISO uppercase like "SAR"
        $this->order_session_key = 'tamara.payments.order_id';
        $this->checkout_response_order_session_key = 'tamara.checkout_response.order_id';
        $this->request_timeout = 1000;
        $this->setCredentialItems($paymentChannel);

        // Infer/normalize supported country-code by currency to avoid API 400 not_supported_country_currency
        $this->normalizeCountryCurrency();
    }

    private function normalizeCountryCurrency(): void
    {
        $currency = strtoupper((string)$this->currency);
        $country = strtoupper((string)$this->country_code);

        $map = [
            'SAR' => 'SA',
            'AED' => 'AE',
            'KWD' => 'KW',
            'BHD' => 'BH',
            'QAR' => 'QA',
        ];

        if (empty($currency) && !empty($country)) {
            $inverse = array_flip($map);
            if (isset($inverse[$country])) {
                $this->currency = $inverse[$country];
            }
        }

        if (isset($map[$currency]) && $country !== $map[$currency]) {
            \Log::warning('Tamara country_code normalized to match currency', [
                'from_country' => $country,
                'to_country' => $map[$currency],
                'currency' => $currency,
            ]);
            $this->country_code = $map[$currency];
        }

        if (empty($this->country_code)) {
            $this->country_code = $map[$currency] ?? 'SA';
        }
    }

    private function getTamaraClient()
    {
        $this->assertConfig();

        // Normalize base url (trim spaces and trailing slashes)
        $base = rtrim(trim((string)$this->base_url));
        if (!str_starts_with($base, 'http://') && !str_starts_with($base, 'https://')) {
            $base = 'https://' . $base;
        }
        $base = rtrim($base, '/');

        $clientConfiguration = Configuration::create($base, $this->api_token, $this->request_timeout);
        return Client::create($clientConfiguration);
    }

    private function assertConfig(): void
    {
        if (empty($this->base_url) || empty($this->api_token)) {
            \Log::error('Tamara configuration missing', [
                'base_url' => $this->base_url,
                'has_api_token' => !empty($this->api_token),
            ]);
            throw new \InvalidArgumentException('Tamara base_url or api_token is not configured');
        }
    }

    public function paymentRequest(Order $order)
    {
        $user = $order->user;
        $userNames = explode(' ', $user->full_name);
        $userFirstName = $userNames[0];
        $userLastName = (count($userNames) > 1) ? implode(" ", array_slice($userNames, 1)) : 'no_last_name';

        $totalAmount = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $totalTaxAmount = $this->makeAmountByCurrency($order->tax, $this->currency);
        $totalDiscountAmount = $this->makeAmountByCurrency($order->total_discount, $this->currency);
        $totalShippingAmount = $this->makeAmountByCurrency($order->product_delivery_fee, $this->currency);


        $tamaraClient = $this->getTamaraClient();
        \Log::debug('Tamara createCheckout init', [
            'base_url' => $this->base_url,
            'country' => $this->country_code,
            'currency' => $this->currency,
        ]);

        //$response = $tamaraClient->getPaymentTypes($this->country_code);
        $merchantUrl = app()->make(MerchantUrl::class);
        $merchantUrl->setSuccessUrl($this->makeCallbackUrl("success"));
        $merchantUrl->setFailureUrl($this->makeCallbackUrl("failure"));
        $merchantUrl->setCancelUrl($this->makeCallbackUrl("cancel"));
        $merchantUrl->setNotificationUrl($this->makeCallbackUrl("notify"));

        $tamaraOrder = (new TamaraOrder());
        $tamaraOrder->setMerchantUrl($merchantUrl);

        // firs set  Order Details
        $tamaraOrder->setOrderReferenceId($order->id);
        $tamaraOrder->setLocale(app()->getLocale());
        $tamaraOrder->setCurrency($this->currency);
        $tamaraOrder->setTotalAmount(new Money($totalAmount, $this->currency));
        $tamaraOrder->setCountryCode($this->country_code);
        // Tamara expects one of its defined payment types, not local order payment method
        $tamaraOrder->setPaymentType(TamaraOrder::PAY_BY_LATER);
        $tamaraOrder->setDescription("paying order {$order->id} description");
        $tamaraOrder->setTaxAmount(new Money($totalTaxAmount, $this->currency));
        $tamaraOrder->setDiscount(new Discount("discount", new Money($totalDiscountAmount, $this->currency)));
        $tamaraOrder->setShippingAmount(new Money($totalShippingAmount, $this->currency));

        // Customer
        $consumer = new TamaraConsumer();
        $consumer->setFirstName($userFirstName);
        $consumer->setLastName($userLastName);
        $consumer->setEmail($user->email);
        $consumer->setPhoneNumber($user->mobile);
        $tamaraOrder->setConsumer($consumer);


        $siteAddress = getContactPageSettings("address");
        $userAddress = !empty($user->address) ? $user->address : (!empty($siteAddress) ? $siteAddress : 'Address');
        $userCountry = $user->getRegionByTypeId($user->country_id);
        $userCity = $user->getRegionByTypeId($user->city_id);

        // Normalize region values to non-empty strings (Tamara requires non-empty city)
        $userCountryName = is_string($userCountry) ? $userCountry : ($userCountry->title ?? $userCountry->name ?? 'Region');
        $userCityName = is_string($userCity) ? $userCity : ($userCity->title ?? $userCity->name ?? 'City');
        $userCountryName = trim((string)$userCountryName) !== '' ? (string)$userCountryName : 'Region';
        $userCityName = trim((string)$userCityName) !== '' ? (string)$userCityName : 'City';


        // Address
        $address = new TamaraAddress();
        $address->setFirstName($userFirstName);
        $address->setLastName($userLastName);
        $address->setLine1($userAddress);
        $address->setLine2(" ");
        $address->setRegion($userCountry ?? " ");
        $address->setCity($userCity ?? " ");
        $address->setPhoneNumber($user->mobile);
        $address->setCountryCode($this->country_code);

        $tamaraOrder->setBillingAddress($address);
        $tamaraOrder->setShippingAddress($address);

        // Order Items
        $orderItemCollection = new TamaraOrderItemCollection();

        foreach ($order->orderItems as $orderItem) {
            $tamaraOrderItem = new TamaraOrderItem();
            $tamaraOrderItem->setName("order_item_{$orderItem->id}");
            $tamaraOrderItem->setQuantity(1);
            $tamaraOrderItem->setUnitPrice(new Money($orderItem->amount ?? 0, $this->currency));
            $tamaraOrderItem->setType($orderItem->getItemTypeName());
            $tamaraOrderItem->setTotalAmount(new Money($orderItem->total_amount ?? 0, $this->currency));
            $tamaraOrderItem->setTaxAmount(new Money($orderItem->tax_price ?? 0, $this->currency));
            $tamaraOrderItem->setDiscountAmount(new Money($orderItem->discount ?? 0, $this->currency));
            $tamaraOrderItem->setReferenceId($orderItem->id);
            $tamaraOrderItem->setSku($orderItem->id);

            $orderItemCollection->append($tamaraOrderItem);
        }

        $tamaraOrder->setItems($orderItemCollection);

        $request = new CreateCheckoutRequest($tamaraOrder);

        try {
            $response = $tamaraClient->createCheckout($request);
        } catch (\Throwable $e) {
            \Log::error('Tamara createCheckout exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return redirect()->back()->with(['toast' => $toastData])->withInput();
        }

        // Guard against failed API responses which return no checkout response
        if (!is_object($response) || !method_exists($response, 'isSuccess') || !$response->isSuccess() || !method_exists($response, 'getCheckoutResponse') || !$response->getCheckoutResponse()) {
            // Surface a friendly error while preserving details in logs
            \Log::error('Tamara createCheckout failed', [
                'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'message' => method_exists($response, 'getMessage') ? $response->getMessage() : null,
                'errors' => method_exists($response, 'getErrors') ? $response->getErrors() : null,
                'content' => method_exists($response, 'getContent') ? $response->getContent() : null,
                'type' => is_object($response) ? get_class($response) : gettype($response),
            ]);

            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return redirect()->back()->with(['toast' => $toastData])->withInput();
        }

        $getCheckoutResponse = $response->getCheckoutResponse();
        $checkoutUrl = $getCheckoutResponse->getCheckoutUrl();
        // Sanitize to avoid header injection/new lines
        $checkoutUrl = str_replace(["\r", "\n"], '', trim($checkoutUrl));
        if (!filter_var($checkoutUrl, FILTER_VALIDATE_URL)) {
            \Log::error('Tamara checkoutUrl is invalid', ['url' => $checkoutUrl]);
            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return redirect()->back()->with(['toast' => $toastData])->withInput();
        }

        $orderID = $getCheckoutResponse->getOrderId();
        //$checkOutID = $getCheckoutResponse->getCheckoutId();

        session()->put($this->order_session_key, $order->id);
        session()->put($this->checkout_response_order_session_key, $orderID);

        // Persist gateway id on the order so notify/success can recover without browser session.
        try {
            $paymentData = [];
            if (!empty($order->payment_data)) {
                $decoded = json_decode($order->payment_data, true);
                if (is_array($decoded)) {
                    $paymentData = $decoded;
                }
            }
            $paymentData['gateway'] = 'Tamara';
            $paymentData['tamara_order_id'] = $orderID;
            $channelId = PaymentChannel::where('class_name', 'Tamara')->value('id');
            $order->update(array_filter([
                'payment_method' => Order::$paymentChannel,
                'payment_channel_id' => $order->payment_channel_id ?: $channelId,
                'payment_data' => json_encode($paymentData),
            ]));
        } catch (\Throwable $e) {
            \Log::warning('Tamara: failed to persist checkout order id', [
                'local_order_id' => $order->id,
                'tamara_order_id' => $orderID,
                'message' => $e->getMessage(),
            ]);
        }

        return $checkoutUrl;
    }

    private function makeCallbackUrl($status)
    {
        return url("/payments/verify/Tamara?status={$status}");
    }

    public function verify(Request $request)
    {
        $callbackStatus = strtolower((string) $request->get('status', $request->input('status', '')));
        $tamaraPaymentOrderId = $request->get('orderId')
            ?? $request->input('orderId')
            ?? $request->input('order_id')
            ?? data_get($request->all(), 'orderId')
            ?? data_get($request->all(), 'order_id');

        $sessionOrderId = session()->get($this->order_session_key, null);
        $checkoutResponseTamaraOrderId = session()->get($this->checkout_response_order_session_key, null);
        session()->forget($this->order_session_key);
        session()->forget($this->checkout_response_order_session_key);

        $order = null;
        $paidStatuses = [
            'authorised', 'authorized', 'approved', 'captured', 'fully_captured',
            'completed', 'complete', 'paid', 'settled',
        ];
        $cancelStatuses = ['cancel', 'canceled', 'cancelled', 'failure', 'failed', 'declined', 'expired'];

        \Log::info('Tamara verify hit', [
            'callback_status' => $callbackStatus,
            'tamara_order_id' => $tamaraPaymentOrderId,
            'session_order_id' => $sessionOrderId,
            'has_auth' => Auth::check(),
        ]);

        // Cancel/failure browser return — mark fail only for explicit cancel/fail statuses.
        if (in_array($callbackStatus, $cancelStatuses, true) && empty($tamaraPaymentOrderId)) {
            $order = $this->resolveLocalOrder(
                $request->get('orderReferenceId') ?? $request->input('order_reference_id'),
                $sessionOrderId,
                null
            );

            if (!empty($order) && !in_array($order->status, [Order::$paid, Order::$paying], true)) {
                $order->update(['status' => Order::$fail]);
            }

            return $order;
        }

        try {
            // Recover gateway id from session / stored payment_data when query params are missing.
            if (empty($tamaraPaymentOrderId) && !empty($checkoutResponseTamaraOrderId)) {
                $tamaraPaymentOrderId = $checkoutResponseTamaraOrderId;
            }

            if (empty($tamaraPaymentOrderId) && !empty($sessionOrderId)) {
                $sessionOrder = Order::find($sessionOrderId);
                if (!empty($sessionOrder?->payment_data)) {
                    $decoded = json_decode($sessionOrder->payment_data, true);
                    if (is_array($decoded) && !empty($decoded['tamara_order_id'])) {
                        $tamaraPaymentOrderId = $decoded['tamara_order_id'];
                    }
                }
            }

            if (!empty($tamaraPaymentOrderId)) {
                $tamaraClient = $this->getTamaraClient();
                $localOrderId = null;
                $tamaraOrderStatus = null;

                // Prefer API order details (works without browser session / for notify).
                try {
                    $orderDetails = $tamaraClient->getOrder(new GetOrderRequest((string) $tamaraPaymentOrderId));
                    if (method_exists($orderDetails, 'isSuccess') && $orderDetails->isSuccess()) {
                        $localOrderId = $orderDetails->getOrderReferenceId();
                        $tamaraOrderStatus = $orderDetails->getStatus();
                    } elseif (method_exists($orderDetails, 'getOrderReferenceId')) {
                        $localOrderId = $orderDetails->getOrderReferenceId();
                        $tamaraOrderStatus = method_exists($orderDetails, 'getStatus') ? $orderDetails->getStatus() : null;
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Tamara getOrder failed during verify', [
                        'tamara_order_id' => $tamaraPaymentOrderId,
                        'message' => $e->getMessage(),
                    ]);
                }

                if (empty($localOrderId)) {
                    $localOrderId = $request->get('orderReferenceId')
                        ?? $request->input('order_reference_id')
                        ?? $sessionOrderId;
                }

                if (
                    empty($localOrderId)
                    && !empty($checkoutResponseTamaraOrderId)
                    && (string) $checkoutResponseTamaraOrderId === (string) $tamaraPaymentOrderId
                ) {
                    $localOrderId = $sessionOrderId;
                }

                $order = $this->resolveLocalOrder($localOrderId, $sessionOrderId, null);

                if (!empty($order)) {
                    if ($order->status === Order::$paid) {
                        $this->ensureBuyerLoggedIn($order);
                        return $order;
                    }

                    if (empty($tamaraOrderStatus) || in_array(strtolower((string) $tamaraOrderStatus), ['new', 'approved', 'authorised', 'authorized'], true)) {
                        try {
                            $authedResponse = $tamaraClient->authoriseOrder(new AuthoriseOrderRequest((string) $tamaraPaymentOrderId));
                            if (method_exists($authedResponse, 'getOrderStatus') && $authedResponse->getOrderStatus()) {
                                $tamaraOrderStatus = $authedResponse->getOrderStatus();
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Tamara authoriseOrder failed during verify', [
                                'tamara_order_id' => $tamaraPaymentOrderId,
                                'local_order_id' => $order->id,
                                'message' => $e->getMessage(),
                            ]);
                        }
                    }

                    $normalizedStatus = strtolower((string) $tamaraOrderStatus);

                    if (in_array($normalizedStatus, $paidStatuses, true)) {
                        $this->ensureBuyerLoggedIn($order);
                        $order->update(['status' => Order::$paying]);
                        \Log::info('Tamara verify: marked paying', [
                            'local_order_id' => $order->id,
                            'tamara_status' => $tamaraOrderStatus,
                            'callback_status' => $callbackStatus,
                        ]);
                    } elseif (in_array($callbackStatus, ['notify', 'success'], true)) {
                        // Never mark fail on success/notify — create Sale even if status string is unfamiliar.
                        $this->ensureBuyerLoggedIn($order);
                        $order->update(['status' => Order::$paying]);
                        \Log::warning('Tamara verify: success/notify without known paid status — marking paying', [
                            'local_order_id' => $order->id,
                            'tamara_status' => $tamaraOrderStatus,
                            'callback_status' => $callbackStatus,
                        ]);
                    } elseif (in_array($callbackStatus, $cancelStatuses, true)) {
                        $order->update(['status' => Order::$fail]);
                    } else {
                        \Log::error('Tamara verify: unpaid status after API check', [
                            'local_order_id' => $order->id,
                            'tamara_status' => $tamaraOrderStatus,
                            'callback_status' => $callbackStatus,
                        ]);
                    }
                } else {
                    \Log::error('Tamara verify: local order not found', [
                        'tamara_order_id' => $tamaraPaymentOrderId,
                        'resolved_reference' => $localOrderId,
                        'session_order_id' => $sessionOrderId,
                    ]);
                }
            } elseif (!empty($sessionOrderId) && Auth::check()) {
                // Legacy fallback when gateway id missing — ONLY fail on explicit cancel/failure.
                // (Previously this marked every return as fail, which broke Tamara success redirects.)
                $order = Order::where('id', $sessionOrderId)
                    ->where('user_id', Auth::id())
                    ->first();

                if (
                    !empty($order)
                    && in_array($callbackStatus, $cancelStatuses, true)
                    && !in_array($order->status, [Order::$paid, Order::$paying], true)
                ) {
                    $order->update(['status' => Order::$fail]);
                } elseif (!empty($order) && in_array($callbackStatus, ['success', 'notify'], true)) {
                    \Log::warning('Tamara verify: success without gateway order id', [
                        'local_order_id' => $order->id,
                        'callback_status' => $callbackStatus,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Tamara verify exception', [
                'message' => $e->getMessage(),
                'tamara_order_id' => $tamaraPaymentOrderId,
            ]);
        }

        return $order;
    }

    private function resolveLocalOrder($referenceId, $sessionOrderId, $userId = null): ?Order
    {
        $orderId = null;

        if (!empty($referenceId) && is_numeric($referenceId)) {
            $orderId = (int) $referenceId;
        } elseif (!empty($sessionOrderId) && is_numeric($sessionOrderId)) {
            $orderId = (int) $sessionOrderId;
        }

        if (empty($orderId)) {
            return null;
        }

        $query = Order::query()->where('id', $orderId);

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    private function ensureBuyerLoggedIn(Order $order): void
    {
        if (!Auth::check() || (int) Auth::id() !== (int) $order->user_id) {
            Auth::loginUsingId($order->user_id);
        }
    }
}
