<?php

namespace App\Console\Commands;

use App\Http\Controllers\Web\PaymentController;
use App\Models\Order;
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
                            {--dry-run : List stuck orders without creating sales}
                            {--force : Actually create missing Sale rows for listed orders}';

    protected $description = 'Find orders with no sales row (default: status=paying) and optionally recover enrollment';

    public function handle(): int
    {
        $orderId = $this->option('order');
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run') || !$this->option('force');

        $query = Order::query()
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('sales')
                    ->whereColumn('sales.order_id', 'orders.id');
            })
            ->when($orderId, fn ($q) => $q->where('id', $orderId))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(
                !$orderId && !$this->option('all-pending'),
                // Default: mid-verify stuck payments (gateway returned, Sale never created)
                fn ($q) => $q->where('status', Order::$paying)
            )
            ->when(
                $this->option('all-pending') && !$orderId,
                fn ($q) => $q->whereIn('status', [Order::$paying, Order::$pending, Order::$paid])
            )
            ->orderByDesc('id');

        $orders = $query->with('orderItems')->get();

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
            $o->total_amount,
            $o->orderItems->count(),
        ])->all();

        $this->table(
            ['order_id', 'user_id', 'status', 'payment_method', 'total', 'items'],
            $rows
        );

        $this->printOfflineWaiting($userId);

        if ($dryRun) {
            $this->comment('Dry run (default). Re-run with --force to create missing sales for these orders.');
            $this->comment('Only force after confirming the gateway shows Paid (or offline was approved).');
            $this->comment('Example: php artisan qiec:recover-paid-orders --order=123 --force');

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
                    $controller->setPaymentAccounting($order);
                    $order->update(['status' => Order::$paid]);
                    $recovered++;
                });
                $this->info("Order {$order->id}: sale(s) created, status=paid.");
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
