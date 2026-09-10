<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function home(Request $request)
    {
        $org = $this->resolveOrganization($request);

        if ($org instanceof \Illuminate\Http\RedirectResponse) {
            return $org;
        }

        $instructorIds = $this->instructorIds($org);

        $stats = [
            ['label' => 'المدربون', 'value' => (string) count($instructorIds)],
            ['label' => 'المتدربون', 'value' => (string) $this->usersQuery($org, 'students')->count()],
            ['label' => 'الدورات', 'value' => (string) $this->orgWebinars($org)->count()],
            ['label' => 'إجمالي المبيعات', 'value' => handlePrice($this->orgSales($org)->sum('total_amount'))],
        ];

        return view('panel_v1.organization.pages.home', [
            'pageTitle' => 'لوحة المنظمة',
            'authUser' => $org,
            'stats' => $stats,
        ]);
    }

    public function users(Request $request, string $type = 'instructors')
    {
        $org = $this->resolveOrganization($request);

        if ($org instanceof \Illuminate\Http\RedirectResponse) {
            return $org;
        }

        if (!in_array($type, ['instructors', 'students'])) {
            abort(404);
        }

        return view('panel_v1.organization.pages.users', [
            'pageTitle' => $type === 'instructors' ? 'مدربو المنظمة' : 'متدربو المنظمة',
            'authUser' => $org,
            'type' => $type,
            'members' => $this->usersQuery($org, $type)->orderBy('id', 'desc')->limit(50)->get(),
        ]);
    }

    public function courses(Request $request)    {
        $org = $this->resolveOrganization($request);

        if ($org instanceof \Illuminate\Http\RedirectResponse) {
            return $org;
        }

        $courses = $this->orgWebinars($org)->map(function ($webinar) {
            return [
                'title' => $webinar->title,
                'subtitle' => $webinar->category->title ?? '',
                'slug' => $webinar->slug,
                'status' => $webinar->status,
                'students' => Sale::where('webinar_id', $webinar->id)->whereNull('refund_at')->distinct('buyer_id')->count('buyer_id'),
            ];
        });

        return view('panel_v1.organization.pages.courses', [
            'pageTitle' => 'دورات المنظمة',
            'authUser' => $org,
            'courses' => $courses,
        ]);
    }

    private function instructorIds($org): array
    {
        return User::where('organ_id', $org->id)
            ->where('role_name', 'teacher')
            ->pluck('id')
            ->all();
    }

    private function usersQuery($org, string $type)
    {
        $query = User::where('organ_id', $org->id);

        if ($type === 'instructors') {
            $query->where('role_name', 'teacher');
        } else {
            $query->where('role_name', 'user');
        }

        return $query;
    }

    private function orgWebinars($org)
    {
        $instructorIds = $this->instructorIds($org);

        return Webinar::with(['category'])
            ->where(function ($query) use ($org, $instructorIds) {
                $query->where('creator_id', $org->id);
                if (!empty($instructorIds)) {
                    $query->orWhereIn('teacher_id', $instructorIds);
                }
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    private function orgSales($org)
    {
        $webinarIds = $this->orgWebinars($org)->pluck('id')->all();

        if (empty($webinarIds)) {
            return Sale::whereRaw('1 = 0');
        }

        return Sale::whereIn('webinar_id', $webinarIds)->whereNull('refund_at');
    }

    public function memberCreate(Request $request, string $type)
    {
        $org = $this->resolveOrganization($request);

        if ($org instanceof \Illuminate\Http\RedirectResponse) {
            return $org;
        }

        if (!in_array($type, ['instructors', 'students'])) {
            abort(404);
        }

        return view('panel_v1.organization.pages.member-form', [
            'pageTitle' => 'عضو جديد',
            'authUser' => $org,
            'type' => $type,
            'member' => null,
        ]);
    }

    public function memberStore(Request $request, string $type)
    {
        $org = $this->resolveOrganization($request);

        if ($org instanceof \Illuminate\Http\RedirectResponse) {
            return $org;
        }

        if (!in_array($type, ['instructors', 'students'])) {
            abort(404);
        }

        $request->validate([
            'full_name' => 'required|string|max:128',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:32|unique:users,mobile',
            'password' => 'required|min:6|confirmed',
        ]);

        $roleId = $type === 'instructors' ? 4 : 1;
        $roleName = $type === 'instructors' ? 'teacher' : 'user';

        $member = new \App\User();
        $member->full_name = $request->input('full_name');
        $member->email = $request->input('email');
        $member->mobile = $request->input('mobile');
        $member->password = \App\User::generatePassword($request->input('password'));
        $member->role_id = $roleId;
        $member->role_name = $roleName;
        $member->organ_id = $org->id;
        $member->status = 'active';
        $member->created_at = time();
        $member->updated_at = time();
        $member->save();

        return redirect()
            ->route('panel.v1.organization.users', ['type' => $type])
            ->with('toast', ['title' => 'تم', 'msg' => 'تمت إضافة العضو بنجاح', 'type' => 'success']);
    }

    public function memberEdit(Request $request, string $type, int $id)
    {
        $org = $this->resolveOrganization($request);

        if ($org instanceof \Illuminate\Http\RedirectResponse) {
            return $org;
        }

        if (!in_array($type, ['instructors', 'students'])) {
            abort(404);
        }

        $member = $this->orgMemberOrFail($org, $type, $id);

        return view('panel_v1.organization.pages.member-form', [
            'pageTitle' => 'تعديل عضو',
            'authUser' => $org,
            'type' => $type,
            'member' => $member,
        ]);
    }

    public function memberUpdate(Request $request, string $type, int $id)
    {
        $org = $this->resolveOrganization($request);

        if ($org instanceof \Illuminate\Http\RedirectResponse) {
            return $org;
        }

        if (!in_array($type, ['instructors', 'students'])) {
            abort(404);
        }

        $member = $this->orgMemberOrFail($org, $type, $id);

        $request->validate([
            'full_name' => 'required|string|max:128',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'mobile' => 'nullable|string|max:32|unique:users,mobile,' . $member->id,
            'password' => 'nullable|min:6|confirmed',
            'status' => 'required|in:active,inactive',
        ]);

        $member->full_name = $request->input('full_name');
        $member->email = $request->input('email');
        $member->mobile = $request->input('mobile');
        $member->status = $request->input('status');
        $member->updated_at = time();

        if ($request->filled('password')) {
            $member->password = \App\User::generatePassword($request->input('password'));
        }

        $member->save();

        return redirect()
            ->route('panel.v1.organization.users', ['type' => $type])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ التعديلات', 'type' => 'success']);
    }

    private function orgMemberOrFail($org, string $type, int $id)
    {
        $roleName = $type === 'instructors' ? 'teacher' : 'user';

        return \App\User::where('id', $id)
            ->where('organ_id', $org->id)
            ->where('role_name', $roleName)
            ->firstOrFail();
    }

    /**
     * @return \App\User|\Illuminate\Http\RedirectResponse
     */
    private function resolveOrganization(Request $request)    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isOrganization()) {
            if ($user->isUser()) {
                return redirect()->route('panel.v1.student.home');
            }

            if ($user->isTeacher()) {
                return redirect()->route('panel.v1.instructor.home');
            }

            if ($user->isAdmin()) {
                return redirect()->route('panel.v1.admin.home');
            }

            return redirect('/panel');
        }

        return $user;
    }
}
