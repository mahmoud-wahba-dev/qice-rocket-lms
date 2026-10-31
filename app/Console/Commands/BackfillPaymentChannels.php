<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\Models\Sale;
use Illuminate\Console\Command;

class BackfillPaymentChannels extends Command
{
    protected $signature = 'qiec:backfill-payment-channels
                            {--dry-run : Show what would change without writing}
                            {--force : Apply updates}
                            {--channel= : Force a gateway class_name (Tamara|Tabby|Paymob) onto listed --orders}
                            {--orders= : Comma-separated order ids to force (used with --channel)}';

    protected $description = 'Backfill orders.payment_channel_id / payment_data so admin sales show Paymob/Tabby/Tamara instead of بوابة دفع';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run') || !$this->option('force');
        $forcedChannel = $this->option('channel');
        $forcedOrderIds = array_values(array_filter(array_map('intval', explode(',', (string) $this->option('orders')))));

        if (!empty($forcedChannel) && !empty($forcedOrderIds)) {
            return $this->forceChannelOnOrders($forcedChannel, $forcedOrderIds, $dryRun);
        }

        $channels = PaymentChannel::query()
            ->whereIn('class_name', ['Tamara', 'Tabby', 'Paymob'])
            ->get()
            ->keyBy(fn ($c) => strtolower($c->class_name));

        $orders = Order::query()
            ->where(function ($q) {
                $q->where('payment_method', Order::$paymentChannel)
                    ->orWhereNotNull('payment_channel_id')
                    ->orWhereNotNull('payment_data');
            })
            ->where(function ($q) {
                $q->whereNull('payment_channel_id')
                    ->orWhereNull('payment_data')
                    ->orWhere('payment_data', '')
                    ->orWhere('payment_data', 'null');
            })
            ->orderByDesc('id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders need payment-channel backfill.');
            $this->printUnresolvedGatewaySales();

            return self::SUCCESS;
        }

        $updated = 0;
        foreach ($orders as $order) {
            $data = $order->decodedPaymentData();
            $gateway = $data['gateway'] ?? $data['title'] ?? $data['class_name'] ?? null;
            $channel = null;

            if (!empty($gateway)) {
                $channel = $channels->get(strtolower((string) $gateway))
                    ?? PaymentChannel::query()
                        ->whereRaw('LOWER(class_name) = ?', [strtolower((string) $gateway)])
                        ->orWhereRaw('LOWER(title) = ?', [strtolower((string) $gateway)])
                        ->first();
            }

            if (empty($channel) && !empty($order->payment_channel_id)) {
                $channel = PaymentChannel::find($order->payment_channel_id);
            }

            if (empty($channel)) {
                $this->line("order {$order->id}: skip (no gateway hint)");
                continue;
            }

            $data['gateway'] = $channel->class_name;
            $data['channel_id'] = $channel->id;
            $data['title'] = $channel->title;

            $this->line("order {$order->id}: → {$channel->class_name} (#{$channel->id})");

            if (!$dryRun) {
                $order->update([
                    'payment_method' => Order::$paymentChannel,
                    'payment_channel_id' => $channel->id,
                    'payment_data' => json_encode($data),
                ]);
                $updated++;
            }
        }

        if ($dryRun) {
            $this->comment('Dry run. Re-run with --force to apply.');
        } else {
            $this->info("Updated {$updated} order(s).");
        }

        $this->printUnresolvedGatewaySales();

        return self::SUCCESS;
    }

    private function forceChannelOnOrders(string $channelName, array $orderIds, bool $dryRun): int
    {
        $channel = PaymentChannel::query()
            ->whereRaw('LOWER(class_name) = ?', [strtolower($channelName)])
            ->orWhereRaw('LOWER(title) = ?', [strtolower($channelName)])
            ->first();

        if (empty($channel)) {
            $this->error("Unknown channel: {$channelName}");

            return self::FAILURE;
        }

        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            if (empty($order)) {
                $this->warn("order {$orderId}: not found");
                continue;
            }

            $data = $order->decodedPaymentData();
            $data['gateway'] = $channel->class_name;
            $data['channel_id'] = $channel->id;
            $data['title'] = $channel->title;

            $this->line("order {$orderId}: force → {$channel->class_name}");

            if (!$dryRun) {
                $order->update([
                    'payment_method' => Order::$paymentChannel,
                    'payment_channel_id' => $channel->id,
                    'payment_data' => json_encode($data),
                ]);
            }
        }

        if ($dryRun) {
            $this->comment('Dry run. Re-run with --force to apply.');
        } else {
            $this->info('Forced channel updates applied.');
        }

        return self::SUCCESS;
    }

    private function printUnresolvedGatewaySales(): void
    {
        $sales = Sale::query()
            ->where('payment_method', Sale::$paymentChannel)
            ->whereNotNull('order_id')
            ->with(['order.paymentChannel'])
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->filter(fn (Sale $s) => $s->resolvePaymentMethodLabel() === 'بوابة دفع');

        if ($sales->isEmpty()) {
            $this->info('No recent sales still labeled بوابة دفع.');

            return;
        }

        $this->warn('Sales still labeled بوابة دفع (set channel manually if you know the gateway):');
        foreach ($sales as $sale) {
            $this->line(sprintf(
                '  sale #%d order #%s total=%s → php artisan qiec:backfill-payment-channels --channel=Tamara --orders=%s --force',
                $sale->id,
                $sale->order_id,
                $sale->total_amount,
                $sale->order_id
            ));
        }
    }
}
