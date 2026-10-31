<?php

namespace App\Models;

use App\Models\Observers\OrderNumberObserver;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //status
    public static $pending = 'pending';
    public static $paying = 'paying';
    public static $paid = 'paid';
    public static $fail = 'fail';

    //types
    public static $webinar = 'webinar';
    public static $meeting = 'meeting';
    public static $charge = 'charge';
    public static $subscribe = 'subscribe';
    public static $promotion = 'promotion';
    public static $registrationPackage = 'registration_package';
    public static $product = 'product';
    public static $bundle = 'bundle';
    public static $installmentPayment = 'installment_payment';
    public static $gift = 'gift';
    public static $eventTicket = 'event_ticket';
    public static $meetingPackage = 'meeting_package';

    public static $addiction = 'addiction';
    public static $deduction = 'deduction';

    public static $income = 'income';
    public static $asset = 'asset';

    //paymentMethod
    public static $credit = 'credit';
    public static $paymentChannel = 'payment_channel';

    public $timestamps = false;

    protected $guarded = ['id'];


    protected static function boot()
    {
        parent::boot();

        Order::observe(OrderNumberObserver::class);
    }


    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function orderItems()
    {
        return $this->hasMany('App\Models\OrderItem', 'order_id', 'id');
    }

    public function paymentChannel()
    {
        return $this->belongsTo(PaymentChannel::class, 'payment_channel_id', 'id');
    }

    public function offlinePayment()
    {
        return $this->hasOne(OfflinePayment::class, 'order_id', 'id');
    }

    /**
     * Human-readable payment method for admin dashboards (Tabby, Tamara, Paymob, credit, offline, …).
     */
    public function resolvePaymentMethodLabel(): string
    {
        if ($this->payment_method === self::$credit) {
            return 'رصيد المحفظة';
        }

        if ($this->relationLoaded('offlinePayment') ? !empty($this->offlinePayment) : $this->offlinePayment()->exists()) {
            return 'تحويل بنكي';
        }

        $paymentData = $this->decodedPaymentData();

        // 1) Linked payment channel row (Paymob / Tabby / Tamara).
        $channel = null;
        if (!empty($this->payment_channel_id)) {
            $channel = $this->relationLoaded('paymentChannel')
                ? $this->paymentChannel
                : $this->paymentChannel()->first();
        }

        // 2) Resolve channel from payment_data when channel_id was not stored.
        if (empty($channel)) {
            $gatewayKey = $paymentData['gateway']
                ?? $paymentData['title']
                ?? $paymentData['class_name']
                ?? null;
            if (!empty($gatewayKey) && !in_array(strtolower((string) $gatewayKey), ['offline', 'credit', 'payment_channel'], true)) {
                $channel = PaymentChannel::query()
                    ->where(function ($q) use ($gatewayKey) {
                        $q->where('class_name', $gatewayKey)
                            ->orWhere('title', $gatewayKey)
                            ->orWhereRaw('LOWER(class_name) = ?', [strtolower((string) $gatewayKey)])
                            ->orWhereRaw('LOWER(title) = ?', [strtolower((string) $gatewayKey)]);
                    })
                    ->first();
            }
        }

        if (!empty($channel)) {
            return self::formatGatewayLabel($channel->title ?: $channel->class_name);
        }

        if (!empty($paymentData['gateway'])) {
            return self::formatGatewayLabel((string) $paymentData['gateway']);
        }

        if (!empty($paymentData['title'])) {
            return self::formatGatewayLabel((string) $paymentData['title']);
        }

        if ($this->payment_method === self::$paymentChannel) {
            return 'بوابة دفع';
        }

        return '—';
    }

    public function decodedPaymentData(): array
    {
        if (empty($this->payment_data)) {
            return [];
        }

        $decoded = json_decode($this->payment_data, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Normalize gateway names for admin UI (matches checkout labels).
     */
    public static function formatGatewayLabel(string $gateway): string
    {
        $key = strtolower(trim($gateway));

        $map = [
            'tamara' => 'Tamara',
            'tabby' => 'Tabby',
            'paymob' => 'Paymob',
            'offline' => 'تحويل بنكي',
            'credit' => 'رصيد المحفظة',
            'payment_channel' => 'بوابة دفع',
        ];

        return $map[$key] ?? $gateway;
    }
}
