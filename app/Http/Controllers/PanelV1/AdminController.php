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

    protected function renderAdmin(Request $request, string $view, string $pageTitle, array $data = [])
    {
        $user = $this->resolveAdmin($request);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return view($view, array_merge($data, [
            'pageTitle' => $pageTitle,
            'authUser' => $user,
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
        ]));
    }
}
