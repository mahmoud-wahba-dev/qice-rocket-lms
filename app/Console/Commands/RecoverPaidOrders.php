<?php

namespace App\Console\Commands;

use App\Http\Controllers\Web\PaymentController;
use App\Models\Order;
use App\Models\PaymentChannel;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecoverPaidOrders extends Command
{
    protected $signature = 'qiec:recover-paid-orders
                            {--user= : Limit to a specific buyer user id}
                            {--order= : Recover a single order id}
                            {--all-pending : Also list abandoned pending carts (not only paying)}
                            {--include-failed : Include payment_channel orders stuck as fail (Tamara/Tabby collected but LMS marked fail)}
                            {--dry-run : List stuck orders without creating sales}
                            {--force : Actually create missing Sale rows for listed orders}';

    protected $description = 'Find gateway orders with no sales row and optionally recover enrollment + dashboard amounts';

    public function handle(): int
    {
        $orderId = $this->option('order');
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run') || !$this->option('force');

        $appUrl = (string) config('app.url');
        if (str_contains($appUrl, '.local') || str_contains($appUrl, 'localhost')) {
            $this->warn("APP_URL is '{$appUrl}' — Tamara/Tabby callbacks will use this host. Production must be https://training.qiec.sa (then config:clear).");
        }

        $statuses = [Order::$paying];
        if ($this->option('all-pending')) {
            $statuses = array_merge($statuses, [Order::$pending, Order::$paid]);
        }
        if ($this->option('include-failed') || $orderId) {
            $statuses[] = Order::$fail;
        }
        $statuses = array_values(array_unique($statuses));

        $query = Order::query()
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('sales')
                    ->whereColumn('sales.order_id', 'orders.id');
            })
            ->when($orderId, fn ($q) => $q->where('id', $orderId))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(
                !$orderId && !$this->option('all-pending') && !$this->option('include-failed'),
                // Default: mid-verify stuck payments (gateway returned, Sale never created)
                fn ($q) => $q->where('status', Order::$paying)
            )
            ->when(
                !$orderId && ($this->option('all-pending') || $this->option('include-failed')),
                function ($q) use ($statuses) {
                    $q->whereIn('status', $statuses);
                    // For failed list, only payment_channel orders (not abandoned wallet drafts).
                    if (in_array(Order::$fail, $statuses, true)) {
                        $q->where(function ($inner) {
                            $inner->where('status', '!=', Order::$fail)
                                ->orWhere('payment_method', Order::$paymentChannel)
                                ->orWhereNotNull('payment_channel_id');
                        });
                    }
                }
            )
            ->orderByDesc('id');

        $orders = $query->with(['orderItems', 'paymentChannel'])->get();

        if ($orders->isEmpty()) {
            $this->info('No orders without sales matched the filters.');
            $this->printOfflineWaiting($userId);

            return self::SUCCESS;
        }

        $rows = $orders->map(fn (Order $o) => [
            $o->id,
            $o->user_id,
            $o->status,
            $o->payment_method,
            optional($o->paymentChannel)->title ?? optional($o->paymentChannel)->class_name,
            $o->total_amount,
            $o->orderItems->count(),
        ])->all();

        $this->table(
            ['order_id', 'user_id', 'status', 'payment_method', 'channel', 'total', 'items'],
            $rows
        );

        $this->printOfflineWaiting($userId);

        if ($dryRun) {
            $this->comment('Dry run (default). Re-run with --force to create missing sales for these orders.');
            $this->comment('Only force after confirming the gateway shows Paid (Tamara/Tabby portal).');
            $this->comment('Example: php artisan qiec:recover-paid-orders --order=173 --force');
            $this->comment('Bulk failed Tamara/Tabby: php artisan qiec:recover-paid-orders --include-failed --force');

            return self::SUCCESS;
        }

        $controller = new PaymentController();
        $recovered = 0;

        foreach ($orders as $order) {
            if ($order->orderItems->isEmpty()) {
                $this->warn("Order {$order->id}: skipped (no order items).");
                continue;
            }

            if (Sale::where('order_id', $order->id)->exists()) {
                $this->warn("Order {$order->id}: skipped (sale already exists).");
                continue;
            }

            try {
                DB::transaction(function () use ($controller, $order, &$recovered) {
                    if (empty($order->payment_method)) {
                        $order->payment_method = Order::$paymentChannel;
                        $order->save();
                    }

                    $controller->setPaymentAccounting($order);
                    $order->update(['status' => Order::$paid]);
                    $recovered++;
                });
                $channel = optional($order->paymentChannel)->title ?? $order->payment_method;
                $this->info("Order {$order->id}: sale(s) created ({$channel}), status=paid, total={$order->total_amount}.");
            } catch (\Throwable $e) {
                $this->error("Order {$order->id}: failed — " . $e->getMessage());
            }
        }

        $this->info("Recovered {$recovered} order(s).");

        return self::SUCCESS;
    }

    private function printOfflineWaiting($userId): void
    {
        if (!Schema::hasTable('offline_payments')) {
            return;
        }

        $columns = ['id', 'user_id', 'amount', 'status'];
        foreach (['order_id', 'type'] as $optional) {
            if (Schema::hasColumn('offline_payments', $optional)) {
                $columns[] = $optional;
            }
        }

        $offlineQuery = DB::table('offline_payments')
            ->where('status', 'waiting')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('id')
            ->limit(20);

        $offline = $offlineQuery->get($columns);

        if ($offline->isEmpty()) {
            $this->line('No waiting offline_payments' . ($userId ? " for user {$userId}" : '') . '.');

            return;
        }

        $this->warn('Waiting offline payments (approve in admin /v1/admin/sales offline):');
        $this->table(
            $columns,
            $offline->map(fn ($r) => collect($columns)->map(fn ($c) => $r->{$c} ?? null)->all())->all()
        );
    }
}
