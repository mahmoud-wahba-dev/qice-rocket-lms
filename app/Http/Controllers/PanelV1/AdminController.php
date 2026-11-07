<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function home(Request $request)
    {
        $user = $this->resolveAdmin($request);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return redirect()->route('panel.v1.admin.education.home');
    }

    /**
     * @return \App\User|RedirectResponse
     */
    protected function resolveAdmin(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isAdmin()) {
            if ($user->isUser()) {
                return redirect()->route('panel.v1.student.home');
            }

            if ($user->isTeacher()) {
                return redirect()->route('panel.v1.instructor.home');
            }

            if ($user->isOrganization()) {
                return redirect()->route('panel.v1.organization.home');
            }

            return redirect('/panel');
        }

        return $user;
    }

    /**
     * Sidebar data for every admin page: fills the nav when a page did not pass the
     * shell, and highlights the item whose URL is the longest prefix of the request.
     * A valid key passed by the controller is kept unless the URL matches a more
     * specific item (the dashboard root, e.g. /v1/admin/education, never overrides).
     */
    protected function sidebarContext(Request $request, array $data): array
    {
        $path = '/' . trim($request->path(), '/');
        $dashboard = $data['adminDashboard'] ?? null;

        if (!$dashboard && preg_match('#^/v1/admin/(education|sales|marketing|system)(?:/|$)#', $path, $m)) {
            $dashboard = $m[1];
        }

        if (!$dashboard) {
            return [];
        }

        $explicit = $data['adminActive'] ?? null;
        $shell = \App\Http\Controllers\PanelV1\Support\AdminMockData::shell($dashboard, $explicit);
        $nav = $data['adminNav'] ?? $shell['adminNav'];
        $root = '/v1/admin/' . $dashboard;

        $bestLen = -1;
        $bestKeys = [];
        foreach ($nav as $group) {
            foreach ($group['items'] ?? [] as $item) {
                foreach (array_merge([$item], $item['children'] ?? []) as $entry) {
                    try {
                        $p = rtrim((string) parse_url(route($entry['route'], $entry['params'] ?? []), PHP_URL_PATH), '/');
                    } catch (\Throwable $e) {
                        continue;
                    }
                    if ($p === '' || ($path !== $p && strpos($path, $p . '/') !== 0)) {
                        continue;
                    }
                    $len = strlen($p);
                    if ($len > $bestLen) {
                        $bestLen = $len;
                        $bestKeys = [$entry['key']];
                    } elseif ($len === $bestLen) {
                        $bestKeys[] = $entry['key'];
                    }
                }
            }
        }

        $active = $explicit ?? 'home';
        if ($bestKeys && !in_array($explicit, $bestKeys, true)) {
            $matchedRoot = $bestLen === strlen($root);
            $explicitIsKnown = false;
            foreach ($nav as $group) {
                foreach ($group['items'] ?? [] as $item) {
                    foreach (array_merge([$item], $item['children'] ?? []) as $entry) {
                        if (($entry['key'] ?? null) === $explicit) {
                            $explicitIsKnown = true;
                        }
                    }
                }
            }
            if (!($matchedRoot && $explicitIsKnown)) {
                $active = $bestKeys[0];
            }
        }

        return array_merge(
            array_diff_key($shell, $data),
            ['adminActive' => $active]
        );
    }

    protected function renderAdmin(Request $request, string $view, string $pageTitle, array $data = [])
    {
        $user = $this->resolveAdmin($request);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $data = array_merge($data, $this->sidebarContext($request, $data));

        // Defaults first; page $data must win (previously array_merge($data, defaults)
        // overwrote sales/education stats and hid real payment totals).
        return view($view, array_merge([
            'pageTitle' => $pageTitle,
            'authUser' => $user,
            'unReadNotifications' => $user->getUnReadNotifications(),
            'stats' => [
                ['label' => 'المتدربون', 'value' => (string) \App\User::where('role_name', 'user')->count()],
                ['label' => 'المدربون', 'value' => (string) \App\User::where('role_name', 'teacher')->count()],
                ['label' => 'المنظمات', 'value' => (string) \App\User::where('role_name', 'organization')->count()],
                ['label' => 'الدورات', 'value' => (string) \App\Models\Webinar::count()],
                ['label' => 'بانتظار المراجعة', 'value' => (string) \App\Models\Webinar::where('status', 'pending')->count()],
                ['label' => 'إجمالي المبيعات', 'value' => handlePrice(\App\Models\Sale::whereNull('refund_at')->sum('total_amount'))],
                ['label' => 'تذاكر مفتوحة', 'value' => (string) \App\Models\Support::where('status', 'open')->count()],
                ['label' => 'الاختبارات', 'value' => (string) \App\Models\Quiz::count()],
            ],
            'recentSales' => \App\Models\Sale::with(['buyer', 'webinar'])
                ->orderBy('id', 'desc')
                ->limit(8)
                ->get(),
            'openTickets' => \App\Models\Support::with(['user'])
                ->where('status', 'open')
                ->orderBy('id', 'desc')
                ->limit(8)
                ->get(),
        ], $data));
    }
}
