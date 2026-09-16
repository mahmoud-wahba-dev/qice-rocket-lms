<?php

namespace Database\Seeders;

use App\Models\Accounting;
use App\Models\Payout;
use App\Models\Role;
use App\Models\Sale;
use App\Models\UserBank;
use App\Models\UserSelectedBank;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo finance data for panel_v1 instructor finance page.
 * Seeds real Sale (+ Accounting income) rows — no controller Mock.
 *
 * Target teacher: username hodinio-instructor-v2pjq (fallback: any teacher with webinars).
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorFinanceDemoSeeder --force
 */
class PanelV1InstructorFinanceDemoSeeder extends Seeder
{
    public const TEACHER_USERNAME = 'hodinio-instructor-v2pjq';

    public function run(): void
    {
        $now = time();

        $teacher = User::query()
            ->where('username', self::TEACHER_USERNAME)
            ->where('role_name', Role::$teacher)
            ->first();

        if (empty($teacher)) {
            $teacher = User::query()
                ->where('role_name', Role::$teacher)
                ->where('status', 'active')
                ->whereHas('webinars')
                ->orderByDesc('id')
                ->first();
        }

        if (empty($teacher)) {
            $this->command?->warn('No instructor found to seed finance demo data.');
            return;
        }

        $webinars = Webinar::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id)
                    ->orWhere('creator_id', $teacher->id);
            })
            ->where('status', Webinar::$active)
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        if ($webinars->isEmpty()) {
            $this->command?->warn('Instructor #' . $teacher->id . ' has no active webinars.');
            return;
        }

        $buyers = $this->ensureBuyers($now);
        $saleBlueprints = [
            ['amount' => 899, 'discount' => 100, 'commission' => 80,  'days_ago' => 1],
            ['amount' => 499, 'discount' => 0,   'commission' => 50,  'days_ago' => 2],
            ['amount' => 1299,'discount' => 200, 'commission' => 120, 'days_ago' => 3],
            ['amount' => 350, 'discount' => 50,  'commission' => 35,  'days_ago' => 5],
            ['amount' => 750, 'discount' => 0,   'commission' => 75,  'days_ago' => 7],
            ['amount' => 199, 'discount' => 0,   'commission' => 20,  'days_ago' => 10],
            ['amount' => 999, 'discount' => 150, 'commission' => 90,  'days_ago' => 12],
            ['amount' => 450, 'discount' => 0,   'commission' => 45,  'days_ago' => 15],
        ];

        $created = 0;
        foreach ($saleBlueprints as $i => $bp) {
            $buyer = $buyers[$i % count($buyers)];
            $webinar = $webinars[$i % $webinars->count()];
            $createdAt = $now - ((int) $bp['days_ago'] * 86400) - ($i * 3600);
            $total = max(0, (float) $bp['amount'] - (float) $bp['discount']);

            $sale = Sale::updateOrCreate(
                [
                    'buyer_id' => $buyer->id,
                    'webinar_id' => $webinar->id,
                    'type' => Sale::$webinar,
                    'manual_added' => true,
                ],
                [
                    'seller_id' => $teacher->id,
                    'payment_method' => Sale::$credit,
                    'amount' => $bp['amount'],
                    'discount' => $bp['discount'],
                    'total_amount' => $total,
                    'commission' => $bp['commission'],
                    'tax' => 0,
                    'created_at' => $createdAt,
                ]
            );

            $net = max(0, $total - (float) $bp['commission']);

            Accounting::updateOrCreate(
                [
                    'user_id' => $teacher->id,
                    'webinar_id' => $webinar->id,
                    'type' => Accounting::$addiction,
                    'type_account' => Accounting::$income,
                    'description' => 'دخل دورة [demo-finance] #' . $sale->id,
                ],
                [
                    'amount' => $net,
                    'created_at' => $createdAt,
                ]
            );

            $created++;
        }

        $payoutsCreated = $this->seedDemoPayouts($teacher, $now);

        $this->command?->info(sprintf(
            'Seeded %d finance demo sales + %d payouts for instructor #%d (%s / %s)',
            $created,
            $payoutsCreated,
            $teacher->id,
            $teacher->username,
            $teacher->email
        ));
    }

    private function seedDemoPayouts(User $teacher, int $now): int
    {
        $teacher->financial_approval = true;
        $teacher->save();

        $bank = UserBank::query()->orderBy('id')->first();
        if (empty($bank)) {
            $this->command?->warn('No user_banks rows — skipped payout seeding.');
            return 0;
        }

        $selectedBank = UserSelectedBank::query()
            ->where('user_id', $teacher->id)
            ->first();

        if (empty($selectedBank)) {
            $selectedBank = UserSelectedBank::create([
                'user_id' => $teacher->id,
                'user_bank_id' => $bank->id,
            ]);
        }

        // Wipe previous demo payout markers then recreate a rich history for UI/export testing.
        Payout::query()
            ->where('user_id', $teacher->id)
            ->whereIn('amount', [450, 320.5, 180, 275, 510, 640, 125.75, 890, 95, 410])
            ->delete();

        $defs = [
            ['amount' => 890, 'status' => Payout::$done, 'days_ago' => 45],
            ['amount' => 640, 'status' => Payout::$done, 'days_ago' => 38],
            ['amount' => 510, 'status' => Payout::$done, 'days_ago' => 30],
            ['amount' => 450, 'status' => Payout::$done, 'days_ago' => 20],
            ['amount' => 410, 'status' => Payout::$done, 'days_ago' => 16],
            ['amount' => 320.5, 'status' => Payout::$done, 'days_ago' => 12],
            ['amount' => 180, 'status' => Payout::$reject, 'days_ago' => 8],
            ['amount' => 125.75, 'status' => Payout::$reject, 'days_ago' => 5],
            ['amount' => 95, 'status' => Payout::$done, 'days_ago' => 3],
            ['amount' => 275, 'status' => Payout::$waiting, 'days_ago' => 1],
        ];

        $created = 0;
        foreach ($defs as $i => $def) {
            Payout::create([
                'user_id' => $teacher->id,
                'user_selected_bank_id' => $selectedBank->id,
                'amount' => $def['amount'],
                'status' => $def['status'],
                'created_at' => $now - ((int) $def['days_ago'] * 86400) - ($i * 1800),
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * @return User[]
     */
    private function ensureBuyers(int $now): array
    {
        $defs = [
            ['email' => 'finance.buyer1@demo.com', 'name' => 'سارة أحمد', 'user' => 'finance-buyer-1'],
            ['email' => 'finance.buyer2@demo.com', 'name' => 'محمد العتيبي', 'user' => 'finance-buyer-2'],
            ['email' => 'finance.buyer3@demo.com', 'name' => 'نورة الشمري', 'user' => 'finance-buyer-3'],
            ['email' => 'finance.buyer4@demo.com', 'name' => 'خالد القحطاني', 'user' => 'finance-buyer-4'],
            ['email' => 'finance.buyer5@demo.com', 'name' => 'ريم الحربي', 'user' => 'finance-buyer-5'],
        ];

        $buyers = [];
        foreach ($defs as $def) {
            $buyers[] = User::updateOrCreate(
                ['email' => $def['email']],
                [
                    'full_name' => $def['name'],
                    'username' => $def['user'],
                    'password' => Hash::make('123456'),
                    'role_name' => Role::$user,
                    'role_id' => Role::getUserRoleId(),
                    'status' => User::$active,
                    'verified' => true,
                    'created_at' => $now,
                ]
            );
        }

        return $buyers;
    }
}
