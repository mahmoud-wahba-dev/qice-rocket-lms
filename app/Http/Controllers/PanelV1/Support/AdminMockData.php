<?php

namespace App\Http\Controllers\PanelV1\Support;

class AdminMockData
{
    public static function dashboards(): array
    {
        return [
            [
                'key' => 'education',
                'label' => 'لوحة التعليم والأكاديميات',
                'icon' => 'icon-[tabler--school]',
                'route' => 'panel.v1.admin.education.home',
            ],
            [
                'key' => 'sales',
                'label' => 'لوحة المبيعات والماليات',
                'icon' => 'icon-[tabler--shopping-bag]',
                'route' => 'panel.v1.admin.sales.home',
            ],
            [
                'key' => 'marketing',
                'label' => 'لوحة التسويق',
                'icon' => 'icon-[tabler--settings]',
                'route' => 'panel.v1.admin.marketing.home',
            ],
            [
                'key' => 'system',
                'label' => 'لوحة إدارة النظام',
                'icon' => 'icon-[tabler--device-desktop]',
                'route' => 'panel.v1.admin.system.home',
            ],
        ];
    }

    public static function shell(string $dashboard, ?string $active = null): array
    {
        $dashboards = self::dashboards();
        $current = collect($dashboards)->firstWhere('key', $dashboard) ?? $dashboards[0];

        return [
            'adminDashboard' => $dashboard,
            'adminDashboards' => $dashboards,
            'adminCurrentDashboard' => $current,
            'adminNav' => self::nav($dashboard),
            'adminActive' => $active ?? 'home',
            'adminCta' => $dashboard === 'education'
                ? ['label' => '+ إنشاء دورة جديدة', 'href' => '#']
                : null,
        ];
    }

    public static function nav(string $dashboard): array
    {
        return match ($dashboard) {
            'sales' => self::salesNav(),
            'marketing' => self::marketingNav(),
            'system' => self::systemNav(),
            default => self::educationNav(),
        };
    }

