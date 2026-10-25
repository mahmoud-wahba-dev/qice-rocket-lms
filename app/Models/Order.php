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
     * Human-readable payment method for admin dashboards (Tabby, Tamara, credit, offline, …).
     */
    public function resolvePaymentMethodLabel(): string
    {
        if ($this->payment_method === self::$credit) {
            return 'رصيد المحفظة';
        }

        if (!empty($this->payment_channel_id)) {
            $channel = $this->relationLoaded('paymentChannel')
                ? $this->paymentChannel
                : $this->paymentChannel()->first();

            if (!empty($channel)) {
                return $channel->title ?: $channel->class_name;
            }
        }

        $paymentData = null;
        if (!empty($this->payment_data)) {
            $decoded = json_decode($this->payment_data, true);
            if (is_array($decoded)) {
                $paymentData = $decoded;
            }
        }

        if (!empty($paymentData['gateway'])) {
            $gateway = (string) $paymentData['gateway'];
            if (strcasecmp($gateway, 'offline') === 0) {
                return 'تحويل بنكي';
            }
            if (strcasecmp($gateway, 'credit') === 0) {
                return 'رصيد المحفظة';
            }

            return $gateway;
        }

        if ($this->relationLoaded('offlinePayment') ? !empty($this->offlinePayment) : $this->offlinePayment()->exists()) {
            return 'تحويل بنكي';
        }

        if ($this->payment_method === self::$paymentChannel) {
            return 'بوابة دفع';
        }

        return '—';
    }
}
