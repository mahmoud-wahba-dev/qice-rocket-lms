<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\Certificate;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\NotificationStatus;
use App\Models\Sale;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\SupportDepartment;
use App\Models\Subscribe;
use App\Models\SubscribeUse;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use App\Models\QuizzesResult;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function home(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $enrolledSales = Sale::with(['webinar.teacher', 'webinar.category'])
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->orderBy('id', 'desc')
            ->get();

        $webinarIds = $enrolledSales->pluck('webinar_id')->filter()->unique()->values()->all();

        $enrolledCourses = $enrolledSales->take(6)->map(function ($sale) use ($user) {
            $webinar = $sale->webinar;
            $progress = 0;
            $sessionsCount = 0;
            $thumbnail = null;
            $title = '';
            $category = '';
            $slug = '';
            $webinarId = null;

            if (!empty($webinar)) {
                try {
                    $progress = (int) $webinar->getProgress(false, $user);
                } catch (\Throwable $e) {
                    $progress = 0;
                }

                try {
                    $sessionsCount = $webinar->sessions->count() + $webinar->files->count();
                } catch (\Throwable $e) {
                    $sessionsCount = 0;
                }

                $thumbnail = $webinar->thumbnail;
                $title = $webinar->title;
                $category = $webinar->category->title ?? '';
                $slug = $webinar->slug;
                $webinarId = $webinar->id;
            }

            return [
                'saleId' => $sale->id,
                'saleDate' => date('Y/m/d', (int) $sale->created_at),
                'hasWebinar' => !empty($webinar),
                'title' => $title,
                'category' => $category,
                'slug' => $slug,
                'webinarId' => $webinarId,
                'thumbnail' => $thumbnail,
                'progress' => max(0, min(100, $progress)),
                'sessionsCount' => $sessionsCount,
            ];
        });

        $stats = [
            'activeCourses' => $enrolledSales->count(),
            'certificates' => Certificate::where('student_id', $user->id)->count(),
            'assignments' => WebinarAssignmentHistory::where('student_id', $user->id)->count(),
            'upcomingSessions' => !empty($webinarIds)
                ? Session::whereIn('webinar_id', $webinarIds)
                    ->where('status', 'active')
                    ->where('date', '>=', time())
                    ->count()
                : 0,
            // Learning hours are not tracked per-user by the core; kept as 0 until a tracker exists.
            'learningHours' => 0,
        ];

        $liveSessions = !empty($webinarIds)
            ? Session::with(['webinar'])
                ->whereIn('webinar_id', $webinarIds)
                ->where('status', 'active')
                ->where('date', '>=', time() - 86400)
                ->orderBy('date')
                ->limit(6)
                ->get()
            : collect();

        $pendingAssignments = collect();
        $submittedHistories = WebinarAssignmentHistory::with(['assignment.webinar'])
            ->where('student_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        if (!empty($webinarIds)) {
            $submittedAssignmentIds = $submittedHistories->pluck('assignment_id')->filter()->unique()->all();
            $pendingAssignments = WebinarAssignment::with(['webinar'])
                ->whereIn('webinar_id', $webinarIds)
                ->where('status', 'active')
                ->when(!empty($submittedAssignmentIds), function ($query) use ($submittedAssignmentIds) {
                    $query->whereNotIn('id', $submittedAssignmentIds);
                })
                ->orderBy('id', 'desc')
                ->limit(6)
                ->get();
        }

        $quizResults = QuizzesResult::with(['quiz.webinar'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $certificates = Certificate::with(['webinar'])
            ->where('student_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        $comments = Comment::with(['webinar'])
            ->where('user_id', $user->id)
            ->whereNotNull('webinar_id')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('panel_v1.student.pages.home', [
            'pageTitle' => 'لوحة المتدرب',
            'authUser' => $user,
            'stats' => $stats,
            'enrolledCourses' => $enrolledCourses,
            'liveSessions' => $liveSessions,
            'pendingAssignments' => $pendingAssignments,
            'submittedHistories' => $submittedHistories,
            'quizResults' => $quizResults,
            'certificates' => $certificates,
            'comments' => $comments,
        ]);
    }

    public function notifications(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $notifications = $this->userNotificationsQuery($user)
            ->orderBy('notifications.id', 'desc')
            ->limit(20)
            ->get();

        $seenIds = NotificationStatus::where('user_id', $user->id)
            ->pluck('notification_id')
            ->flip();

        $notifications->each(function ($notification) use ($seenIds) {
            $notification->is_seen = isset($seenIds[$notification->id]);
        });

        return view('panel_v1.student.pages.notifications', [
            'pageTitle' => 'الاشعارات',
            'authUser' => $user,
            'notifications' => $notifications,
            'hasNotifications' => $notifications->isNotEmpty(),
        ]);
    }

    public function purchases(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $sales = Sale::with(['webinar.category'])
            ->where('buyer_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $transactions = Accounting::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(4)
            ->get();

        return view('panel_v1.student.pages.purchases', [
            'pageTitle' => 'عمليات الشراء الخاصة بي',
            'authUser' => $user,
            'sales' => $sales,
            'balance' => $user->getAccountingBalance(),
            'transactions' => $transactions,
            'subscribePlans' => Subscribe::orderBy('id')->get(),            'userPayouts' => \App\Models\Payout::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get(),
            'userSubscribes' => SubscribeUse::with(['subscribe'])
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get(),
        ]);
    }

    public function support(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $tickets = Support::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return view('panel_v1.student.pages.support', [
            'pageTitle' => 'الدعم',
            'authUser' => $user,
            'tickets' => $tickets,
            'courseTickets' => $tickets->whereNotNull('webinar_id')->values(),
            'departments' => SupportDepartment::orderBy('id')->get(),
            'enrolledCourses' => Sale::with('webinar')
                ->where('buyer_id', $user->id)
                ->whereNotNull('webinar_id')
                ->orderBy('id', 'desc')
                ->get()
                ->pluck('webinar')
                ->filter(),
        ]);
    }

    public function storeSupport(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'title' => 'required|min:2',
            'type' => 'required|in:platform_support,course_support',
            'department_id' => 'required_if:type,platform_support|exists:support_departments,id',
            'webinar_id' => 'required_if:type,course_support|exists:webinars,id',
            'message' => 'required|min:2',
        ]);

        $support = Support::create([
            'user_id' => $user->id,
            'department_id' => $request->input('type') === 'platform_support' ? $request->input('department_id') : null,
            'webinar_id' => $request->input('type') === 'course_support' ? $request->input('webinar_id') : null,
            'title' => $request->input('title'),
            'status' => 'open',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'attach' => null,
            'created_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.student.support')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إرسال رسالتك للدعم بنجاح',
                'type' => 'success',
            ]);
    }

    public function settings(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('panel_v1.student.pages.settings', [
            'pageTitle' => 'اعدادات الحساب',
            'authUser' => $user,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'full_name' => 'required|string|max:128',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:32|unique:users,mobile,' . $user->id,
            'language' => 'nullable|string|max:128',
            'timezone' => 'nullable|string|max:255',
            'newsletter' => 'nullable|boolean',
            'password' => 'nullable|min:6|confirmed',
            'current_password' => 'required_with:password',
        ]);

        if ($request->filled('password') && !Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => trans('validation.password_or_username')])->withInput();
        }

        $user->full_name = $request->input('full_name');
        $user->email = $request->input('email');
        $user->mobile = $request->input('mobile');
        $user->language = $request->input('language', $user->language);
        $user->timezone = $request->input('timezone', $user->timezone);
        $user->newsletter = $request->boolean('newsletter');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return redirect()
            ->route('panel.v1.student.settings')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم حفظ الإعدادات بنجاح',
                'type' => 'success',
            ]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $ids = $this->userNotificationsQuery($user)->pluck('notifications.id');

        $existing = NotificationStatus::where('user_id', $user->id)
            ->whereIn('notification_id', $ids)
            ->pluck('notification_id')
            ->all();

        $now = time();
        foreach ($ids->diff($existing) as $notificationId) {
            NotificationStatus::create([
                'user_id' => $user->id,
                'notification_id' => $notificationId,
                'seen_at' => $now,
            ]);
        }

        return redirect()
            ->route('panel.v1.student.notifications')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم وضع علامة مقروء على جميع الإشعارات',
                'type' => 'success',
            ]);
    }

    public function favorites(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('panel_v1.student.pages.favorites', [
            'pageTitle' => 'المفضلة',
            'authUser' => $user,
            'favorites' => \App\Models\Favorite::with(['webinar.category'])
                ->where('user_id', $user->id)
                ->whereNotNull('webinar_id')
                ->orderBy('id', 'desc')
                ->limit(30)
                ->get(),
        ]);
    }

    public function toggleFavorite(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate(['webinar_id' => 'required|exists:webinars,id']);

        $favorite = \App\Models\Favorite::where('user_id', $user->id)
            ->where('webinar_id', $request->input('webinar_id'))
            ->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'تمت الإزالة من المفضلة';
        } else {
            \App\Models\Favorite::create([
                'user_id' => $user->id,
                'webinar_id' => $request->input('webinar_id'),
                'created_at' => time(),
            ]);
            $message = 'تمت الإضافة إلى المفضلة';
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => $message, 'type' => 'success']);
    }

    public function notes(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $enrolledForNotes = Sale::with(['webinar'])
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->orderBy('id', 'desc')
            ->get()
            ->pluck('webinar')
            ->filter()
            ->unique('id')
            ->values();

        return view('panel_v1.student.pages.notes', [
            'pageTitle' => 'ملاحظاتي',
            'authUser' => $user,
            'enrolledForNotes' => $enrolledForNotes,
            'notes' => \App\Models\CoursePersonalNote::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(30)
                ->get(),
        ]);
    }

    public function storeNote(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'webinar_id' => 'required|exists:webinars,id',
            'note' => 'required|string|max:5000',
        ]);

        $webinar = \App\Models\Webinar::findOrFail($request->input('webinar_id'));

        \App\Models\CoursePersonalNote::create([
            'user_id' => $user->id,
            'course_id' => $webinar->id,
            'targetable_id' => $webinar->id,
            'targetable_type' => 'webinar',
            'note' => $request->input('note'),
            'created_at' => time(),
        ]);

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ الملاحظة', 'type' => 'success']);
    }

    public function deleteNote(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        \App\Models\CoursePersonalNote::where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف الملاحظة', 'type' => 'success']);
    }

    private function userNotificationsQuery($user)
    {
        $query = Notification::query()->where(function ($query) use ($user) {
            $query->where('notifications.user_id', $user->id)
                ->where('notifications.type', 'single');
        })->orWhere(function ($query) {
            $query->whereNull('notifications.user_id')
                ->whereNull('notifications.group_id')
                ->where('notifications.type', 'all_users');
        })->orWhere(function ($query) {
            $query->whereNull('notifications.user_id')
                ->whereNull('notifications.group_id')
                ->where('notifications.type', 'students');
        });

        $purchasedIds = $user->getPurchasedCoursesIds();

        if (!empty($purchasedIds)) {
            $query->orWhere(function ($query) use ($purchasedIds) {
                $query->whereIn('webinar_id', $purchasedIds)
                    ->where('type', 'course_students');
            });
        }

        return $query;
    }

    /**
     * @return \App\User|\Illuminate\Http\RedirectResponse
     */
    private function resolveStudent(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isUser()) {
            if ($user->isTeacher()) {
                return redirect()->route('panel.v1.instructor.home');
            }

            if ($user->isAdmin()) {
                return redirect()->route('panel.v1.admin.home');
            }

            if ($user->isOrganization()) {
                return redirect()->route('panel.v1.organization.home');
            }

            return redirect('/panel');
        }

        return $user;
    }
}