    public static function educationNav(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'items' => [
                    ['key' => 'home', 'label' => 'لوحة التحكم الأساسية', 'icon' => 'icon-[tabler--layout-dashboard]', 'route' => 'panel.v1.admin.education.home'],
                ],
            ],
            [
                'title' => 'الإدارة الأكاديمية والدورات',
                'items' => [
                    ['key' => 'courses', 'label' => 'إدارة الدورات', 'icon' => 'icon-[tabler--book]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'courses']],
                    ['key' => 'bundles', 'label' => 'حزم الدورات والباقات', 'icon' => 'icon-[tabler--package]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'bundles']],
                    ['key' => 'live', 'label' => 'البث والمحاضرات المباشرة', 'icon' => 'icon-[tabler--video]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'live']],
                    ['key' => 'events', 'label' => 'الفعاليات', 'icon' => 'icon-[tabler--calendar-event]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'events']],
                ],
            ],
            [
                'title' => 'التقييمات والشهادات',
                'items' => [
                    ['key' => 'assignments', 'label' => 'التكليفات والواجبات', 'icon' => 'icon-[tabler--clipboard-list]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'assignments']],
                    ['key' => 'quizzes', 'label' => 'الاختبارات', 'icon' => 'icon-[tabler--list-check]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'quizzes']],
                    ['key' => 'certificates', 'label' => 'الشهادات والاعتمادات', 'icon' => 'icon-[tabler--certificate]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'certificates']],
                ],
            ],
            [
                'title' => 'التواصل والإنشاء',
                'items' => [
                    ['key' => 'forums', 'label' => 'منتديات الدورات', 'icon' => 'icon-[tabler--messages]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'forums']],
                    ['key' => 'notifications', 'label' => 'إشعارات الدورات', 'icon' => 'icon-[tabler--bell]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'notifications']],
                    ['key' => 'reviews', 'label' => 'المراجعات والتقييمات', 'icon' => 'icon-[tabler--star]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'reviews']],
                ],
            ],
            [
                'title' => 'القبول والمتابعة',
                'items' => [
                    ['key' => 'registration', 'label' => 'التسجيل', 'icon' => 'icon-[tabler--user-plus]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'registration']],
                    ['key' => 'waitlists', 'label' => 'قوائم الانتظار', 'icon' => 'icon-[tabler--list]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'waitlists']],
                    ['key' => 'departments', 'label' => 'الأقسام', 'icon' => 'icon-[tabler--building]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'departments']],
                    ['key' => 'filters', 'label' => 'فلاتر الدورات المخصصة', 'icon' => 'icon-[tabler--filter]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'filters']],
                ],
            ],
            [
                'title' => 'الحضور والتأخير',
                'items' => [
                    ['key' => 'attendance', 'label' => 'الحضور', 'icon' => 'icon-[tabler--calendar-check]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'attendance']],
                    ['key' => 'attendance-history', 'label' => 'تواريخ الحضور والغياب', 'icon' => 'icon-[tabler--history]', 'route' => 'panel.v1.admin.education.section', 'params' => ['section' => 'attendance-history']],
                ],
            ],
        ];
    }

    public static function salesNav(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'items' => [
                    ['key' => 'home', 'label' => 'لوحة التحكم الأساسية', 'icon' => 'icon-[tabler--layout-dashboard]', 'route' => 'panel.v1.admin.sales.home'],
                ],
            ],
            [
                'title' => 'سجل المبيعات والعمليات',
                'items' => [
                    ['key' => 'sales-list', 'label' => 'قائمة المبيعات', 'icon' => 'icon-[tabler--receipt]', 'route' => 'panel.v1.admin.sales.home'],
                    ['key' => 'budget', 'label' => 'الميزانية', 'icon' => 'icon-[tabler--chart-bar]', 'route' => 'panel.v1.admin.sales.section', 'params' => ['section' => 'budget']],
                ],
            ],
            [
                'title' => 'التحصيل وطرق السحب',
                'items' => [
                    ['key' => 'payouts', 'label' => 'طلبات السحب', 'icon' => 'icon-[tabler--cash]', 'route' => 'panel.v1.admin.sales.section', 'params' => ['section' => 'payouts']],
                    ['key' => 'offline', 'label' => 'المدفوعات دون اتصال', 'icon' => 'icon-[tabler--building-bank]', 'route' => 'panel.v1.admin.sales.section', 'params' => ['section' => 'offline']],
                ],
            ],
            [
                'title' => 'الخطط والاشتراكات',
                'items' => [
                    ['key' => 'subscriptions', 'label' => 'خطط اشتراك المتدربين', 'icon' => 'icon-[tabler--id]', 'route' => 'panel.v1.admin.sales.section', 'params' => ['section' => 'subscriptions']],
                    ['key' => 'installments', 'label' => 'التقسيط', 'icon' => 'icon-[tabler--calendar-dollar]', 'route' => 'panel.v1.admin.sales.section', 'params' => ['section' => 'installments']],
                    ['key' => 'packages', 'label' => 'باقات الخدمات', 'icon' => 'icon-[tabler--package]', 'route' => 'panel.v1.admin.sales.section', 'params' => ['section' => 'packages']],
                    ['key' => 'meetings', 'label' => 'باقات الاجتماعات', 'icon' => 'icon-[tabler--users]', 'route' => 'panel.v1.admin.sales.section', 'params' => ['section' => 'meetings']],
                ],
            ],
        ];
    }

    public static function marketingNav(): array
    {
        return [
            [
                'title' => 'التسويق والعروض',
                'items' => [
                    ['key' => 'dashboard', 'label' => 'لوحة قيادة التسويق', 'icon' => 'icon-[tabler--chart-pie]', 'route' => 'panel.v1.admin.marketing.section', 'params' => ['section' => 'dashboard']],
                    ['key' => 'tools', 'label' => 'إدارة أدوات التسويق', 'icon' => 'icon-[tabler--tool]', 'route' => 'panel.v1.admin.marketing.section', 'params' => ['section' => 'tools']],
                    ['key' => 'affiliate', 'label' => 'التسويق بالعمولة', 'icon' => 'icon-[tabler--share]', 'route' => 'panel.v1.admin.marketing.section', 'params' => ['section' => 'affiliate']],
                ],
            ],
            [
                'title' => 'إدارة المحتوى والمظهر',
                'items' => [
                    ['key' => 'home', 'label' => 'لوحة إدارة المحتوى والمظهر', 'icon' => 'icon-[tabler--device-desktop]', 'route' => 'panel.v1.admin.marketing.home'],
                ],
            ],
            [
                'title' => 'الحوافز والمكافآت',
                'items' => [
                    ['key' => 'cashback', 'label' => 'الاسترداد النقدي', 'icon' => 'icon-[tabler--cash]', 'route' => 'panel.v1.admin.marketing.section', 'params' => ['section' => 'cashback']],
                    ['key' => 'points', 'label' => 'نقاط المكافآت', 'icon' => 'icon-[tabler--coin]', 'route' => 'panel.v1.admin.marketing.section', 'params' => ['section' => 'points']],
                    ['key' => 'registration-bonus', 'label' => 'مكافأة التسجيل', 'icon' => 'icon-[tabler--gift]', 'route' => 'panel.v1.admin.marketing.section', 'params' => ['section' => 'registration-bonus']],
                    ['key' => 'gifts', 'label' => 'هدايا', 'icon' => 'icon-[tabler--gift-card]', 'route' => 'panel.v1.admin.marketing.section', 'params' => ['section' => 'gifts']],
                ],
            ],
        ];
    }

    public static function systemNav(): array
    {
        return [
            [
                'title' => 'إدارة المستخدمين والصلاحيات',
                'items' => [
                    ['key' => 'home', 'label' => 'المستخدمين', 'icon' => 'icon-[tabler--users]', 'route' => 'panel.v1.admin.system.home'],
                    ['key' => 'roles', 'label' => 'أدوار المستخدمين', 'icon' => 'icon-[tabler--shield]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'roles']],
                    ['key' => 'access', 'label' => 'إدارة الوصول', 'icon' => 'icon-[tabler--lock]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'access']],
                    ['key' => 'groups', 'label' => 'المجموعات', 'icon' => 'icon-[tabler--users-group]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'groups']],
                    ['key' => 'badges', 'label' => 'شارات التميز', 'icon' => 'icon-[tabler--rosette]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'badges']],
                    ['key' => 'custom-badges', 'label' => 'شارات مخصصة', 'icon' => 'icon-[tabler--award]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'custom-badges']],
                    ['key' => 'instructor-requests', 'label' => 'طلبات انضمام المدربين', 'icon' => 'icon-[tabler--user-plus]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'instructor-requests']],
                    ['key' => 'delete-requests', 'label' => 'طلبات حذف الحساب', 'icon' => 'icon-[tabler--trash]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'delete-requests']],
                    ['key' => 'ip', 'label' => 'إدارة عناوين IP', 'icon' => 'icon-[tabler--world]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'ip']],
                ],
            ],
            [
                'title' => 'الدعم والمجتمع',
                'items' => [
                    ['key' => 'forums', 'label' => 'المنتديات', 'icon' => 'icon-[tabler--messages]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'forums']],
                    ['key' => 'tickets', 'label' => 'نظام التذاكر', 'icon' => 'icon-[tabler--ticket]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'tickets']],
                    ['key' => 'reports', 'label' => 'البلاغات', 'icon' => 'icon-[tabler--flag]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'reports']],
                    ['key' => 'contact', 'label' => 'رسائل التواصل', 'icon' => 'icon-[tabler--mail]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'contact']],
                    ['key' => 'consultations', 'label' => 'الاستشارات', 'icon' => 'icon-[tabler--video]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'consultations']],
                    ['key' => 'notifications', 'label' => 'مركز الإشعارات', 'icon' => 'icon-[tabler--bell]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'notifications']],
                ],
            ],
            [
                'title' => 'الإعدادات العامة',
                'items' => [
                    ['key' => 'settings', 'label' => 'إعدادات النظام', 'icon' => 'icon-[tabler--settings]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'settings']],
                    ['key' => 'import', 'label' => 'الاستيراد الجماعي', 'icon' => 'icon-[tabler--database-import]', 'route' => 'panel.v1.admin.system.section', 'params' => ['section' => 'import']],
                ],
            ],
        ];
    }

    public static function stubMeta(string $dashboard, string $section): array
    {
        $labels = [];
        foreach (self::nav($dashboard) as $group) {
            foreach ($group['items'] as $item) {
                $labels[$item['key']] = $item['label'];
            }
        }

        $title = $labels[$section] ?? 'قسم الإدارة';

        return [
            'stubTitle' => $title,
            'stubSubtitle' => 'صفحة ثابتة للمعاينة — سيتم ربط البيانات لاحقاً.',
        ];
    }

    public static function educationHome(): array
    {
        return array_merge(self::shell('education', 'home'), [
            'welcomeTitle' => 'أهلاً بك في لوحة التعليم والأكاديمية',
            'welcomeSubtitle' => 'نظرة عامة على الدورات، الطلاب، الاختبارات، والشهادات في المنصة.',
            'stats' => [
                ['label' => 'الدورات النشطة', 'value' => '0', 'icon' => 'icon-[tabler--book]'],
                ['label' => 'الطلاب المسجلين', 'value' => '0', 'icon' => 'icon-[tabler--users]'],
                ['label' => 'جميع الاختبارات', 'value' => '0', 'icon' => 'icon-[tabler--list-check]'],
                ['label' => 'الشهادات الصادرة', 'value' => '2', 'icon' => 'icon-[tabler--certificate]'],
            ],
            'chartMetrics' => [
                ['label' => 'دورات مسجلة', 'value' => '18'],
                ['label' => 'دورات مباشرة', 'value' => '5'],
                ['label' => 'دورات نصية', 'value' => '5'],
                ['label' => 'نسبة النجاح', 'value' => '92%'],
            ],
            'latestCourses' => [
                ['title' => 'قياس النجاح والجودة', 'type' => 'دورة مسجلة', 'status' => 'منشور', 'statusTone' => 'success'],
                ['title' => 'قياس النجاح والجودة', 'type' => 'دورة مسجلة', 'status' => 'معلقة', 'statusTone' => 'danger'],
                ['title' => 'قياس النجاح والجودة', 'type' => 'دورة مسجلة', 'status' => 'منشور', 'statusTone' => 'success'],
                ['title' => 'قياس النجاح والجودة', 'type' => 'دورة مسجلة', 'status' => 'منشور', 'statusTone' => 'success'],
                ['title' => 'قياس النجاح والجودة', 'type' => 'دورة مسجلة', 'status' => 'منشور', 'statusTone' => 'success'],
            ],
        ]);
    }

    public static function salesList(): array
    {
        $row = [
            'id' => '1024',
            'student' => 'علا محمد',
            'student_email' => 'ola@qiec.example',
            'instructor' => 'د. سعودي حسنين',
            'service' => 'دورة قياس النجاح',
            'price' => '299 ر.س',
            'discount' => '0 ر.س',
            'vat' => '44.85 ر.س',
            'type' => 'دورة',
            'date' => '11 أبريل 2026',
            'status' => 'ناجحة',
        ];

        return array_merge(self::shell('sales', 'sales-list'), [
            'pageTitleText' => 'قائمة المبيعات',
            'pageSubtitle' => 'سجل شامل لجميع معاملات الشراء وفواتير الدورات المكتملة.',
            'stats' => [
                ['label' => 'إجمالي المبيعات', 'value' => '0.00 ر.س', 'icon' => 'icon-[tabler--school]'],
                ['label' => 'مبيعات الدورات', 'value' => '0.00 ر.س', 'icon' => 'icon-[tabler--book]'],
                ['label' => 'مبيعات الجلسات', 'value' => '0.00 ر.س', 'icon' => 'icon-[tabler--video]'],
                ['label' => 'مبيعات فاشلة', 'value' => '0.00 ر.س', 'icon' => 'icon-[tabler--x]'],
            ],
            'salesRows' => [$row, $row, $row, $row, $row],
            'pagination' => ['from' => 1, 'to' => 10, 'total' => 97],
        ]);
    }

    public static function marketingContent(): array
    {
        return array_merge(self::shell('marketing', 'home'), [
            'pageTitleText' => 'إدارة المحتوى والمظهر',
            'pageSubtitle' => 'تخصيص الواجهات والهوية البصرية وإدارة المحتوى.',
            'contentCards' => [
                ['title' => 'المتجر', 'icon' => 'icon-[tabler--code]'],
                ['title' => 'المدونة', 'icon' => 'icon-[tabler--shopping-cart]'],
                ['title' => 'الصفحات', 'icon' => 'icon-[tabler--bookmark]'],
                ['title' => 'صفحات إضافية', 'icon' => 'icon-[tabler--bookmark]'],
                ['title' => 'التوصيات', 'icon' => 'icon-[tabler--shopping-cart-off]'],
                ['title' => 'الثيمات', 'icon' => 'icon-[tabler--code]'],
                ['title' => 'منشئ صفحات الهبوط', 'icon' => 'icon-[tabler--shopping-cart]'],
                ['title' => 'العلامات', 'icon' => 'icon-[tabler--bookmark]'],
                ['title' => 'خريطة المدربين', 'icon' => 'icon-[tabler--map-pin]'],
                ['title' => 'منشئ النماذج', 'icon' => 'icon-[tabler--forms]'],
                ['title' => 'محتوى الذكاء الاصطناعي', 'icon' => 'icon-[tabler--robot]'],
                ['title' => 'طلبات إزالة المحتوى', 'icon' => 'icon-[tabler--trash]'],
            ],
        ]);
    }

    public static function systemUsers(): array
    {
        $user = [
            'id' => '881',
            'name' => 'علا محمد',
            'email' => 'ola@qiec.example',
            'role' => 'طالب',
            'balance' => '0.00 ر.س',
            'income' => '0.00 ر.س',
            'group' => 'عام',
            'registered_at' => '11 أبريل 2026',
            'status' => 'نشط',
        ];

        return array_merge(self::shell('system', 'home'), [
            'pageTitleText' => 'المستخدمين',
            'pageSubtitle' => 'لوحة مركزية لإدارة الطلاب والمحاضرين وصلاحياتهم.',
            'userTabs' => ['الكل', 'الموظفين', 'الطلاب', 'المديرين', 'المستشارين', 'المنظمات'],
            'userRows' => [$user, $user, $user, $user, $user],
            'pagination' => ['from' => 1, 'to' => 10, 'total' => 97],
        ]);
    }
}
