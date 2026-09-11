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
    use \App\Http\Controllers\PanelV1\Support\ProfileSettingsTrait;
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

        $now = now();

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
            'calendarYear' => (int) $now->format('Y'),
            'calendarMonth' => (int) $now->format('n'),
            'calendarSelected' => (int) $now->format('j'),
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

        return view('panel_v1.student.pages.settings', array_merge([
            'pageTitle' => 'اعدادات الحساب',
            'authUser' => $user,
        ], $this->profileExtraViewData($request, $user), $this->profileAboutData($user), $this->profileFinancialData($user), [
            'loginHistories' => $this->profileLoginHistories($user),
        ]));
    }

    public function updateExtra(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'country_id' => 'nullable|integer|exists:regions,id',
            'province_id' => 'nullable|integer|exists:regions,id',
            'city_id' => 'nullable|integer|exists:regions,id',
            'district_id' => 'nullable|integer|exists:regions,id',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:man,woman',
            'meeting_type' => 'nullable|in:in_person,online,all',
        ]);

        $this->saveProfileExtra($request, $user);

        return redirect()->route('panel.v1.student.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ المعلومات الإضافية', 'type' => 'success']);
    }

    public function updateFinancial(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $this->saveProfileFinancial($request, $user);

        return redirect()->route('panel.v1.student.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ بيانات الهوية والمالية', 'type' => 'success']);
    }

    public function updateImages(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'avatar' => 'nullable|image|max:5120',
            'cover_img' => 'nullable|image|max:5120',
            'profile_secondary_image' => 'nullable|image|max:5120',
            'profile_video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:51200',
            'signature_img' => 'nullable|image|max:5120',
        ]);

        $this->saveProfileMedia($request, $user);

        return redirect()->route('panel.v1.student.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ الصور', 'type' => 'success']);
    }

    public function deleteMedia(string $type)
    {
        $user = request()->user();

        if (!$user || !$user->isUser()) {
            return redirect('/login');
        }

        if (!$this->deleteProfileMedia($user, $type)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف الملف', 'type' => 'success']);
    }

    public function updateAbout(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'about' => 'nullable|string|max:5000',
            'bio' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:255',
            'occupations' => 'nullable|array|max:10',
            'occupations.*' => 'integer|exists:categories,id',
        ]);

        $this->saveProfileAbout($request, $user);

        return redirect()->route('panel.v1.student.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ بيانات "حول"', 'type' => 'success']);
    }

    public function storeMeta(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$this->storeProfileMeta($user, $request->input('name'), $request->input('value'))) {
            return response()->json([], 422);
        }

        return response()->json(['code' => 200], 200);
    }

    public function updateMeta(Request $request, $metaId)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$this->updateProfileMeta($user, $metaId, $request->input('name'), $request->input('value'))) {
            return response()->json([], 422);
        }

        return response()->json(['code' => 200], 200);
    }

    public function deleteMeta(Request $request, $metaId)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$this->deleteProfileMeta($user, $metaId)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم الحذف', 'type' => 'success']);
    }

    public function storeAttachment(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $this->storeProfileAttachment($request, $user);

        return redirect()->route('panel.v1.student.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تمت إضافة المرفق', 'type' => 'success']);
    }

    public function updateAttachment(Request $request, $attachmentId)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (empty($this->updateProfileAttachment($request, $user, $attachmentId))) {
            abort(404);
        }

        return redirect()->route('panel.v1.student.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم تحديث المرفق', 'type' => 'success']);
    }

    public function deleteAttachment($attachmentId)
    {
        $user = request()->user();

        if (!$user || !$user->isUser()) {
            return redirect('/login');
        }

        if (!$this->deleteProfileAttachment($user, $attachmentId)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف المرفق', 'type' => 'success']);
    }

    public function endSession($sessionId)
    {
        $user = request()->user();

        if (!$user || !$user->isUser()) {
            return redirect('/login');
        }

        if (!$this->endProfileSession($user, $sessionId)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم إنهاء الجلسة', 'type' => 'success']);
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
            'public_message' => 'nullable|boolean',
            'enable_profile_statistics' => 'nullable|boolean',
            'auto_renew_subscription' => 'nullable|boolean',
            'offline' => 'nullable|boolean',
            'offline_message' => 'nullable|string|max:2000',
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

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        $this->saveProfileAccountOptions($request, $user);

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

    public function certificates(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $certificates = Certificate::with(['webinar', 'quiz'])
            ->where('student_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        $pendingCount = 0;
        try {
            $purchasedIds = $user->getPurchasedCoursesIds();
            $pendingQuizzes = \App\Models\Quiz::whereIn('webinar_id', $purchasedIds)
                ->where('certificate', true)
                ->where('status', \App\Models\Quiz::ACTIVE)
                ->whereDoesntHave('quizResults', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count();
            $pendingWebinars = \App\Models\Webinar::where('status', 'active')
                ->where('certificate', true)
                ->whereIn('id', $purchasedIds)
                ->whereDoesntHave('certificates', function ($q) use ($user) {
                    $q->where('student_id', $user->id);
                })->count();
            $pendingCount = $pendingQuizzes + $pendingWebinars;
        } catch (\Throwable $e) {
        }

        return view('panel_v1.student.pages.certificates', [
            'pageTitle' => 'شهاداتي',
            'authUser' => $user,
            'certificates' => $certificates,
            'pendingCount' => $pendingCount,
            'hasCerts' => $certificates->isNotEmpty(),
        ]);
    }

    public function downloadCertificate(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $certificate = Certificate::where('id', $id)->where('student_id', $user->id)->first();
        if (empty($certificate)) {
            abort(404);
        }
        $make = new \App\Mixins\Certificate\MakeCertificate();
        return $make->showCertificateByType($certificate);
    }

    public function assignmentsPage(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $purchasedIds = $user->getPurchasedCoursesIds();
        $myAssignments = WebinarAssignment::with(['webinar'])
            ->whereIn('webinar_id', $purchasedIds)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();
        $histories = WebinarAssignmentHistory::with(['assignment.webinar'])
            ->where('student_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();
        $pendingIds = $histories->pluck('assignment_id')->filter()->unique()->all();
        $pending = $myAssignments->whereNotIn('id', $pendingIds)->values();

        return view('panel_v1.student.pages.assignments', [
            'pageTitle' => 'تكليفاتي',
            'authUser' => $user,
            'myAssignments' => $myAssignments,
            'histories' => $histories,
            'pendingAssignments' => $pending,
        ]);
    }

    public function quizzesPage(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $results = QuizzesResult::with(['quiz.webinar'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();
        $purchasedIds = $user->getPurchasedCoursesIds();
        $pendingQuizzes = collect();
        if (!empty($purchasedIds)) {
            $pendingQuizzes = \App\Models\Quiz::whereIn('webinar_id', $purchasedIds)
                ->where('status', 'active')
                ->whereDoesntHave('quizResults', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();
        }
        $grouped = $results->groupBy('quiz_id');
        foreach ($grouped as $quizId => $group) {
            $first = $group->first();
            $quiz = $first->quiz ?? null;
            $canTry = false;
            if ($quiz && (!isset($quiz->attempt) || count($group) < (int) $quiz->attempt) && $first->status !== 'passed') {
                $canTry = true;
            }
            foreach ($group as $item) {
                $item->can_try = $canTry;
            }
        }

        return view('panel_v1.student.pages.quizzes', [
            'pageTitle' => 'اختباراتي',
            'authUser' => $user,
            'quizResults' => $results,
            'pendingQuizzes' => $pendingQuizzes,
        ]);
    }

    public function commentsPage(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $comments = Comment::with(['webinar'])
            ->where('user_id', $user->id)
            ->whereNotNull('webinar_id')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        return view('panel_v1.student.pages.comments', [
            'pageTitle' => 'تعليقاتي',
            'authUser' => $user,
            'comments' => $comments,
        ]);
    }

    public function deleteComment(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        Comment::where('id', $id)->where('user_id', $user->id)->delete();
        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف التعليق', 'type' => 'success']);
    }

    public function meetings(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $reserveMeetings = \App\Models\ReserveMeeting::with(['meeting.creator', 'meetingTime', 'sale'])
            ->where('user_id', $user->id)
            ->whereNotNull('reserved_at')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();
        $openCount = $reserveMeetings->where('status', \App\Models\ReserveMeeting::$open)->count();
        $finishedCount = $reserveMeetings->where('status', \App\Models\ReserveMeeting::$finished)->count();

        return view('panel_v1.student.pages.meetings', [
            'pageTitle' => 'حجوزاتي الاستشارية',
            'authUser' => $user,
            'reserveMeetings' => $reserveMeetings,
            'openCount' => $openCount,
            'finishedCount' => $finishedCount,
        ]);
    }

    public function meetingsJoin(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $reserve = \App\Models\ReserveMeeting::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        if ($reserve->meeting_type === 'in_person' || $reserve->status !== \App\Models\ReserveMeeting::$open || (empty($reserve->link) && empty($reserve->session))) {
            return back()->with('toast', ['title' => 'تنبيه', 'msg' => 'الجلسة غير متاحة للانضمام حالياً', 'type' => 'error']);
        }
        $link = $reserve->link;
        if (!empty($reserve->session)) {
            $link = $reserve->session->getJoinLink();
        }
        if (empty($link)) {
            abort(403);
        }
        return \Illuminate\Support\Facades\Redirect::away($link);
    }

    public function meetingsFinish(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $reserve = \App\Models\ReserveMeeting::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $reserve->update(['status' => \App\Models\ReserveMeeting::$finished]);
        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم إنهاء الجلسة', 'type' => 'success']);
    }

    public function noticeboards(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $purchasedIds = $user->getPurchasedCoursesIds();
        $courseNoticeboards = \App\Models\CourseNoticeboard::with(['webinar'])
            ->whereIn('webinar_id', $purchasedIds)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();
        // also general noticeboards from instructors of purchased courses
        $instructorIds = \App\Models\Webinar::whereIn('id', $purchasedIds)->pluck('teacher_id')->filter()->unique()->values()->all();
        $generalNoticeboards = \App\Models\Noticeboard::where(function($q) use ($user, $instructorIds){
                $q->where('type','students')->orWhere(function($q) use ($instructorIds){
                    $q->whereIn('instructor_id', $instructorIds);
                });
            })
            ->orderBy('created_at','desc')
            ->limit(30)
            ->get();
        $all = $courseNoticeboards->merge($generalNoticeboards)->sortByDesc('created_at')->values();
        // unread via getUnreadNoticeboards helper if exists
        $unreadCount = 0;
        try { $unreadCount = count($user->getUnreadNoticeboards()); } catch(\Throwable $e) {}

        return view('panel_v1.student.pages.noticeboards', [
            'pageTitle' => 'إعلانات الدورات',
            'authUser' => $user,
            'noticeboards' => $all,
            'courseNoticeboards' => $courseNoticeboards,
            'generalNoticeboards' => $generalNoticeboards,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function noticeboardSeen(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        // try both types
        $cn = \App\Models\CourseNoticeboard::find($id);
        if ($cn) {
            \App\Models\CourseNoticeboardStatus::firstOrCreate(
                ['user_id'=>$user->id,'noticeboard_id'=>$id],
                ['seen_at'=>time()]
            );
        } else {
            \App\Models\NoticeboardStatus::firstOrCreate(
                ['user_id'=>$user->id,'noticeboard_id'=>$id],
                ['seen_at'=>time()]
            );
        }
        return back()->with('toast', ['title'=>'تم','msg'=>'تم وضع علامة مقروء','type'=>'success']);
    }

    public function rewards(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $accountings = \App\Models\RewardAccounting::where('user_id',$user->id)->orderBy('id','desc')->limit(30)->get();
        $totalPoints = (int) \App\Models\RewardAccounting::where('user_id',$user->id)->sum('score');
        $availableRewards = \App\Models\Reward::where('status','active')->orderBy('id')->get();
        $settings = getRewardsSettings();
        $exchangeableUnit = $settings['exchangeable_unit'] ?? 1;
        $exchangeableWorth = $settings['exchangeable_worth'] ?? 0;

        return view('panel_v1.student.pages.rewards', [
            'pageTitle' => 'نقاطي ومكافآتي',
            'authUser' => $user,
            'accountings' => $accountings,
            'totalPoints' => $totalPoints,
            'availableRewards' => $availableRewards,
            'exchangeableUnit' => $exchangeableUnit,
            'exchangeableWorth' => $exchangeableWorth,
        ]);
    }

    public function rewardExchange(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $request->validate(['amount'=>'required|integer|min:1']);
        $amount = (int) $request->input('amount');
        $totalPoints = (int) \App\Models\RewardAccounting::where('user_id',$user->id)->sum('score');
        // spent points are negative scores? In original, exchange creates negative. Simplified: check enough
        if ($amount > $totalPoints) {
            return back()->with('toast',['title'=>'تنبيه','msg'=>'النقاط غير كافية','type'=>'error']);
        }
        $settings = getRewardsSettings();
        $worth = $settings['exchangeable_worth'] ?? 0;
        $unit = $settings['exchangeable_unit'] ?? 1;
        $charge = ($amount / $unit) * $worth;
        \App\Models\RewardAccounting::create([
            'user_id'=>$user->id,
            'score'=> -$amount,
            'type'=>'exchange',
            'item_id'=>null,
            'created_at'=>time(),
        ]);
        // also credit accounting
        if ($charge > 0) {
            \App\Models\Accounting::create([
                'user_id'=>$user->id,
                'type'=>'addiction',
                'amount'=>$charge,
                'description'=>'تحويل نقاط',
                'created_at'=>time(),
            ]);
        }
        return back()->with('toast',['title'=>'تم','msg'=>'تم تحويل النقاط إلى رصيد','type'=>'success']);
    }

    public function attendances(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $attendances = \App\Models\SessionAttendance::with(['session.webinar'])
            ->where('student_id',$user->id)
            ->orderBy('id','desc')
            ->limit(30)
            ->get();
        $myAttendances = \App\Models\SessionAttendance::where('student_id',$user->id)->count();
        $present = \App\Models\SessionAttendance::where('student_id',$user->id)->where('status','present')->count();

        return view('panel_v1.student.pages.attendances', [
            'pageTitle' => 'سجل الحضور',
            'authUser' => $user,
            'attendances' => $attendances,
            'myAttendances' => $myAttendances,
            'presentCount' => $present,
        ]);
    }

    public function upcomingCourses(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $upcoming = \App\Models\UpcomingCourse::where('status','pending')->orderBy('id','desc')->limit(20)->get();
        $followings = \App\Models\UpcomingCourseFollower::where('user_id',$user->id)->pluck('upcoming_course_id')->toArray();
        return view('panel_v1.student.pages.upcoming', [
            'pageTitle' => 'الدورات القادمة',
            'authUser' => $user,
            'upcomingCourses' => $upcoming,
            'followings' => $followings,
        ]);
    }

    public function upcomingFollow(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $course = \App\Models\UpcomingCourse::findOrFail($id);
        \App\Models\UpcomingCourseFollower::firstOrCreate(['user_id'=>$user->id,'upcoming_course_id'=>$course->id]);
        return back()->with('toast',['title'=>'تم','msg'=>'تمت المتابعة','type'=>'success']);
    }

    public function upcomingUnfollow(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        \App\Models\UpcomingCourseFollower::where('user_id',$user->id)->where('upcoming_course_id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم إلغاء المتابعة','type'=>'success']);
    }

    public function forums(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $topics = \App\Models\ForumTopic::with(['creator','posts'])
            ->orderBy('id','desc')->limit(20)->get();
        $posts = \App\Models\ForumTopicPost::with(['topic','user'])
            ->where('user_id',$user->id)->orderBy('id','desc')->limit(20)->get();
        $bookmarks = \App\Models\ForumTopicBookmark::with(['topic'])
            ->where('user_id',$user->id)->orderBy('id','desc')->limit(20)->get();
        return view('panel_v1.student.pages.forums', [
            'pageTitle' => 'منتدياتي',
            'authUser' => $user,
            'topics' => $topics,
            'posts' => $posts,
            'bookmarks' => $bookmarks,
        ]);
    }

    public function forumBookmarkToggle(Request $request, int $topicId)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $existing = \App\Models\ForumTopicBookmark::where('user_id',$user->id)->where('topic_id',$topicId)->first();
        if ($existing) {
            $existing->delete();
            $msg='تمت الإزالة من المحفوظات';
        } else {
            \App\Models\ForumTopicBookmark::create(['user_id'=>$user->id,'topic_id'=>$topicId]);
            $msg='تم الحفظ';
        }
        return back()->with('toast',['title'=>'تم','msg'=>$msg,'type'=>'success']);
    }

    public function updateComment(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $request->validate(['comment'=>'required|string|max:2000']);
        $c = Comment::where('id',$id)->where('user_id',$user->id)->firstOrFail();
        $c->update(['comment'=>$request->input('comment')]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم تحديث التعليق','type'=>'success']);
    }

    public function reportComment(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $request->validate(['reason'=>'required|string|max:500']);
        $c = Comment::findOrFail($id);
        \App\Models\CommentReport::firstOrCreate(
            ['user_id'=>$user->id,'comment_id'=>$c->id],
            ['reason'=>$request->input('reason'),'created_at'=>time()]
        );
        return back()->with('toast',['title'=>'تم','msg'=>'تم الإبلاغ','type'=>'success']);
    }

    public function installments(Request $request)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $orders = \App\Models\InstallmentOrder::with(['installment','selectedInstallment'])
            ->where('user_id',$user->id)->orderBy('id','desc')->limit(20)->get();
        $userPackages = \App\Models\UserRegistrationPackage::with(['registrationPackage'])
            ->where('user_id',$user->id)->orderBy('id','desc')->limit(20)->get();
        $packages = \App\Models\RegistrationPackage::where('status','active')->orderBy('id')->get();
        return view('panel_v1.student.pages.installments', [
            'pageTitle' => 'الأقساط والباقات',
            'authUser' => $user,
            'orders' => $orders,
            'userPackages' => $userPackages,
            'packages' => $packages,
        ]);
    }

    public function supportShow(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $tickets = Support::where('user_id', $user->id)->orderBy('id', 'desc')->limit(20)->get();
        $ticket = Support::with(['department', 'webinar', 'conversations.sender', 'conversations.supporter'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        $courseTickets = $tickets->whereNotNull('webinar_id')->values();

        return view('panel_v1.student.pages.support-thread', [
            'pageTitle' => 'تفاصيل التذكرة',
            'authUser' => $user,
            'tickets' => $tickets,
            'ticket' => $ticket,
            'courseTickets' => $courseTickets,
            'departments' => SupportDepartment::orderBy('id')->get(),
        ]);
    }

    public function storeSupportConversation(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $request->validate(['message' => 'required|string|min:2', 'attach' => 'nullable|file|max:10240']);
        $support = Support::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        if ($support->status === 'close') {
            return back()->with('toast', ['title' => 'تنبيه', 'msg' => 'التذكرة مغلقة', 'type' => 'error']);
        }
        $support->update(['status' => 'open', 'updated_at' => time()]);
        $conv = SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'attach' => null,
            'created_at' => time(),
        ]);
        if ($request->hasFile('attach')) {
            $path = $this->uploadFile($request->file('attach'), "supports/{$support->id}/conversations", "attach_{$conv->id}", $user->id);
            $conv->update(['attach' => $path]);
        }
        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم إرسال الرد', 'type' => 'success']);
    }

    public function closeSupport(Request $request, int $id)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $support = Support::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $support->update(['status' => 'close', 'updated_at' => time()]);
        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم إغلاق التذكرة', 'type' => 'success']);
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
