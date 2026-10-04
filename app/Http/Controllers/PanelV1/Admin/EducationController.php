<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Exports\WebinarsExport;
use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EducationController extends AdminController
{
    use \App\Http\Controllers\PanelV1\Support\CourseWizardTrait;

    public function home(Request $request)
    {
        $activeCount = (int) \App\Models\Webinar::where('status', 'active')->count();
        $pendingCount = (int) \App\Models\Webinar::where('status', 'pending')->count();
        $draftCount = (int) \App\Models\Webinar::where('status', 'is_draft')->count();
        $inactiveCount = (int) \App\Models\Webinar::where('status', 'inactive')->count();

        $stats = [
            ['label' => 'إجمالي الدورات', 'value' => (string) \App\Models\Webinar::count()],
            ['label' => 'نشطة', 'value' => (string) $activeCount],
            ['label' => 'بانتظار المراجعة', 'value' => (string) $pendingCount],
            ['label' => 'مسودات', 'value' => (string) $draftCount],
            ['label' => 'الباقات', 'value' => (string) \App\Models\Bundle::count()],
            ['label' => 'الجلسات المباشرة', 'value' => (string) \App\Models\Session::count()],
        ];

        $latestCourses = \App\Models\Webinar::with(['category'])->orderBy('id', 'desc')->limit(5)->get()->map(function ($w) {
            $statusMeta = $this->webinarStatusMeta($w->status);

            return [
                'title' => $w->title,
                'category' => $w->category->title ?? '',
                'status' => $statusMeta['label'],
                'statusTone' => $statusMeta['tone'],
                'type' => $w->type ?? $w->category->title ?? 'دورة',
                'price' => $w->price ? handlePrice($w->price) : 'مجانية',
            ];
        })->all();

        $activityChart = $this->buildAcademicActivityChart();

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.home',
            'لوحة التعليم والأكاديميات',
            array_merge(AdminMockData::shell('education', 'home'), [
                'welcomeTitle' => 'مرحباً بك في لوحة التعليم',
                'welcomeSubtitle' => 'نظرة عامة على الأكاديميات والدورات',
                'stats' => $stats,
                'statsCards' => $stats,
                'latestCourses' => $latestCourses,
                'activityChart' => $activityChart,
                'chartMetrics' => [
                    ['label' => 'نشطة', 'value' => $activeCount],
                    ['label' => 'قيد المراجعة', 'value' => $pendingCount],
                    ['label' => 'مسودات', 'value' => $draftCount],
                    ['label' => 'مرفوضة', 'value' => $inactiveCount],
                ],
            ])
        );
    }

    /** @return array{label: string, tone: string} */
    private function webinarStatusMeta(?string $status): array
    {
        return match ($status) {
            'active' => ['label' => 'نشط', 'tone' => 'success'],
            'pending' => ['label' => 'بانتظار المراجعة', 'tone' => 'warning'],
            'is_draft' => ['label' => 'مسودة', 'tone' => 'info'],
            'inactive' => ['label' => 'مرفوض', 'tone' => 'danger'],
            default => ['label' => $status ?: '—', 'tone' => 'info'],
        };
    }

    /** Last 7 days enrollments + new courses for ApexCharts (unix timestamps). */
    private function buildAcademicActivityChart(): array
    {
        $dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $labels = [];
        $enrollments = [];
        $newCourses = [];

        $todayStart = strtotime('today');

        for ($i = 6; $i >= 0; $i--) {
            $start = $todayStart - ($i * 86400);
            $end = $start + 86399;
            $labels[] = $dayNames[(int) date('w', $start)];

            $enrollments[] = (int) \App\Models\Sale::query()
                ->whereNotNull('webinar_id')
                ->whereNull('refund_at')
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $newCourses[] = (int) \App\Models\Webinar::query()
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'التسجيلات', 'data' => $enrollments],
                ['name' => 'دورات جديدة', 'data' => $newCourses],
            ],
        ];
    }

    public function section(Request $request, string $section)
    {
        $shell = AdminMockData::shell('education', $section);
        $real = [];
        $title = $section;
        $search = trim((string)$request->input('search',''));
        switch($section){
            case 'courses':
                $courseType = $request->input('type');
                $typeLabels = [
                    'webinar' => 'دورات مباشرة',
                    'course' => 'دورات مسجلة',
                    'text_lesson' => 'دورات كتابية',
                ];
                $title = $typeLabels[$courseType] ?? 'جميع الدورات';
                $q = \App\Models\Webinar::query()
                    ->with(['category', 'teacher'])
                    ->withCount([
                        'sales as sales_count' => fn ($sq) => $sq->whereNull('refund_at'),
                    ])
                    ->withSum([
                        'sales as sales_amount' => fn ($sq) => $sq->whereNull('refund_at'),
                    ], 'total_amount')
                    ->orderBy('id', 'desc');
                if (in_array($courseType, ['webinar', 'course', 'text_lesson'], true)) {
                    $q->where('type', $courseType);
                    $real['adminActive'] = 'courses-'.$courseType;
                }
                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('id', $search);
                        $qq->orWhereHas('translations', fn ($t) => $t->where('title', 'like', "%{$search}%"));
                        $qq->orWhereHas('teacher', fn ($t) => $t->where('full_name', 'like', "%{$search}%"));
                    });
                }
                if ($cat = $request->input('category_id')) {
                    $q->where('category_id', $cat);
                }
                if ($st = $request->input('status')) {
                    $q->where('status', $st);
                }
                $real['courses'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['courses'];
                $real['filterCategories'] = \App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn ($c) => ['id' => $c->id, 'title' => $c->title])->all();
                $real['courseType'] = $courseType;
                $real['courseListStats'] = [
                    [
                        'label' => 'الدورات النشطة',
                        'value' => (string) \App\Models\Webinar::when(
                            in_array($courseType, ['webinar', 'course', 'text_lesson'], true),
                            fn ($qq) => $qq->where('type', $courseType)
                        )->where('status', 'active')->count(),
                        'icon' => 'icon-[tabler--book]',
                    ],
                    [
                        'label' => 'الطلاب المسجلين',
                        'value' => (string) \App\Models\Sale::whereNotNull('webinar_id')->whereNull('refund_at')->distinct('buyer_id')->count('buyer_id'),
                        'icon' => 'icon-[tabler--school]',
                    ],
                    [
                        'label' => 'جميع الإختبارات',
                        'value' => (string) \App\Models\Quiz::count(),
                        'icon' => 'icon-[tabler--message-question]',
                    ],
                    [
                        'label' => 'الشهادات الصادرة',
                        'value' => (string) \App\Models\Certificate::count(),
                        'icon' => 'icon-[tabler--certificate]',
                    ],
                ];
                $real['stubTitle'] = $title;
                $real['stubSubtitle'] = match ($courseType) {
                    'webinar' => 'دورات وجلسات مباشرة تُبث في مواعيد محددة مع المدرب.',
                    'text_lesson' => 'دورات نصية ومحتوى مقروء يمكن للطلاب دراسته في أي وقت.',
                    'course' => 'دورات فيديو مُعدّة مسبقاً يمكن للطلاب مشاهدتها في أي وقت — تعلّم مرن حسب جدولك.',
                    default => 'جميع أنواع الدورات في المنصة.',
                };
                break;
            case 'bundles':
                $title='حزم الدورات';
                $q = \App\Models\Bundle::orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $real['bundles'] = $q->paginate(15)->withQueryString();
                $real['paginator'] = $real['bundles'];
                $real['stubTitle']=$title;
                break;
            case 'assignments':
                $title = 'جميع التكليفات والواجبات';
                $q = \App\Models\WebinarAssignment::query()
                    ->with(['webinar', 'translations'])
                    ->withCount('instructorAssignmentHistories as submissions_count')
                    ->withCount([
                        'instructorAssignmentHistories as pending_count' => fn ($hq) => $hq->where('status', \App\Models\WebinarAssignmentHistory::$pending),
                    ])
                    ->withCount([
                        'instructorAssignmentHistories as graded_count' => fn ($hq) => $hq->whereIn('status', [
                            \App\Models\WebinarAssignmentHistory::$passed,
                            \App\Models\WebinarAssignmentHistory::$notPassed,
                        ]),
                    ])
                    ->withAvg('instructorAssignmentHistories as avg_grade', 'grade')
                    ->orderBy('id', 'desc');

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('webinar_assignments.id', $search)
                            ->orWhereTranslationLike('title', "%{$search}%")
                            ->orWhereHas('webinar.translations', fn ($t) => $t->where('title', 'like', "%{$search}%"));
                    });
                }
                if ($st = $request->input('status')) {
                    $q->where('status', $st);
                }

                $real['assignments'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['assignments'];

                // Enrolled students per webinar for ratio (batch)
                $webinarIds = $real['assignments']->getCollection()->pluck('webinar_id')->filter()->unique()->values()->all();
                $enrolledByWebinar = [];
                if (!empty($webinarIds)) {
                    $enrolledByWebinar = \App\Models\Sale::query()
                        ->whereIn('webinar_id', $webinarIds)
                        ->whereNull('refund_at')
                        ->selectRaw('webinar_id, count(distinct buyer_id) as cnt')
                        ->groupBy('webinar_id')
                        ->pluck('cnt', 'webinar_id')
                        ->all();
                }
                $real['assignmentEnrolledByWebinar'] = $enrolledByWebinar;

                $pendingAll = (int) \App\Models\WebinarAssignmentHistory::where('status', \App\Models\WebinarAssignmentHistory::$pending)->count();
                $gradedAll = (int) \App\Models\WebinarAssignmentHistory::whereIn('status', [
                    \App\Models\WebinarAssignmentHistory::$passed,
                    \App\Models\WebinarAssignmentHistory::$notPassed,
                ])->count();
                $avgAll = \App\Models\WebinarAssignmentHistory::whereNotNull('grade')->avg('grade');
                $avgPct = $avgAll !== null ? (int) round((float) $avgAll) : 0;

                $real['assignmentListStats'] = [
                    ['label' => 'جميع التكليفات', 'value' => ((int) \App\Models\WebinarAssignment::count()) . ' تكليف', 'icon' => 'icon-[tabler--school]'],
                    ['label' => 'بانتظار التصحيح', 'value' => $pendingAll . ' تسليمات', 'icon' => 'icon-[tabler--school]'],
                    ['label' => 'تم تصحيحها', 'value' => $gradedAll . ' تسليم', 'icon' => 'icon-[tabler--school]'],
                    ['label' => 'متوسط الدرجات', 'value' => $avgPct . '%', 'icon' => 'icon-[tabler--school]'],
                ];
                $real['stubTitle'] = $title;
                $real['stubSubtitle'] = 'متابعة وتقييم المهام الدراسية للطلاب';
                break;
            case 'quizzes':
                $title = 'جميع الاختبارات';
                $q = \App\Models\Quiz::query()
                    ->with(['webinar.teacher', 'teacher', 'translations'])
                    ->withCount('quizQuestions as questions_count')
                    ->withCount([
                        'quizResults as passed_count' => fn ($rq) => $rq->where('status', \App\Models\QuizzesResult::$passed),
                    ])
                    ->withAvg('quizResults as avg_grade', 'user_grade')
                    ->addSelect([
                        'students_count' => \App\Models\QuizzesResult::selectRaw('count(distinct user_id)')
                            ->whereColumn('quiz_id', 'quizzes.id'),
                    ])
                    ->orderBy('id', 'desc');

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('quizzes.id', $search)
                            ->orWhereTranslationLike('title', "%{$search}%")
                            ->orWhereHas('webinar.translations', fn ($t) => $t->where('title', 'like', "%{$search}%"))
                            ->orWhereHas('teacher', fn ($t) => $t->where('full_name', 'like', "%{$search}%"));
                    });
                }
                if ($st = $request->input('status')) {
                    $q->where('status', $st);
                }
                if ($from = $request->input('from')) {
                    $ts = strtotime($from . ' 00:00:00');
                    if ($ts) {
                        $q->where('created_at', '>=', $ts);
                    }
                }
                if ($to = $request->input('to')) {
                    $ts = strtotime($to . ' 23:59:59');
                    if ($ts) {
                        $q->where('created_at', '<=', $ts);
                    }
                }

                $real['quizzes'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['quizzes'];

                $totalQuizzes = (int) \App\Models\Quiz::count();
                $activeQuizzes = (int) \App\Models\Quiz::where('status', 'active')->count();
                $allStudents = (int) \App\Models\QuizzesResult::distinct('user_id')->count('user_id');
                $passedStudents = (int) \App\Models\QuizzesResult::where('status', \App\Models\QuizzesResult::$passed)->distinct('user_id')->count('user_id');

                $real['quizListStats'] = [
                    ['label' => 'كل الاختبارات', 'value' => $totalQuizzes . ' اختبار', 'icon' => 'icon-[tabler--school]'],
                    ['label' => 'الاختبارات النشطة', 'value' => $activeQuizzes . ' اختبارات', 'icon' => 'icon-[tabler--school]'],
                    ['label' => 'جميع الطلاب', 'value' => $allStudents . ' طالب', 'icon' => 'icon-[tabler--school]'],
                    ['label' => 'الطلاب الناجحين', 'value' => $passedStudents . ' طالب', 'icon' => 'icon-[tabler--school]'],
                ];
                $real['stubTitle'] = $title;
                $real['stubSubtitle'] = 'إعداد وإدارة التقييمات الأكاديمية والامتحانات الإلكترونية';
                break;
            case 'certificates':
                $title='الشهادات والاعتمادات';
                $q = \App\Models\Certificate::with(['webinar','student','quiz.webinar','bundle'])->orderBy('id','desc');
                if($search !== '') $q->where('id',$search)->orWhereHas('student', fn($sq)=>$sq->where('full_name','like',"%{$search}%"));
                if($type=$request->input('type')) $q->where('type',$type);
                $real['certificates'] = $q->paginate(15)->withQueryString();
                $real['paginator'] = $real['certificates'];
                $real['certificateStats'] = [
                    ['label'=>'إجمالي الشهادات','value'=>(string)\App\Models\Certificate::count(),'icon'=>'icon-[tabler--certificate]'],
                    ['label'=>'إتمام دورة','value'=>(string)\App\Models\Certificate::where('type','course')->count(),'icon'=>'icon-[tabler--school]'],
                    ['label'=>'اختبارات','value'=>(string)\App\Models\Certificate::where('type','quiz')->count(),'icon'=>'icon-[tabler--list-check]'],
                    ['label'=>'حزم','value'=>(string)\App\Models\Certificate::where('type','bundle')->count(),'icon'=>'icon-[tabler--package]'],
                ];
                $real['certificateTemplates'] = \App\Models\CertificateTemplate::with('translations')->orderBy('id','desc')->limit(10)->get();
                $real['qiecCertificatePreview'] = asset('assets/panel_v1/img/certificate.jpg');
                $real['qiecTemplateReady'] = is_file(public_path('assets/panel_v1/img/certificate.jpg'));
                $real['stubTitle']=$title;
                break;
            case 'live':
                $title='البث المباشر';
                $q = \App\Models\Session::with(['webinar'])->where('status','active')->orderBy('date','desc');
                if($search !== '') $q->where('id',$search);
                $real['lives'] = $q->paginate(15)->withQueryString();
                $real['paginator'] = $real['lives'];
                $real['stubTitle']=$title;
                break;
            case 'events':
                $title='الفعاليات';
                if(class_exists(\App\Models\Event::class)){
                    $q = \App\Models\Event::with(['category','creator'])->withMin('tickets','price')->withMax('tickets','price')->orderBy('id','desc');
                    // فلترة حرفية من Admin\EventsController::handleFilters
                    if($search !== '') $q->whereTranslationLike('title', "%{$search}%");
                    if($cid=$request->input('category_id')) $q->where('category_id',$cid);
                    if($tp=$request->input('type')) $q->where('type',$tp);
                    if($st=$request->input('status')) $q->where('status',$st);
                    if($tid=$request->input('teacher_id')) $q->where('creator_id',$tid);
                    $real['events'] = $q->paginate(15)->withQueryString();
                    $real['paginator'] = $real['events'];
                    $real['eventStats'] = [
                        ['label'=>'الإجمالي','value'=>(string)\App\Models\Event::count(),'icon'=>'icon-[tabler--calendar-event]'],
                        ['label'=>'مجدولة','value'=>(string)\App\Models\Event::where('start_date','>',time())->count(),'icon'=>'icon-[tabler--clock]'],
                        ['label'=>'مسودات','value'=>(string)\App\Models\Event::where('status','draft')->count(),'icon'=>'icon-[tabler--file-text]'],
                    ];
                    $real['filterCategories'] = \App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
                } else { $real['events'] = collect(); $real['eventStats']=[]; }
                $real['stubTitle']=$title;
                break;
            case 'reviews':
                $title = 'المراجعات والتقييمات';
                $q = \App\Models\WebinarReview::with(['webinar.translations', 'bundle.translations', 'event', 'creator'])
                    ->where(function ($inner) {
                        $inner->whereNotNull('webinar_id')
                            ->orWhereNotNull('bundle_id')
                            ->orWhereNotNull('event_id');
                    })
                    ->orderBy('id', 'desc');
                if ($search !== '') {
                    $q->where(function ($inner) use ($search) {
                        $inner->where('id', $search)
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('creator', fn ($u) => $u->where('full_name', 'like', "%{$search}%"))
                            ->orWhereHas('webinar.translations', fn ($t) => $t->where('title', 'like', "%{$search}%"));
                    });
                }
                $statusFilter = trim((string) $request->input('status', ''));
                if (in_array($statusFilter, ['active', 'pending'], true)) {
                    $q->where('status', $statusFilter);
                }
                $real['courseReviews'] = $q->paginate(15)->withQueryString();
                $real['paginator'] = $real['courseReviews'];
                $real['reviewStats'] = [
                    [
                        'label' => 'تقييمات الدورات',
                        'value' => (string) \App\Models\WebinarReview::whereNotNull('webinar_id')->count(),
                        'icon' => 'icon-[tabler--book]',
                    ],
                    [
                        'label' => 'متوسط تقييم الدورة',
                        'value' => (($avg = \App\Models\WebinarReview::whereNotNull('webinar_id')->where('status', 'active')->avg('content_quality')) ? number_format((float) $avg, 1) . '/5' : '—'),
                        'icon' => 'icon-[tabler--star]',
                    ],
                    [
                        'label' => 'متوسط تقييم المدرب',
                        'value' => (($avg = \App\Models\WebinarReview::whereNotNull('webinar_id')->where('status', 'active')->avg('instructor_skills')) ? number_format((float) $avg, 1) . '/5' : '—'),
                        'icon' => 'icon-[tabler--user-star]',
                    ],
                    [
                        'label' => 'بانتظار الاعتماد',
                        'value' => (string) \App\Models\WebinarReview::whereNotNull('webinar_id')->where('status', 'pending')->count(),
                        'icon' => 'icon-[tabler--clock]',
                    ],
                ];
                $real['stubTitle'] = $title;
                break;
            case 'departments':
                $title='الأقسام والتصنيفات';
                $q = \App\Models\Category::whereNull('parent_id')->orderBy('order');
                if($search !== '') $q->where('id',$search)->orWhereHas('translations', fn($t)=>$t->where('title','like',"%{$search}%"));
                $real['departments']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['departments'];
                $real['stubTitle']=$title;
                break;
            case 'attendance':
                $title='الحضور والغياب';
                $q = \App\Models\SessionAttendance::with(['session.webinar'])->orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $real['attendances']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['attendances'];
                $real['stubTitle']=$title;
                break;
            case 'filters':
                $title='الفلاتر'; $q=\App\Models\Filter::with('category')->orderBy('id','desc'); if($search!=='') $q->whereHas('translations',fn($t)=>$t->where('title','like',"%{$search}%")); $real['filters']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['filters']; $real['stubTitle']=$title; break;
            case 'trends':
                $title='التصنيفات الرائجة'; $q=\App\Models\TrendCategory::with('category')->orderBy('created_at','desc'); $real['trends']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['trends']; $real['stubTitle']=$title; break;
            case 'enrollment':
                $title='التسجيل'; $q=\App\Models\Sale::whereNotNull('webinar_id')->with(['buyer','webinar'])->orderBy('created_at','desc'); if($search!=='') $q->where('id',$search); $real['sales']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['sales']; $real['stubTitle']=$title; break;
            case 'upcoming':
                $title='الدورات القادمة'; $q=\App\Models\UpcomingCourse::withCount('followers')->with('teacher')->orderBy('created_at','desc'); if($search!=='') $q->whereTranslationLike('title',"%{$search}%"); $real['upcomingCourses']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['upcomingCourses']; $real['stubTitle']=$title; break;
            case 'waitlists':
                $title='قوائم الانتظار'; $q=\App\Models\Webinar::where('enable_waitlist',true); $p=$q->paginate(15)->withQueryString(); foreach($p as $w){ $wq=\App\Models\Waitlist::where('webinar_id',$w->id); $w->members=$wq->count(); } $real['waitlists']=$p; $real['paginator']=$p; $real['stubTitle']=$title; break;
            case 'statistics':
                $title='الإحصائيات'; $real['stats']=[['label'=>'إجمالي الدورات','value'=>(string)\App\Models\Webinar::count(),'icon'=>'icon-[tabler--book]'],['label'=>'نشطة','value'=>(string)\App\Models\Webinar::where('status','active')->count(),'icon'=>'icon-[tabler--check]']]; $real['stubTitle']=$title; break;
            case 'noticeboard':
                $title='الإعلانات'; $q=\App\Models\Noticeboard::orderBy('created_at','desc'); $real['noticeboards']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['noticeboards']; $real['stubTitle']=$title; break;
            case 'forums':
            case 'notifications':
            case 'registration':
            case 'attendance-history':
                abort(404, 'الصفحة غير موجودة — تم تنظيم النظام');
            default:
                $meta = AdminMockData::stubMeta('education', $section);
                $title = $meta['stubTitle'] ?? $section;
                $real = $meta;
                break;
        }
        $data = array_merge($shell, $real, ['stubTitle'=>$title]);
        if ($section === 'courses') {
            $view = 'panel_v1.admin.pages.education.courses-list';
        } elseif ($section === 'quizzes') {
            $view = 'panel_v1.admin.pages.education.quizzes-list';
        } elseif ($section === 'assignments') {
            $view = 'panel_v1.admin.pages.education.assignments-list';
        } else {
            $view = in_array($section,['courses','bundles','departments','events','quizzes','assignments','certificates','reviews','live','attendance','filters','trends','enrollment','upcoming','waitlists','statistics','noticeboard']) ? 'panel_v1.admin.pages.education.section-real' : 'panel_v1.admin.pages.education.stub';
        }

        // fallback to stub if real view not exists
        if(!view()->exists($view)) $view='panel_v1.admin.pages.education.stub';

        return $this->renderAdmin(
            $request,
            $view,
            $title,
            $data
        );
    }

    public function createDepartment(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.department-form','إنشاء قسم جديد',array_merge(AdminMockData::shell('education','departments'),['category'=>null,'subCategories'=>collect(),'formAction'=>route('panel.v1.admin.education.departments.store')]));
    }
    public function storeDepartment(Request $request)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['title'=>'required|string|min:3|max:255','slug'=>'nullable|max:255|unique:categories,slug']);
        $data=$request->all(); $order=!empty($data['order'])?$data['order']:(\App\Models\Category::whereNull('parent_id')->count()+1);
        $cat=\App\Models\Category::create(['slug'=>$data['slug']??\App\Models\Category::makeSlug($data['title']),'icon'=>$data['icon']??null,'cover_image'=>$data['cover_image']??null,'icon2'=>$data['icon2']??null,'icon2_box_color'=>$data['icon2_box_color']??null,'overlay_image'=>$data['overlay_image']??null,'order'=>$order,'enable'=>!empty($data['enable'])&&$data['enable']=='on']);
        \App\Models\Translation\CategoryTranslation::updateOrCreate(['category_id'=>$cat->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'subtitle'=>$data['subtitle']??null,'bottom_seo_title'=>$data['bottom_seo_title']??null,'bottom_seo_content'=>$data['bottom_seo_content']??null]);
        $hasSub=!empty($data['has_sub'])&&$data['has_sub']=='on'; $this->setDepartmentSubCategories($cat,$data['sub_categories']??[],$hasSub,$data['locale']??app()->getLocale());
        cache()->forget(\App\Models\Category::$cacheKey ?? 'categories'); return redirect()->route('panel.v1.admin.education.departments.edit',['id'=>$cat->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء القسم','type'=>'success']);
    }

    public function editDepartment(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $category=\App\Models\Category::findOrFail($id);
        $subCategories=\App\Models\Category::where('parent_id',$category->id)->orderBy('order')->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.department-form','تعديل قسم',array_merge(AdminMockData::shell('education','departments'),[
            'category'=>$category,'subCategories'=>$subCategories,
            'formAction'=>route('panel.v1.admin.education.departments.update',['id'=>$category->id]),
        ]));
    }

    public function updateDepartment(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $category=\App\Models\Category::findOrFail($id);
        $request->validate(['title'=>'required|min:3|max:255','slug'=>'nullable|max:255|unique:categories,slug,'.$category->id]);
        $data=$request->all();
        $category->update([
            'slug'=> $data['slug'] ?? \App\Models\Category::makeSlug($data['title']),
            'order'=> $data['order'] ?? $category->order,
            'icon'=> $data['icon'] ?? $category->icon,
            'cover_image'=> $data['cover_image'] ?? $category->cover_image,
            'icon2'=> $data['icon2'] ?? $category->icon2,
            'icon2_box_color'=> $data['icon2_box_color'] ?? $category->icon2_box_color,
            'overlay_image'=> $data['overlay_image'] ?? $category->overlay_image,
            'enable'=> !empty($data['enable']) && $data['enable']=='on',
            'updated_at'=>time(),
        ]);
        \App\Models\Translation\CategoryTranslation::updateOrCreate([
            'category_id'=>$category->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale()),
        ],['title'=>$data['title'],'subtitle'=>$data['subtitle']??null,'bottom_seo_title'=>$data['bottom_seo_title']??null,'bottom_seo_content'=>$data['bottom_seo_content']??null]);
        $hasSub=!empty($data['has_sub'])&&$data['has_sub']=='on'; $this->setDepartmentSubCategories($category,$data['sub_categories']??[],$hasSub,$data['locale']??app()->getLocale());
        cache()->forget(\App\Models\Category::$cacheKey ?? 'categories');
        return redirect()->route('panel.v1.admin.education.section',['section'=>'departments'])->with('toast',['title'=>'تم','msg'=>'تم تحديث القسم','type'=>'success']);
    }

    public function deleteDepartment(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $cat=\App\Models\Category::where('id',$id)->first(); $parent=!empty($cat->parent_id)?$cat->parent_id:null;
        if(!empty($cat)){\App\Models\Category::where('parent_id',$cat->id)->delete(); $cat->delete();}
        cache()->forget(\App\Models\Category::$cacheKey ?? 'categories');
        return !empty($parent)?back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']):redirect()->route('panel.v1.admin.education.section',['section'=>'departments'])->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']);
    }
    private function setDepartmentSubCategories(\App\Models\Category $category,$subCategories,$hasSubCategories,$locale){
        $order=1; $oldIds=[];
        if($hasSubCategories && !empty($subCategories) && count($subCategories)){
            foreach($subCategories as $key=>$sub){
                if(empty($sub['title'])) continue;
                $check=is_numeric($key)?\App\Models\Category::where('id',$key)->first():null;
                if(is_numeric($key)) $oldIds[]=(int)$key;
                $checkSlug=0; if(!empty($sub['slug'])) $checkSlug=\App\Models\Category::where('slug',$sub['slug'])->count();
                $slug=(!empty($sub['slug'])&&($checkSlug==0||($checkSlug==1&&!empty($check)&&$check->slug==$sub['slug'])))?$sub['slug']:\App\Models\Category::makeSlug($sub['title']);
                if(!empty($check)){
                    $check->update(['slug'=>$slug,'order'=>$order,'icon'=>$sub['icon']??null,'cover_image'=>$sub['cover_image']??null,'icon2'=>$sub['icon2']??null,'icon2_box_color'=>$sub['icon2_box_color']??null,'overlay_image'=>$sub['overlay_image']??null,'enable'=>!empty($sub['enable'])&&$sub['enable']=='on']);
                    \App\Models\Translation\CategoryTranslation::updateOrCreate(['category_id'=>$check->id,'locale'=>mb_strtolower($locale)],['title'=>$sub['title'],'subtitle'=>$sub['subtitle']??null,'bottom_seo_title'=>$sub['bottom_seo_title']??null,'bottom_seo_content'=>$sub['bottom_seo_content']??null]);
                }else{
                    $new=\App\Models\Category::create(['parent_id'=>$category->id,'slug'=>$slug,'order'=>$order,'icon'=>$sub['icon']??null,'cover_image'=>$sub['cover_image']??null,'icon2'=>$sub['icon2']??null,'icon2_box_color'=>$sub['icon2_box_color']??null,'overlay_image'=>$sub['overlay_image']??null,'enable'=>!empty($sub['enable'])&&$sub['enable']=='on']);
                    \App\Models\Translation\CategoryTranslation::updateOrCreate(['category_id'=>$new->id,'locale'=>mb_strtolower($locale)],['title'=>$sub['title'],'subtitle'=>$sub['subtitle']??null,'bottom_seo_title'=>$sub['bottom_seo_title']??null,'bottom_seo_content'=>$sub['bottom_seo_content']??null]);
                    $oldIds[]=$new->id;
                }
                $order++;
            }
        }
        \App\Models\Category::where('parent_id',$category->id)->whereNotIn('id',$oldIds)->delete(); return true;
    }

    // ===== Filters — parity Admin\FilterController =====
    public function filtersList(Request $request){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $q=\App\Models\Filter::with('category')->orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','الفلاتر',array_merge(AdminMockData::shell('education','departments'),['filters'=>$p,'paginator'=>$p,'stubTitle'=>'الفلاتر'])); }
    public function createFilter(Request $request){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.filter-form','فلتر جديد',array_merge(AdminMockData::shell('education','departments'),['categories'=>$cats,'filter'=>null,'filterOptions'=>collect(),'formAction'=>route('panel.v1.admin.education.filters.store')])); }
    public function storeFilter(Request $request){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $request->validate(['title'=>'required|min:3|max:128','category_id'=>'required|exists:categories,id']); $data=$request->all(); $filter=\App\Models\Filter::create(['category_id'=>$data['category_id']]); \App\Models\Translation\FilterTranslation::updateOrCreate(['filter_id'=>$filter->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title']]); $this->setFilterOptions($filter,$data['sub_filters']??[],$data['locale']??app()->getLocale()); return redirect()->route('panel.v1.admin.education.filters.edit',['id'=>$filter->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الفلتر','type'=>'success']); }
    public function editFilter(Request $request,int $id){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $filter=\App\Models\Filter::findOrFail($id); $cats=\App\Models\Category::getCategories(); $opts=\App\Models\FilterOption::where('filter_id',$filter->id)->orderBy('order')->get(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.filter-form','تعديل فلتر',array_merge(AdminMockData::shell('education','departments'),['categories'=>$cats,'filter'=>$filter,'filterOptions'=>$opts,'formAction'=>route('panel.v1.admin.education.filters.update',['id'=>$filter->id])])); }
    public function updateFilter(Request $request,int $id){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $request->validate(['title'=>'required|min:3|max:128','category_id'=>'required|exists:categories,id']); $data=$request->all(); $filter=\App\Models\Filter::findOrFail($id); $filter->update(['category_id'=>$data['category_id']]); \App\Models\Translation\FilterTranslation::updateOrCreate(['filter_id'=>$filter->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title']]); $this->setFilterOptions($filter,$data['sub_filters']??[],$data['locale']??app()->getLocale()); return redirect()->route('panel.v1.admin.education.filters.edit',['id'=>$filter->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteFilter(Request $request,int $id){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; \App\Models\Filter::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    private function setFilterOptions(\App\Models\Filter $filter,$filterOptions,$locale){ $allIds=$filter->options->pluck('id')->toArray(); if(!empty($filterOptions)&&count($filterOptions)){ $order=1; foreach($filterOptions as $key=>$opt){ if(empty($opt['title'])) continue; $old=\App\Models\FilterOption::where('filter_id',$filter->id)->where('id',$key)->first(); if(!empty($old)){ $idx=array_search($key,$allIds); if($idx!==false) unset($allIds[$idx]); $old->update(['order'=>$order]); \App\Models\Translation\FilterOptionTranslation::updateOrCreate(['filter_option_id'=>$old->id,'locale'=>mb_strtolower($locale)],['title'=>$opt['title']]); }else{ $o=\App\Models\FilterOption::create(['filter_id'=>$filter->id,'order'=>$order]); \App\Models\Translation\FilterOptionTranslation::updateOrCreate(['filter_option_id'=>$o->id,'locale'=>mb_strtolower($locale)],['title'=>$opt['title']]); } $order++; } } if(!empty($allIds)) \App\Models\FilterOption::whereIn('id',$allIds)->delete(); }

    // ===== Trend Categories — parity Admin\TrendCategoriesController =====
    public function trendCategoriesList(Request $request){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $q=\App\Models\TrendCategory::with('category')->orderBy('created_at','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','التصنيفات الرائجة',array_merge(AdminMockData::shell('education','trends'),['trends'=>$p,'paginator'=>$p,'stubTitle'=>'التصنيفات الرائجة'])); }
    public function createTrendCategory(Request $request){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.trend-form','تصنيف رائج جديد',array_merge(AdminMockData::shell('education','trends-create'),['categories'=>$cats,'trend'=>null,'formAction'=>route('panel.v1.admin.education.trends.store')])); }
    public function storeTrendCategory(Request $request){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $request->validate(['category_id'=>'required','icon'=>'required','color'=>'required']); $data=$request->all(); \App\Models\TrendCategory::create(['category_id'=>$data['category_id'],'icon'=>$data['icon'],'color'=>$data['color'],'created_at'=>time()]); return redirect()->route('panel.v1.admin.education.trends.list')->with('toast',['title'=>'تم','msg'=>'تم الإنشاء','type'=>'success']); }
    public function editTrendCategory(Request $request,int $id){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $trend=\App\Models\TrendCategory::findOrFail($id); $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.trend-form','تعديل تصنيف رائج',array_merge(AdminMockData::shell('education','trends-create'),['categories'=>$cats,'trend'=>$trend,'formAction'=>route('panel.v1.admin.education.trends.update',['id'=>$trend->id])])); }
    public function updateTrendCategory(Request $request,int $id){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $request->validate(['category_id'=>'required','icon'=>'required','color'=>'required']); $data=$request->all(); $t=\App\Models\TrendCategory::findOrFail($id); $t->update(['category_id'=>$data['category_id'],'icon'=>$data['icon'],'color'=>$data['color']]); return redirect()->route('panel.v1.admin.education.trends.list')->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteTrendCategory(Request $request,int $id){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; \App\Models\TrendCategory::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== دورات — منطق حرفي مستند لـ Admin\WebinarController (قابل للاستخدام) =====
    public function createCourse(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $teacher = \App\User::query()
            ->where('role_name', \App\Models\Role::$teacher)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->first();

        if (empty($teacher)) {
            return redirect()
                ->route('panel.v1.admin.education.section', ['section' => 'courses'])
                ->with('toast', [
                    'title' => 'تعذر الإنشاء',
                    'msg' => 'لا يوجد مدرب نشط — أضف مدربًا أولاً ثم أنشئ الدورة',
                    'type' => 'error',
                ]);
        }

        $draft = new \App\Models\Webinar();
        $draft->teacher_id = $teacher->id;
        $draft->creator_id = $user->id;
        $draft->type = 'course';
        $draft->status = 'is_draft';
        $draft->slug = 'course-' . time() . '-' . rand(100, 999);
        $draft->created_at = time();
        $draft->updated_at = time();
        $draft->save();

        $translation = $draft->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = 'دورة تدريبية بدون عنوان';
        $translation->save();

        return redirect()->route('panel.v1.admin.education.courses.edit', [
            'id' => $draft->id,
            'step' => 1,
        ]);
    }

    public function storeCourse(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        // تحقق حرفي مستند لـ Admin\WebinarController::store مع جعل الحقول الثقيلة اختيارية ليتوافق مع التصميم المبسط
        $request->validate([
            'title'=>'required|string|max:255',
            'teacher_id'=>'required|exists:users,id',
            'category_id'=>'required|exists:categories,id',
            'type'=>'required|in:webinar,course,text_lesson',
            'price'=>'nullable|numeric|min:0',
            'status'=>'required|in:active,pending,is_draft',
            'thumbnail'=>'nullable|string|max:1000',
            'image_cover'=>'nullable|string|max:1000',
            'summary'=>'nullable|string|max:1000',
            'description'=>'nullable|string',
            'seo_description'=>'nullable|string|max:160',
            'duration'=>'nullable|numeric|min:1',
            'capacity'=>'nullable|numeric|min:1',
            'access_days'=>'nullable|numeric|min:1',
            'support'=>'nullable|in:on',
            'certificate'=>'nullable|in:on',
            'downloadable'=>'nullable|in:on',
            'private'=>'nullable|in:on',
        ]);

        $data = $request->all();
        $teacher = \App\User::findOrFail($data['teacher_id']);
        $slug = !empty($data['slug']) ? \Illuminate\Support\Str::slug($data['slug']) : \Illuminate\Support\Str::slug($data['title']).'-'.time();

        // معالجة السعر بنفس تحويل العملة الأصلي
        $price = !empty($data['price']) ? (function_exists('convertPriceToDefaultCurrency') ? convertPriceToDefaultCurrency($data['price']) : $data['price']) : null;

        $webinar = \App\Models\Webinar::create([
            'type'=>$data['type'],
            'slug'=>$slug,
            'teacher_id'=>$teacher->id,
            'creator_id'=>$teacher->id,
            'category_id'=>$data['category_id'],
            'price'=>$price,
            'status'=>$data['status'] ?? 'pending',
            'thumbnail'=>$data['thumbnail'] ?? '/assets/default/img/course_default.jpg',
            'image_cover'=>$data['image_cover'] ?? '/assets/default/img/course_default.jpg',
            'duration'=> $data['duration'] ?? 60,
            'capacity'=> $data['capacity'] ?? null,
            'access_days'=> $data['access_days'] ?? null,
            'support'=>!empty($data['support']) && $data['support']=='on',
            'certificate'=>!empty($data['certificate']) && $data['certificate']=='on' ? true : true,
            'downloadable'=>!empty($data['downloadable']) && $data['downloadable']=='on',
            'private'=>!empty($data['private']) && $data['private']=='on',
            'subscribe'=>!empty($data['subscribe']) && $data['subscribe']=='on',
            'forum'=>!empty($data['forum']) && $data['forum']=='on',
            'enable_waitlist'=>!empty($data['enable_waitlist']) && $data['enable_waitlist']=='on',
            'only_for_students'=>!empty($data['only_for_students']) && $data['only_for_students']=='on',
            'partner_instructor'=>!empty($data['partner_instructor']) && $data['partner_instructor']=='on',
            'points'=> $data['points'] ?? null,
            'message_for_reviewer'=> $data['message_for_reviewer'] ?? null,
            // ملاحظة: seo_description حقل مترجم — لا يُمرر هنا (يُحفظ في webinar_translations فقط) وإلا أنشأ Astrotomic صف ترجمة فارغاً
            'created_at'=>time(),
            'updated_at'=>time(),
        ]);

        if($webinar){
            \App\Models\Translation\WebinarTranslation::updateOrCreate([
                'webinar_id'=>$webinar->id,
                'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale()),
            ],[
                'title'=>$data['title'],
                'summary'=>$data['summary'] ?? $data['title'],
                'description'=>$data['description'] ?? $data['title'],
                'seo_description'=>$data['seo_description'] ?? $data['title'],
            ]);
            // وسوم
            if(!empty($data['tags'])){
                $tags = array_filter(array_map('trim', explode(',', (string)$data['tags'])));
                \App\Models\Tag::where('webinar_id',$webinar->id)->delete();
                foreach(array_slice(array_unique($tags),0,10) as $tagTitle){
                    \App\Models\Tag::create(['title'=>mb_substr($tagTitle,0,64),'webinar_id'=>$webinar->id]);
                }
            }
            // شركاء
            if(!empty($data['partner_instructor']) && !empty($data['partners']) && is_array($data['partners'])){
                foreach($data['partners'] as $pid){
                    \App\Models\WebinarPartnerTeacher::create(['webinar_id'=>$webinar->id,'teacher_id'=>$pid]);
                }
            }
        }

        return redirect()->route('panel.v1.admin.education.section',['section'=>'courses'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الدورة بنجاح (منطق Admin\WebinarController حرفياً)','type'=>'success']);
    }

    public function editCourse(Request $request, int $id, ?int $step = 1)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($request->filled('step')) {
            $step = (int) $request->input('step');
        }
        $step = max(1, min(5, $step ?? 1));

        $webinar = \App\Models\Webinar::with(['tags', 'translations'])->findOrFail($id);
        $data = $this->buildCourseWizardViewData($request, $webinar, $step, $user);

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.course-wizard',
            'تعديل دورة',
            array_merge(AdminMockData::shell('education', 'courses'), $data, [
                'wizardCoursesUrl' => route('panel.v1.admin.education.section', ['section' => 'courses']),
                'wizardStoreUrl' => route('panel.v1.admin.education.courses.wizard.store', ['id' => $webinar->id]),
                'wizardCreateUrl' => route('panel.v1.admin.education.courses.edit', ['id' => $webinar->id]),
            ])
        );
    }

    public function storeCourseWizard(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $draft = $this->wizardWebinarOrFail($user, $id);
        // Keep draft_id in sync for curriculum / SPA
        $request->merge(['draft_id' => $draft->id]);

        $result = $this->persistCourseWizardStep($request, $user, $draft, ['allowCreate' => false]);
        $draft = $result['draft'];
        $isDone = $result['isDone'];
        $nextStep = $result['nextStep'];
        $draftTitle = $result['draftTitle'];
        $doneMessage = $result['doneMessage'];
        $step = $result['step'];

        $progressMap = [1 => 20, 2 => 40, 3 => 60, 4 => 80, 5 => 100];
        $coursesUrl = route('panel.v1.admin.education.section', ['section' => 'courses']);

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'ok' => true,
                'draft_id' => $draft->id ?? null,
                'draft_title' => $draftTitle ?: 'دورة تدريبية بدون عنوان',
                'step' => $step,
                'next_step' => $isDone ? null : $nextStep,
                'progress' => $progressMap[$isDone ? 5 : $nextStep] ?? 20,
                'message' => $isDone ? $doneMessage : 'تم حفظ التعديلات',
                'done' => $isDone,
                'redirect' => $isDone ? $coursesUrl : null,
            ]);
        }

        if ($isDone) {
            return redirect()
                ->to($coursesUrl)
                ->with('toast', [
                    'title' => 'تم',
                    'msg' => $doneMessage,
                    'type' => 'success',
                ]);
        }

        return redirect()
            ->route('panel.v1.admin.education.courses.edit', ['id' => $draft->id, 'step' => $nextStep])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم حفظ التعديلات بنجاح',
                'type' => 'success',
            ]);
    }

    public function updateCourse(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $webinar = \App\Models\Webinar::findOrFail($id);
        $request->validate([
            'title'=>'required|string|max:255',
            'teacher_id'=>'required|exists:users,id',
            'category_id'=>'required|exists:categories,id',
            'price'=>'nullable|numeric|min:0',
            'status'=>'required|in:active,pending,is_draft,inactive',
            'duration'=>'nullable|numeric|min:1',
            'capacity'=>'nullable|numeric|min:1',
            'summary'=>'nullable|string|max:1000',
            'description'=>'nullable|string',
        ]);
        $data=$request->all();
        $webinar->update([
            'teacher_id'=>$data['teacher_id'],
            'category_id'=>$data['category_id'],
            'price'=>!empty($data['price']) ? (function_exists('convertPriceToDefaultCurrency') ? convertPriceToDefaultCurrency($data['price']) : $data['price']) : null,
            'status'=>$data['status'],
            'duration'=>$data['duration'] ?? $webinar->duration,
            'capacity'=>$data['capacity'] ?? null,
            'support'=>!empty($data['support']) && $data['support']=='on',
            'certificate'=>!empty($data['certificate']) && $data['certificate']=='on',
            'downloadable'=>!empty($data['downloadable']) && $data['downloadable']=='on',
            'private'=>!empty($data['private']) && $data['private']=='on',
            'updated_at'=>time(),
        ]);
        \App\Models\Translation\WebinarTranslation::updateOrCreate([
            'webinar_id'=>$webinar->id,
            'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale()),
        ],[
            'title'=>$data['title'],
            'summary'=>$data['summary'] ?? $data['title'],
            'description'=>$data['description'] ?? $data['title'],
        ]);

        return redirect()->route('panel.v1.admin.education.section',['section'=>'courses'])->with('toast',['title'=>'تم','msg'=>'تم التحديث (منطق Admin\WebinarController حرفياً)','type'=>'success']);
    }

    public function notifyCourseForm(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $webinar=\App\Models\Webinar::findOrFail($id);
        $studentsCount=\App\Models\Sale::where('webinar_id',$webinar->id)->whereNull('refund_at')->count();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.course-notify','إشعار طلاب الدورة',array_merge(AdminMockData::shell('education','courses'),[
            'webinar'=>$webinar,'studentsCount'=>$studentsCount,
            'formAction'=>route('panel.v1.admin.education.courses.notify.send',['id'=>$webinar->id]),
        ]));
    }
    public function sendCourseNotification(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['title'=>'required|string|max:255','message'=>'required|string']);
        $webinar=\App\Models\Webinar::with(['sales'=>fn($q)=>$q->whereNull('refund_at')->with('buyer')])->findOrFail($id);
        $count=0;
        foreach($webinar->sales as $sale){
            if(!empty($sale->buyer)){
                \App\Models\Notification::create([
                    'user_id'=>$sale->buyer->id,'group_id'=>null,'sender_id'=>$user->id,
                    'title'=>$request->input('title'),'message'=>$request->input('message'),
                    'sender'=>\App\Models\Notification::$AdminSender,'type'=>'single','created_at'=>time(),
                ]);
                $count++;
            }
        }
        return redirect()->route('panel.v1.admin.education.section',['section'=>'courses'])->with('toast',['title'=>'تم','msg'=>"تم إرسال الإشعار إلى $count طالب",'type'=>'success']);
    }
    public function courseCurriculum(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $webinar=\App\Models\Webinar::findOrFail($id);
        $chapters=\App\Models\WebinarChapter::with(['sessions','files','textLessons'])->where('webinar_id',$webinar->id)->orderBy('order')->orderBy('id')->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.curriculum','منهج: '.$webinar->title,array_merge(AdminMockData::shell('education','courses'),[
            'webinar'=>$webinar,'chapters'=>$chapters,'stubTitle'=>'منهج: '.$webinar->title,
        ]));
    }
    public function adminChapterStore(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $webinar=\App\Models\Webinar::findOrFail($id);
        $request->validate(['title'=>'required|string|max:255']);
        $chapter=new \App\Models\WebinarChapter();
        $chapter->user_id=$user->id;
        $chapter->webinar_id=$webinar->id;
        $chapter->status='active';
        $chapter->created_at=time();
        $chapter->save();
        $translation=$chapter->translateOrNew('ar');
        $translation->locale='ar';
        $translation->title=$request->input('title');
        $translation->save();
        return back()->with('toast',['title'=>'تم','msg'=>'تمت إضافة الوحدة','type'=>'success']);
    }
    public function adminChapterDelete(Request $request, int $id, int $chapterId)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\WebinarChapter::where('id',$chapterId)->where('webinar_id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الوحدة','type'=>'success']);
    }
    public function adminSessionStore(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $webinar=\App\Models\Webinar::findOrFail($id);
        $request->validate(['chapter_id'=>'required|integer','topic'=>'required|string|max:255','date'=>'required|date','duration'=>'required|integer|min:1']);
        $chapter=\App\Models\WebinarChapter::where('id',$request->input('chapter_id'))->where('webinar_id',$webinar->id)->firstOrFail();
        $session=new \App\Models\Session();
        $session->creator_id=$user->id;
        $session->webinar_id=$webinar->id;
        $session->chapter_id=$chapter->id;
        $session->date=strtotime($request->input('date'));
        $session->duration=(int)$request->input('duration');
        $session->status='active';
        $session->created_at=time();
        $session->updated_at=time();
        $session->save();
        $translation=$session->translateOrNew('ar');
        $translation->locale='ar';
        $translation->title=$request->input('topic');
        $translation->save();
        return back()->with('toast',['title'=>'تم','msg'=>'تمت إضافة الجلسة','type'=>'success']);
    }
    public function adminSessionDelete(Request $request, int $id, int $sessionId)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Session::where('id',$sessionId)->where('webinar_id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الجلسة','type'=>'success']);
    }
    public function adminFileStore(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $webinar=\App\Models\Webinar::findOrFail($id);
        $request->validate(['chapter_id'=>'required|integer','title'=>'required|string|max:255','upload'=>'required|file|max:5242880']);
        $chapter=\App\Models\WebinarChapter::where('id',$request->input('chapter_id'))->where('webinar_id',$webinar->id)->firstOrFail();
        $path=$request->file('upload')->store('webinars/files','public');
        $file=new \App\Models\File();
        $file->creator_id=$user->id;
        $file->webinar_id=$webinar->id;
        $file->chapter_id=$chapter->id;
        $file->accessibility='paid';
        $file->downloadable=1;
        $file->storage='upload';
        $file->file='/store/'.ltrim($path,'/');
        $file->volume=(string)$request->file('upload')->getSize();
        $file->file_type=explode('/',$request->file('upload')->getMimeType())[0] ?? 'file';
        $file->status='active';
        $file->created_at=time();
        $file->updated_at=time();
        $file->save();
        $translation=$file->translateOrNew('ar');
        $translation->locale='ar';
        $translation->title=$request->input('title');
        $translation->save();
        return back()->with('toast',['title'=>'تم','msg'=>'تم رفع الملف','type'=>'success']);
    }
    public function adminFileDelete(Request $request, int $id, int $fileId)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\File::where('id',$fileId)->where('webinar_id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الملف','type'=>'success']);
    }
    public function deleteCourse(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Webinar::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الدورة','type'=>'success']);
    }

    public function approveCourse(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Webinar::where('id',$id)->update(['status'=>'active','updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تمت الموافقة','type'=>'success']);
    }

    public function rejectCourse(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Webinar::where('id',$id)->update(['status'=>'inactive','updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم الرفض','type'=>'success']);
    }

    public function exportCourses(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $query = \App\Models\Webinar::with(['teacher','category','sales']);
        // نفس فلترة Admin\WebinarController::filterWebinar مبسطة
        if($s=$request->input('search')) $query->where(function($q)use($s){ $q->where('id',$s)->orWhereHas('translations',fn($t)=>$t->where('title','like',"%{$s}%")); });
        if($c=$request->input('category_id')) $query->where('category_id',$c);
        if($st=$request->input('status')) $query->where('status',$st);
        $webinars = $query->orderBy('created_at','desc')->limit(500)->get();
        return Excel::download(new WebinarsExport($webinars), 'courses-'.date('Y-m-d').'.xlsx');
    }

    // ===== حِزم — منطق Admin\BundleController =====
    public function createBundle(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $categories=\App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
        $teachers=\App\User::where('role_name','teacher')->select('id','full_name')->limit(50)->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.bundle-form','إنشاء حزمة',array_merge(AdminMockData::shell('education','bundles'),['categories'=>$categories,'teachers'=>$teachers,'bundle'=>null]));
    }
    public function storeBundle(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['title'=>'required|string|max:255','teacher_id'=>'required|exists:users,id','category_id'=>'required|exists:categories,id','price'=>'nullable|numeric|min:0']);
        $data=$request->all();
        $bundle=\App\Models\Bundle::create([
            'slug'=>\Illuminate\Support\Str::slug($data['title']).'-'.time(),
            'teacher_id'=>$data['teacher_id'],'creator_id'=>$data['teacher_id'],'category_id'=>$data['category_id'],
            'price'=>!empty($data['price'])?convertPriceToDefaultCurrency($data['price']):null,
            'status'=>'pending','created_at'=>time(),'updated_at'=>time(),
        ]);
        \App\Models\Translation\BundleTranslation::updateOrCreate(['bundle_id'=>$bundle->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title']]);
        return redirect()->route('panel.v1.admin.education.section',['section'=>'bundles'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الحزمة','type'=>'success']);
    }
    public function deleteBundle(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Bundle::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']);
    }
    public function editBundle(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $bundle=\App\Models\Bundle::findOrFail($id);
        $categories=\App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
        $teachers=\App\User::where('role_name','teacher')->select('id','full_name')->limit(50)->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.bundle-form','تعديل حزمة',array_merge(AdminMockData::shell('education','bundles'),[
            'categories'=>$categories,'teachers'=>$teachers,'bundle'=>$bundle,
            'formAction'=>route('panel.v1.admin.education.bundles.update',['id'=>$bundle->id]),
        ]));
    }
    public function updateBundle(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $bundle=\App\Models\Bundle::findOrFail($id);
        $request->validate(['title'=>'required|string|max:255','teacher_id'=>'required|exists:users,id','category_id'=>'required|exists:categories,id','price'=>'nullable|numeric|min:0','status'=>'required|in:active,pending,is_draft,inactive']);
        $data=$request->all();
        $bundle->update([
            'teacher_id'=>$data['teacher_id'],'category_id'=>$data['category_id'],
            'price'=>!empty($data['price'])?convertPriceToDefaultCurrency($data['price']):null,
            'status'=>$data['status'],'updated_at'=>time(),
        ]);
        \App\Models\Translation\BundleTranslation::updateOrCreate(['bundle_id'=>$bundle->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title']]);
        return redirect()->route('panel.v1.admin.education.section',['section'=>'bundles'])->with('toast',['title'=>'تم','msg'=>'تم تحديث الحزمة','type'=>'success']);
    }

    // ===== اختبارات — Admin\QuizController (إنشاء/حذف) =====
    public function createQuiz(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinars = \App\Models\Webinar::orderBy('id', 'desc')->limit(200)->get()
            ->map(fn ($w) => ['id' => $w->id, 'title' => $w->title])
            ->all();

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.quiz-form',
            'إضافة اختبار جديد',
            array_merge(AdminMockData::shell('education', 'quizzes'), [
                'quiz' => null,
                'webinars' => $webinars,
                'formAction' => route('panel.v1.admin.education.quizzes.store'),
            ])
        );
    }

    public function storeQuiz(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'webinar_id' => 'required|exists:webinars,id',
            'title' => 'required|string|max:255',
            'pass_mark' => 'required|integer|min:0',
            'time' => 'nullable|integer|min:0',
            'attempt' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $webinar = \App\Models\Webinar::findOrFail($request->input('webinar_id'));
        $quiz = new \App\Models\Quiz();
        $quiz->webinar_id = $webinar->id;
        $quiz->creator_id = $webinar->teacher_id;
        $quiz->pass_mark = $request->input('pass_mark');
        $quiz->time = $request->input('time', 0);
        $quiz->attempt = $request->input('attempt');
        $quiz->certificate = 0;
        $quiz->status = $request->input('status', 'active');
        $quiz->created_at = time();
        $quiz->updated_at = time();
        $quiz->save();

        $translation = $quiz->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->save();

        return redirect()
            ->route('panel.v1.admin.education.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم إنشاء الاختبار، أضف الأسئلة الآن', 'type' => 'success']);
    }

    public function editQuiz(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $quiz = \App\Models\Quiz::with(['webinar'])->findOrFail($id);

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.quiz-form',
            'تعديل إعدادات الاختبار',
            array_merge(AdminMockData::shell('education', 'quizzes'), [
                'quiz' => $quiz,
                'webinars' => [],
                'formAction' => route('panel.v1.admin.education.quizzes.update', ['id' => $quiz->id]),
            ])
        );
    }

    public function updateQuiz(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $quiz = \App\Models\Quiz::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'pass_mark' => 'required|integer|min:0',
            'time' => 'nullable|integer|min:0',
            'attempt' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $quiz->pass_mark = $request->input('pass_mark');
        $quiz->time = $request->input('time', 0);
        $quiz->attempt = $request->input('attempt');
        $quiz->status = $request->input('status', 'active');
        $quiz->updated_at = time();
        $quiz->save();

        $translation = $quiz->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->save();

        return redirect()
            ->route('panel.v1.admin.education.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ إعدادات الاختبار', 'type' => 'success']);
    }

    public function deleteQuiz(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        \App\Models\Quiz::where('id', $id)->delete();

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف الاختبار', 'type' => 'success']);
    }

    public function quizView(Request $request, int $id)
    {
        return $this->renderAdminQuizView($request, $id, null);
    }

    /** Legacy alias — same manage cycle as instructor quiz view. */
    public function quizQuestions(Request $request, int $id)
    {
        return redirect()->route('panel.v1.admin.education.quizzes.view', ['id' => $id]);
    }

    public function editQuizQuestion(Request $request, int $id, int $questionId)
    {
        return $this->renderAdminQuizView($request, $id, $questionId);
    }

    private function renderAdminQuizView(Request $request, int $id, ?int $editQuestionId = null)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $quiz = \App\Models\Quiz::with(['webinar.category'])->findOrFail($id);
        $webinar = $quiz->webinar;

        $questions = \App\Models\QuizzesQuestion::with(['quizzesQuestionsAnswers'])
            ->where('quiz_id', $quiz->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $realQuestions = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'title' => $question->title,
                'type' => $question->type,
                'grade' => $question->grade,
                'model_answer' => $question->type === \App\Models\QuizzesQuestion::$descriptive
                    ? ($question->correct ?: '—')
                    : null,
                'options' => $question->quizzesQuestionsAnswers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'text' => $answer->title,
                        'correct' => (bool) $answer->correct,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $waitingResults = \App\Models\QuizzesResult::with(['user'])
            ->where('quiz_id', $quiz->id)
            ->where('status', \App\Models\QuizzesResult::$waiting)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $editPayload = null;
        if ($editQuestionId) {
            $editQuestion = \App\Models\QuizzesQuestion::with(['quizzesQuestionsAnswers'])
                ->where('quiz_id', $quiz->id)
                ->where('id', $editQuestionId)
                ->firstOrFail();

            $correctOption = 0;
            foreach ($editQuestion->quizzesQuestionsAnswers->values() as $i => $a) {
                if (!empty($a->correct)) {
                    $correctOption = (int) $i;
                    break;
                }
            }

            $editPayload = [
                'id' => $editQuestion->id,
                'title' => $editQuestion->title,
                'type' => $editQuestion->type,
                'grade' => $editQuestion->grade,
                'model_answer' => $editQuestion->type === \App\Models\QuizzesQuestion::$descriptive
                    ? ($editQuestion->correct ?: '')
                    : '',
                'options' => $editQuestion->quizzesQuestionsAnswers->map(fn ($a) => [
                    'text' => $a->title,
                    'correct' => (bool) $a->correct,
                ])->values()->all(),
                'correct_option' => $correctOption,
            ];
        }

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.quiz-view',
            $editPayload ? 'تعديل سؤال' : 'إدارة الاختبار',
            array_merge(AdminMockData::shell('education', 'quizzes'), [
                'quiz' => $quiz,
                'quizId' => $quiz->id,
                'quizTitle' => $quiz->title,
                'quizMeta' => [
                    'pass_mark' => $quiz->pass_mark,
                    'time' => $quiz->time,
                    'attempt' => $quiz->attempt,
                    'status' => $quiz->status,
                    'total_mark' => $quiz->total_mark,
                ],
                'quizView' => [
                    'title' => $quiz->title,
                    'subtitle' => ($webinar->title ?? '') . (!empty($webinar->category->title) ? (' • ' . $webinar->category->title) : ''),
                ],
                'realQuestions' => $realQuestions,
                'waitingResults' => $waitingResults,
                'editQuestion' => $editPayload,
            ])
        );
    }

    public function updateQuizQuestion(Request $request, int $id, int $questionId)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $quiz = \App\Models\Quiz::findOrFail($id);
        $question = \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->where('id', $questionId)->firstOrFail();
        $type = $request->input('type', 'multiple');

        $rules = [
            'title' => 'required|string|max:1000',
            'type' => 'required|in:multiple,descriptive',
            'grade' => 'required|integer|min:1|max:1000',
        ];
        if ($type === 'multiple') {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'nullable|string|max:1000';
            $rules['correct_option'] = 'required|integer|min:0|max:10';
        } else {
            $rules['correct'] = 'nullable|string|max:5000';
        }

        $data = $request->validate($rules, [
            'title.required' => 'اكتب نص السؤال',
            'grade.required' => 'حدد درجة السؤال',
            'options.required' => 'أضف خيارين على الأقل',
            'correct_option.required' => 'اختر الإجابة الصحيحة بالنقر على ○ بجانب الخيار',
        ]);

        $options = [];
        if ($type === 'multiple') {
            $options = array_values(array_filter(array_map('trim', (array) $request->input('options', [])), fn ($v) => $v !== ''));
            if (count($options) < 2) {
                return back()->withInput()->withErrors(['options' => 'أضف خيارين مكتوبين على الأقل']);
            }
            $correctOption = (int) $request->input('correct_option', 0);
            if ($correctOption < 0 || $correctOption >= count($options)) {
                return back()->withInput()->withErrors(['correct_option' => 'اختر الإجابة الصحيحة من الخيارات المكتوبة']);
            }
        }

        $question->grade = (int) $data['grade'];
        $question->type = $type;
        $question->updated_at = time();
        $question->save();

        $translation = $question->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $data['title'];
        $translation->correct = $type === 'descriptive' ? $request->input('correct') : null;
        $translation->save();

        \App\Models\QuizzesQuestionsAnswer::where('question_id', $question->id)->delete();
        if ($type === 'multiple') {
            $correctOption = (int) $request->input('correct_option', 0);
            foreach ($options as $index => $optionTitle) {
                $answer = new \App\Models\QuizzesQuestionsAnswer();
                $answer->question_id = $question->id;
                $answer->creator_id = $question->creator_id ?: $user->id;
                $answer->correct = $index === $correctOption ? 1 : 0;
                $answer->created_at = time();
                $answer->updated_at = time();
                $answer->save();
                $answerTranslation = $answer->translateOrNew('ar');
                $answerTranslation->locale = 'ar';
                $answerTranslation->title = mb_substr($optionTitle, 0, 1000);
                $answerTranslation->save();
            }
        }

        $quiz->total_mark = (int) \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->sum('grade');
        $quiz->updated_at = time();
        $quiz->save();

        return redirect()
            ->route('panel.v1.admin.education.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم تحديث السؤال', 'type' => 'success']);
    }

    public function storeQuizQuestion(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $quiz = \App\Models\Quiz::findOrFail($id);
        $type = $request->input('type', 'multiple');

        $rules = [
            'title' => 'required|string|max:1000',
            'type' => 'required|in:multiple,descriptive',
            'grade' => 'required|integer|min:1|max:1000',
        ];
        if ($type === 'multiple') {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'nullable|string|max:1000';
            $rules['correct_option'] = 'required|integer|min:0|max:10';
        } else {
            $rules['correct'] = 'nullable|string|max:5000';
        }

        $data = $request->validate($rules, [
            'title.required' => 'اكتب نص السؤال',
            'grade.required' => 'حدد درجة السؤال',
            'options.required' => 'أضف خيارين على الأقل',
            'correct_option.required' => 'اختر الإجابة الصحيحة بالنقر على ○ بجانب الخيار',
        ]);

        $options = [];
        if ($type === 'multiple') {
            $options = array_values(array_filter(array_map('trim', (array) $request->input('options', [])), fn ($v) => $v !== ''));
            if (count($options) < 2) {
                return back()->withInput()->withErrors(['options' => 'أضف خيارين مكتوبين على الأقل']);
            }
            $correctOption = (int) $request->input('correct_option', 0);
            if ($correctOption < 0 || $correctOption >= count($options)) {
                return back()->withInput()->withErrors(['correct_option' => 'اختر الإجابة الصحيحة من الخيارات المكتوبة']);
            }
        }

        $maxOrder = (int) \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->max('order');
        $question = new \App\Models\QuizzesQuestion();
        $question->quiz_id = $quiz->id;
        $question->creator_id = $quiz->creator_id ?? $user->id;
        $question->grade = (int) $data['grade'];
        $question->type = $type;
        $question->order = $maxOrder + 1;
        $question->created_at = time();
        $question->updated_at = time();
        $question->save();

        $translation = $question->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $data['title'];
        if ($type === 'descriptive') {
            $translation->correct = $request->input('correct');
        }
        $translation->save();

        if ($type === 'multiple') {
            $correctOption = (int) $request->input('correct_option', 0);
            foreach ($options as $index => $optionTitle) {
                $answer = new \App\Models\QuizzesQuestionsAnswer();
                $answer->question_id = $question->id;
                $answer->creator_id = $question->creator_id;
                $answer->correct = $index === $correctOption ? 1 : 0;
                $answer->created_at = time();
                $answer->updated_at = time();
                $answer->save();
                $answerTranslation = $answer->translateOrNew('ar');
                $answerTranslation->locale = 'ar';
                $answerTranslation->title = mb_substr($optionTitle, 0, 1000);
                $answerTranslation->save();
            }
        }

        $quiz->total_mark = (int) \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->sum('grade');
        $quiz->updated_at = time();
        $quiz->save();

        return redirect()
            ->route('panel.v1.admin.education.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تمت إضافة السؤال بنجاح', 'type' => 'success']);
    }

    public function deleteQuizQuestion(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $question = \App\Models\QuizzesQuestion::findOrFail($id);
        $quizId = $question->quiz_id;
        $question->delete();

        $quiz = \App\Models\Quiz::find($quizId);
        if ($quiz) {
            $quiz->total_mark = (int) \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->sum('grade');
            $quiz->updated_at = time();
            $quiz->save();
        }

        return redirect()
            ->route('panel.v1.admin.education.quizzes.view', ['id' => $quizId])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حذف السؤال', 'type' => 'success']);
    }

    // ===== تكليفات — Admin\AssignmentController (إنشاء/حذف) =====
    public function createAssignment(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $webinars=\App\Models\Webinar::orderBy('id','desc')->limit(100)->get()->map(fn($w)=>['id'=>$w->id,'title'=>$w->title])->all();
        $chapters=\App\Models\WebinarChapter::with(['webinar'])->orderBy('id','desc')->limit(200)->get()->map(fn($ch)=>['id'=>$ch->id,'title'=>($ch->title ?: 'وحدة #'.$ch->id).' — '.($ch->webinar->title ?? '')])->all();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.assignment-form','إنشاء تكليف',array_merge(AdminMockData::shell('education','assignments'),[
            'webinars'=>$webinars,'chapters'=>$chapters,'assignment'=>null,
            'formAction'=>route('panel.v1.admin.education.assignments.store'),
            'formTitle'=>'إنشاء تكليف جديد','submitLabel'=>'إنشاء التكليف',
        ]));
    }
    public function storeAssignment(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['webinar_id'=>'required|exists:webinars,id','chapter_id'=>'required|exists:webinar_chapters,id','title'=>'required|string|max:255','grade'=>'required|integer|min:0','pass_grade'=>'required|integer|min:0','attempts'=>'nullable|integer|min:1','deadline'=>'nullable|integer|min:1','status'=>'required|in:active,inactive']);
        $webinar=\App\Models\Webinar::findOrFail($request->input('webinar_id'));
        $a=new \App\Models\WebinarAssignment();
        $a->webinar_id=$webinar->id;
        $a->chapter_id=$request->input('chapter_id');
        $a->creator_id=$webinar->teacher_id;
        $a->grade=$request->input('grade');
        $a->pass_grade=$request->input('pass_grade');
        $a->attempts=$request->input('attempts');
        $a->deadline=$request->input('deadline');
        $a->status=$request->input('status','active');
        $a->created_at=time();
        $a->save();
        $translation=$a->translateOrNew('ar');
        $translation->locale='ar';
        $translation->title=$request->input('title');
        $translation->description=$request->input('description');
        $translation->save();
        return redirect()->route('panel.v1.admin.education.section',['section'=>'assignments'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء التكليف','type'=>'success']);
    }

    public function editAssignment(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $assignment = \App\Models\WebinarAssignment::with(['webinar'])->findOrFail($id);
        $webinars = \App\Models\Webinar::orderBy('id', 'desc')->limit(100)->get()
            ->map(fn ($w) => ['id' => $w->id, 'title' => $w->title])->all();
        $chapters = \App\Models\WebinarChapter::with(['webinar'])->orderBy('id', 'desc')->limit(200)->get()
            ->map(fn ($ch) => [
                'id' => $ch->id,
                'title' => ($ch->title ?: 'وحدة #'.$ch->id).' — '.($ch->webinar->title ?? ''),
            ])->all();

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.assignment-form',
            'تعديل تكليف',
            array_merge(AdminMockData::shell('education', 'assignments'), [
                'webinars' => $webinars,
                'chapters' => $chapters,
                'assignment' => $assignment,
                'formAction' => route('panel.v1.admin.education.assignments.update', ['id' => $assignment->id]),
                'formTitle' => 'تعديل التكليف',
                'submitLabel' => 'حفظ التعديلات',
            ])
        );
    }

    public function updateAssignment(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $assignment = \App\Models\WebinarAssignment::findOrFail($id);
        $request->validate([
            'webinar_id' => 'required|exists:webinars,id',
            'chapter_id' => 'required|exists:webinar_chapters,id',
            'title' => 'required|string|max:255',
            'grade' => 'required|integer|min:0',
            'pass_grade' => 'required|integer|min:0',
            'attempts' => 'nullable|integer|min:1',
            'deadline' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $webinar = \App\Models\Webinar::findOrFail($request->input('webinar_id'));
        $assignment->webinar_id = $webinar->id;
        $assignment->chapter_id = $request->input('chapter_id');
        $assignment->grade = $request->input('grade');
        $assignment->pass_grade = $request->input('pass_grade');
        $assignment->attempts = $request->input('attempts');
        $assignment->deadline = $request->input('deadline');
        $assignment->status = $request->input('status', 'active');
        $assignment->updated_at = time();
        $assignment->save();

        $translation = $assignment->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->description = $request->input('description');
        $translation->save();

        return redirect()
            ->route('panel.v1.admin.education.section', ['section' => 'assignments'])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم تحديث التكليف', 'type' => 'success']);
    }

    public function deleteAssignment(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\WebinarAssignment::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']);
    }

    // ===== تقييمات الدورات — WebinarReview =====
    public function approveReview(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $review = \App\Models\WebinarReview::where('id', $id)->firstOrFail();
        $review->status = 'active';
        $review->save();

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم اعتماد التقييم', 'type' => 'success']);
    }

    public function rejectReview(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        \App\Models\WebinarReview::where('id', $id)->update(['status' => 'pending']);

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم إرجاع التقييم للانتظار', 'type' => 'success']);
    }

    public function deleteReview(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        \App\Models\WebinarReview::where('id', $id)->delete();

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف التقييم', 'type' => 'success']);
    }

    // ===== شهادات — حذف + قوالب + تنزيل QIEC =====
    public function deleteCertificate(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Certificate::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الشهادة','type'=>'success']);
    }

    public function createCertificate(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $students = \App\User::query()
            ->where('role_name', \App\Models\Role::$user)
            ->orderBy('full_name')
            ->limit(800)
            ->get(['id', 'full_name', 'email'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->full_name, 'email' => $u->email])
            ->all();

        $courses = \App\Models\Webinar::query()
            ->with('translations')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(function ($c) {
                $ar = '';
                $en = '';
                try { $ar = (string) (($c->translate('ar')->title ?? null) ?: ($c->title ?? '')); } catch (\Throwable $e) { $ar = (string) ($c->title ?? ''); }
                try { $en = (string) (($c->translate('en')->title ?? null) ?: $ar); } catch (\Throwable $e) { $en = $ar; }
                $hours = !empty($c->duration) ? max(1, (int) ceil(((int) $c->duration) / 60)) : 8;
                return [
                    'id' => $c->id,
                    'title_ar' => $ar,
                    'title_en' => $en,
                    'hours' => $hours,
                ];
            })->all();

        $defaults = $this->qiecCertificateDefaults();

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.certificate-create',
            'إنشاء شهادة جديدة',
            array_merge(AdminMockData::shell('education', 'certificates'), [
                'students' => $students,
                'courses' => $courses,
                'defaults' => $defaults,
            ])
        );
    }

    public function storeCertificate(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'webinar_id' => 'required|exists:webinars,id',
            'trainee_name_ar' => 'required|string|max:255',
            'trainee_name_en' => 'nullable|string|max:255',
            'course_title_ar' => 'required|string|max:255',
            'course_title_en' => 'nullable|string|max:255',
            'hours' => 'required|integer|min:1|max:999',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'issue_date' => 'required|date',
            'accreditation_number' => 'required|string|max:120',
            'officer_name' => 'required|string|max:255',
            'director_name' => 'required|string|max:255',
            'save_as_defaults' => 'nullable|in:1',
        ]);

        $issueTs = strtotime($data['issue_date'] . ' 12:00:00') ?: time();

        $meta = [
            'trainee_name_ar' => $data['trainee_name_ar'],
            'trainee_name_en' => $data['trainee_name_en'] ?: $data['trainee_name_ar'],
            'course_title_ar' => $data['course_title_ar'],
            'course_title_en' => $data['course_title_en'] ?: $data['course_title_ar'],
            'hours' => (int) $data['hours'],
            'start_date' => date('d/m/Y', strtotime($data['start_date'])),
            'end_date' => date('d/m/Y', strtotime($data['end_date'])),
            'issue_date' => date('d/m/Y', $issueTs),
            'accreditation_number' => $data['accreditation_number'],
            'officer_name' => $data['officer_name'],
            'director_name' => $data['director_name'],
        ];

        $certificate = \App\Models\Certificate::create([
            'student_id' => (int) $data['student_id'],
            'webinar_id' => (int) $data['webinar_id'],
            'type' => 'course',
            'created_at' => $issueTs,
            'meta' => $meta,
        ]);

        if (!empty($data['save_as_defaults'])) {
            cache()->forever('qiec_certificate_defaults', [
                'officer_name' => $data['officer_name'],
                'director_name' => $data['director_name'],
                'accreditation_number' => $data['accreditation_number'],
            ]);
        }

        return redirect()
            ->route('panel.v1.admin.education.section', ['section' => 'certificates'])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إنشاء الشهادة ' . $certificate->formatted_number,
                'type' => 'success',
            ]);
    }

    private function qiecCertificateDefaults(): array
    {
        $cached = cache()->get('qiec_certificate_defaults', []);
        if (!is_array($cached)) {
            $cached = [];
        }
        $settings = function_exists('getCertificateMainSettings') ? (getCertificateMainSettings() ?: []) : [];
        if (!is_array($settings)) {
            $settings = [];
        }

        return [
            'hours' => 8,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
            'issue_date' => date('Y-m-d'),
            'accreditation_number' => $cached['accreditation_number'] ?? ($settings['accreditation_number'] ?? 'QIEC-ACC-001'),
            'officer_name' => $cached['officer_name'] ?? ($settings['officer_name'] ?? 'مسؤول التدريب والاعتماد'),
            'director_name' => $cached['director_name'] ?? ($settings['director_name'] ?? 'مدير المركز'),
        ];
    }

    public function downloadCertificate(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $certificate = \App\Models\Certificate::with([
            'student', 'webinar', 'quiz.webinar', 'bundle', 'quizzesResult',
        ])->findOrFail($id);

        $make = new \App\Mixins\Certificate\MakeCertificate();
        $inline = $request->boolean('view') || $request->routeIs('panel.v1.admin.education.certificates.view');

        return $make->showCertificateByType($certificate, $inline);
    }

    public function createCertificateTemplate(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.certificate-template-form','إنشاء قالب شهادة',array_merge(AdminMockData::shell('education','certificates'),[
            'template'=>null,'formAction'=>route('panel.v1.admin.education.certificates.templates.store'),
        ]));
    }
    public function storeCertificateTemplate(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['title'=>'required|string|max:255','image'=>'required|string|max:1000','type'=>'required|in:quiz,course,bundle']);
        $data=$request->all();
        $tmpl=\App\Models\CertificateTemplate::create([
            'image'=>$data['image'],'status'=>$data['status'] ?? 'draft','type'=>$data['type'],'created_at'=>time(),
        ]);
        \App\Models\Translation\CertificateTemplateTranslation::updateOrCreate([
            'certificate_template_id'=>$tmpl->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale()),
        ],['title'=>$data['title'],'body'=>$data['template_contents'] ?? $data['title']]);
        return redirect()->route('panel.v1.admin.education.section',['section'=>'certificates'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء القالب','type'=>'success']);
    }
    public function editCertificateTemplate(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $tmpl=\App\Models\CertificateTemplate::findOrFail($id);
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.certificate-template-form','تعديل قالب',array_merge(AdminMockData::shell('education','certificates'),[
            'template'=>$tmpl,'formAction'=>route('panel.v1.admin.education.certificates.templates.update',['id'=>$tmpl->id]),
        ]));
    }
    public function updateCertificateTemplate(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $tmpl=\App\Models\CertificateTemplate::findOrFail($id);
        $request->validate(['title'=>'required|string|max:255','image'=>'required|string|max:1000','type'=>'required|in:quiz,course,bundle']);
        $data=$request->all();
        $tmpl->update(['image'=>$data['image'],'status'=>$data['status'] ?? $tmpl->status,'type'=>$data['type']]);
        \App\Models\Translation\CertificateTemplateTranslation::updateOrCreate([
            'certificate_template_id'=>$tmpl->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale()),
        ],['title'=>$data['title'],'body'=>$data['template_contents'] ?? $data['title']]);
        return redirect()->route('panel.v1.admin.education.section',['section'=>'certificates'])->with('toast',['title'=>'تم','msg'=>'تم تحديث القالب','type'=>'success']);
    }
    public function deleteCertificateTemplate(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\CertificateTemplate::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف القالب','type'=>'success']);
    }

    // ===== عام — حذف لكل جداول التعليم المتبقية =====
    public function deleteLive(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Session::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الجلسة','type'=>'success']); }
    public function deleteAttendance(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\SessionAttendance::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الحضور','type'=>'success']); }

    // ===== فعاليات — نسخة حرفية من Admin\EventsController (CRUD كامل) =====
    public function createEvent(Request $request)
    {
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $categories=\App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
        $teachers=\App\User::where('role_name','teacher')->select('id','full_name')->orderBy('full_name')->limit(100)->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.event-form','إنشاء فعالية جديدة',array_merge(AdminMockData::shell('education','events'),[
            'categories'=>$categories,'teachers'=>$teachers,'event'=>null,
            'formAction'=>route('panel.v1.admin.education.events.store'),
        ]));
    }

    public function storeEvent(Request $request)
    {
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate([
            'type'=>'required|in:in_person,online',
            'title'=>'required|max:255',
            'subtitle'=>'required|max:255',
            'creator_id'=>'required|exists:users,id',
            'thumbnail'=>'required|max:255',
            'cover_image'=>'required|max:255',
            'category_id'=>'required|exists:categories,id',
            'seo_description'=>'required|string',
            'summary'=>'required|string',
            'description'=>'required|string',
            'capacity'=>'nullable|numeric|min:1',
            'duration'=>'nullable|numeric|min:1',
            'start_date'=>'nullable|date',
            'end_date'=>'nullable|date',
        ]);
        $event=\App\Models\Event::create($this->makeEventStoreData($request));
        $this->handleEventExtraData($request,$event);
        return redirect()->route('panel.v1.admin.education.events.edit',['id'=>$event->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الفعالية بنجاح','type'=>'success']);
    }

    public function editEvent(Request $request,int $id)
    {
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $event=\App\Models\Event::where('id',$id)->with(['tickets','speakers','prerequisites','faqs','extraDescriptions'])->firstOrFail();
        $categories=\App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
        $teachers=\App\User::where('role_name','teacher')->select('id','full_name')->orderBy('full_name')->limit(100)->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.education.event-form','تعديل فعالية',array_merge(AdminMockData::shell('education','events'),[
            'categories'=>$categories,'teachers'=>$teachers,'event'=>$event,
            'formAction'=>route('panel.v1.admin.education.events.update',['id'=>$event->id]),
        ]));
    }

    public function updateEvent(Request $request,int $id)
    {
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $event=\App\Models\Event::findOrFail($id);
        $request->validate([
            'type'=>'required|in:in_person,online',
            'title'=>'required|max:255',
            'subtitle'=>'required|max:255',
            'creator_id'=>'required|exists:users,id',
            'thumbnail'=>'required|max:255',
            'cover_image'=>'required|max:255',
            'category_id'=>'required|exists:categories,id',
            'seo_description'=>'required|string',
            'summary'=>'required|string',
            'description'=>'required|string',
        ]);
        $event->update($this->makeEventStoreData($request,$event));
        $this->handleEventExtraData($request,$event);
        return redirect()->route('panel.v1.admin.education.events.edit',['id'=>$event->id])->with('toast',['title'=>'تم','msg'=>'تم تحديث الفعالية','type'=>'success']);
    }

    public function deleteEvent(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Event::findOrFail($id)->delete(); return redirect()->route('panel.v1.admin.education.section',['section'=>'events'])->with('toast',['title'=>'تم','msg'=>'تم حذف الفعالية','type'=>'success']); }

    // ===== تذاكر الفعاليات — Admin\EventTicketsController =====
    public function storeEventTicket(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $event=\App\Models\Event::findOrFail($id);
        $request->validate(['title'=>'required|max:255','description'=>'required|string','price'=>'nullable|numeric|min:0','capacity'=>'nullable|integer|min:1','discount'=>'nullable|numeric|min:0|max:100']);
        $data=$request->all();
        $ticket=\App\Models\EventTicket::create([
            'event_id'=>$event->id,
            'price'=>!empty($data['price'])?convertPriceToDefaultCurrency($data['price']):null,
            'capacity'=>!empty($data['capacity'])?$data['capacity']:null,
            'discount'=>!empty($data['discount'])?$data['discount']:null,
            'order'=>\App\Models\EventTicket::where('event_id',$event->id)->count()+1,
            'enable'=>true,'created_at'=>time(),
        ]);
        \App\Models\Translation\EventTicketTranslation::updateOrCreate(['event_ticket_id'=>$ticket->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return back()->with('toast',['title'=>'تم','msg'=>'تمت إضافة التذكرة','type'=>'success']);
    }
    public function deleteEventTicket(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        \App\Models\EventTicket::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف التذكرة','type'=>'success']);
    }
    // ===== متحدثو الفعاليات — Admin\EventSpeakersController =====
    public function storeEventSpeaker(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $event=\App\Models\Event::findOrFail($id);
        $request->validate(['name'=>'required|max:255','job'=>'nullable|string|max:255']);
        $data=$request->all();
        $speaker=\App\Models\EventSpeaker::create([
            'event_id'=>$event->id,
            'order'=>\App\Models\EventSpeaker::where('event_id',$event->id)->count()+1,
            'enable'=>true,'created_at'=>time(),
        ]);
        \App\Models\Translation\EventSpeakerTranslation::updateOrCreate(['event_speaker_id'=>$speaker->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['name'=>$data['name'],'job'=>$data['job']??null]);
        return back()->with('toast',['title'=>'تم','msg'=>'تمت إضافة المتحدث','type'=>'success']);
    }
    public function deleteEventSpeaker(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        \App\Models\EventSpeaker::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف المتحدث','type'=>'success']);
    }

    public function changeEventStatus(Request $request,int $id,string $status)
    {
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $event=\App\Models\Event::findOrFail($id);
        $newStatus=$event->status;
        switch($status){
            case 'publish': $newStatus='publish'; break;
            case 'reject': $newStatus='rejected'; break;
            case 'unpublish': $newStatus='unpublish'; break;
            case 'cancel': $newStatus='canceled'; break;
        }
        $event->update(['status'=>$newStatus]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم تغيير حالة الفعالية','type'=>'success']);
    }

    public function exportEvents(Request $request)
    {
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $q=\App\Models\Event::with(['category','creator'])->withMin('tickets','price')->withMax('tickets','price');
        if($s=trim((string)$request->input('search',''))) $q->whereTranslationLike('title',"%{$s}%");
        if($c=$request->input('category_id')) $q->where('category_id',$c);
        if($t=$request->input('type')) $q->where('type',$t);
        if($st=$request->input('status')) $q->where('status',$st);
        $events=$q->orderBy('created_at','desc')->limit(500)->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EventsListsExport($events),'events-'.date('Y-m-d').'.xlsx');
    }

    private function makeEventStoreData(Request $request,$event=null): array
    {
        $data=$request->all();
        if(empty($data['timezone'])) $data['timezone']=getTimezone();
        return [
            'type'=>$data['type'],
            'slug'=>!empty($data['slug'])?$data['slug']:\App\Models\Event::makeSlug($data['title']),
            'creator_id'=>$data['creator_id'],
            'category_id'=>$data['category_id'],
            'thumbnail'=>$data['thumbnail']??null,
            'cover_image'=>$data['cover_image']??null,
            'capacity'=>!empty($data['capacity'])?$data['capacity']:null,
            'duration'=>!empty($data['duration'])?$data['duration']:null,
            'start_date'=>!empty($data['start_date'])?convertTimeToUTCzone($data['start_date'],$data['timezone'])->getTimestamp():null,
            'end_date'=>!empty($data['end_date'])?convertTimeToUTCzone($data['end_date'],$data['timezone'])->getTimestamp():null,
            'timezone'=>$data['timezone'],
            'support'=>!empty($data['support'])&&$data['support']=="on",
            'certificate'=>!empty($data['certificate'])&&$data['certificate']=="on",
            'private'=>!empty($data['private'])&&$data['private']=="on",
            'message_for_reviewer'=>$data['message_for_reviewer']??null,
            'status'=>$data['status']??'draft',
            'created_at'=>!empty($event)?$event->created_at:time(),
            'updated_at'=>time(),
        ];
    }

    private function handleEventExtraData(Request $request,$event): void
    {
        $data=$request->all();
        \App\Models\Translation\EventTranslation::updateOrCreate([
            'event_id'=>$event->id,
            'locale'=>mb_strtolower($data['locale']??app()->getLocale()),
        ],[
            'title'=>$data['title'],
            'subtitle'=>$data['subtitle']??$data['title'],
            'seo_description'=>$data['seo_description']??null,
            'summary'=>$data['summary']??null,
            'description'=>$data['description']??null,
        ]);
        if(!empty($data['tags'])){
            $tags=array_filter(array_map('trim',explode(',',(string)$data['tags'])));
            \App\Models\Tag::where('event_id',$event->id)->delete();
            foreach(array_slice(array_unique($tags),0,10) as $tagTitle){
                \App\Models\Tag::create(['title'=>mb_substr($tagTitle,0,64),'event_id'=>$event->id]);
            }
        }
    }

    // ===== Quiz Results — parity Admin\QuizResultsController =====
    public function quizResults(Request $request,int $quizId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $results=\App\Models\QuizzesResult::where('quiz_id',$quizId)->with(['quiz.teacher','user'])->orderBy('created_at','desc')->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','نتائج الاختبار #'.$quizId,array_merge(AdminMockData::shell('education','quizzes'),['quizzesResults'=>$results,'paginator'=>$results,'quiz_id'=>$quizId,'stubTitle'=>'نتائج الاختبار'])); }
    public function quizResultReview(Request $request,int $quizId,int $resultId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $qr=\App\Models\QuizzesResult::where('id',$resultId)->where('quiz_id',$quizId)->with(['quiz'=>fn($q)=>$q->with(['quizQuestions'=>fn($qq)=>$qq->orderBy('type','desc'),'webinar']),'user'])->firstOrFail(); $numberOfAttempt=\App\Models\QuizzesResult::where('quiz_id',$qr->quiz->id)->where('user_id',$qr->user_id)->count(); $quizQuestions=$qr->quiz->quizQuestions ?? collect(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.quiz-result-review','مراجعة النتيجة',array_merge(AdminMockData::shell('education','quizzes'),['quiz'=>$qr->quiz,'quizResult'=>$qr,'userAnswers'=>json_decode($qr->results,true) ?? [],'numberOfAttempt'=>$numberOfAttempt,'questionsSumGrade'=>$quizQuestions->sum('grade'),'quizQuestions'=>$quizQuestions,'formAction'=>route('panel.v1.admin.education.quiz-results.update',['quizId'=>$quizId,'resultId'=>$resultId])])); }
    public function quizResultUpdate(Request $request,int $quizId,int $resultId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $qr=\App\Models\QuizzesResult::where('id',$resultId)->where('quiz_id',$quizId)->with(['quiz.quizQuestions','quiz.webinar'])->firstOrFail(); $reviews=$request->get('question',[]); $old=json_decode($qr->results,true); $grade=$qr->user_grade; if(!empty($old)&&!empty($reviews)){ foreach($old as $qid=>$res){ if(isset($reviews[$qid])){ $q=\App\Models\QuizzesQuestion::where('id',$qid)->where('quiz_id',$quizId)->first(); if($q&&$q->type=='descriptive'){ $old[$qid]['status']=true; $old[$qid]['grade']=$reviews[$qid]['grade']??$q->grade; } } } $grade=array_sum(array_map(fn($r)=>isset($r['grade'])?(int)$r['grade']:0,$old)); } $qr->user_grade=$grade; $qr->status=$grade>=($qr->quiz->pass_mark??0)?\App\Models\QuizzesResult::$passed:\App\Models\QuizzesResult::$failed; $qr->results=json_encode($old); $qr->save(); if($qr->status==\App\Models\QuizzesResult::$passed){ $reward=\App\Models\RewardAccounting::calculateScore(\App\Models\Reward::PASS_THE_QUIZ); \App\Models\RewardAccounting::makeRewardAccounting($qr->user_id,$reward,\App\Models\Reward::PASS_THE_QUIZ,$qr->id,true); } return redirect()->route('panel.v1.admin.education.quiz-results',['quizId'=>$quizId])->with('toast',['title'=>'تم','msg'=>'تم تحديث النتيجة','type'=>'success']); }
    public function quizResultDelete(Request $request,int $quizId,int $resultId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\QuizzesResult::where('id',$resultId)->where('quiz_id',$quizId)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function quizResultsExport(Request $request,int $quizId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $results=\App\Models\QuizzesResult::where('quiz_id',$quizId)->with(['quiz.teacher','user'])->orderBy('created_at','desc')->get(); return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\QuizResultsExport($results),'quiz_'.$quizId.'_results.xlsx'); }

    // ===== Enrollment — parity Admin\EnrollmentController =====
    public function enrollmentHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Sale::whereNotNull('webinar_id'); $from=$request->get('from'); $to=$request->get('to'); $q=fromAndToDateFilter($from,$to,$q,'created_at'); if($s=$request->get('search')) $q->where('id',$s); $p=$q->with(['buyer','webinar'])->orderBy('created_at','desc')->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','سجل التسجيل',array_merge(AdminMockData::shell('education','courses'),['sales'=>$p,'paginator'=>$p,'stubTitle'=>'سجل التسجيل'])); }
    public function enrollmentAddStudentForm(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.education.enrollment-form','إضافة طالب لدورة',array_merge(AdminMockData::shell('education','courses'),['formAction'=>route('panel.v1.admin.education.enrollment.store')])); }
    public function enrollmentStore(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $data=$request->all(); $request->validate(['user_id'=>'required|exists:users,id']); $user=\App\User::find($data['user_id']); $sellerId=null; $itemId=null; $itemCol=null; $type=null; $product=null; if(!empty($data['webinar_id'])){ $c=\App\Models\Webinar::find($data['webinar_id']); if($c){ $sellerId=$c->creator_id; $itemId=$c->id; $type=\App\Models\Sale::$webinar; $itemCol='webinar_id'; } }elseif(!empty($data['bundle_id'])){ $b=\App\Models\Bundle::find($data['bundle_id']); if($b){ $sellerId=$b->creator_id; $itemId=$b->id; $type=\App\Models\Sale::$bundle; $itemCol='bundle_id'; } }elseif(!empty($data['product_id'])){ $product=\App\Models\Product::find($data['product_id']); if($product){ $sellerId=$product->creator_id; $itemCol='product_order_id'; $type=\App\Models\Sale::$product; $po=\App\Models\ProductOrder::create(['product_id'=>$product->id,'seller_id'=>$product->creator_id,'buyer_id'=>$user->id,'quantity'=>1,'status'=>'pending','created_at'=>time()]); $itemId=$po->id; } } if(!empty($type)&&!empty($itemId)){ $sale=\App\Models\Sale::create(['buyer_id'=>$user->id,'seller_id'=>$sellerId,$itemCol=>$itemId,'type'=>$type,'manual_added'=>true,'payment_method'=>\App\Models\Sale::$credit,'amount'=>0,'total_amount'=>0,'created_at'=>time()]); if(!empty($product)&&!empty($po)) $po->update(['sale_id'=>$sale->id]); return redirect()->route('panel.v1.admin.education.enrollment.history')->with('toast',['title'=>'تم','msg'=>'تمت الإضافة','type'=>'success']); } return back()->withErrors(['user_id'=>[trans('update.something_went_wrong')]]); }
    public function enrollmentBlock(Request $request,int $saleId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $sale=\App\Models\Sale::where('id',$saleId)->whereNull('refund_at')->firstOrFail(); if($sale->manual_added) $sale->delete(); else $sale->update(['access_to_purchased_item'=>false]); return back()->with('toast',['title'=>'تم','msg'=>'تم الحظر','type'=>'success']); }
    public function enrollmentEnable(Request $request,int $saleId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Sale::where('id',$saleId)->whereNull('refund_at')->firstOrFail()->update(['access_to_purchased_item'=>true]); return back()->with('toast',['title'=>'تم','msg'=>'تم التفعيل','type'=>'success']); }

    // ===== Upcoming Courses — parity Admin\UpcomingCoursesController =====
    public function upcomingList(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\UpcomingCourse::query(); $q=fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at'); if($t=$request->get('title')) $q->whereTranslationLike('title',"%$t%"); if($c=$request->get('category_id')) $q->where('category_id',$c); $p=$q->withCount('followers')->with('teacher')->orderBy('created_at','desc')->paginate(15)->withQueryString(); $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','الدورات القادمة',array_merge(AdminMockData::shell('education','courses'),['upcomingCourses'=>$p,'paginator'=>$p,'categories'=>$cats,'stubTitle'=>'الدورات القادمة'])); }
    public function upcomingCreate(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $teachers=\App\User::where('role_name',\App\Models\Role::$teacher)->get(); $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.upcoming-form','دورة قادمة جديدة',array_merge(AdminMockData::shell('education','courses'),['teachers'=>$teachers,'categories'=>$cats,'upcomingCourse'=>null,'formAction'=>route('panel.v1.admin.education.upcoming.store')])); }
    public function upcomingStore(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['type'=>'required|in:webinar,course,text_lesson','title'=>'required|max:255','thumbnail'=>'required','image_cover'=>'required','description'=>'required','teacher_id'=>'required|exists:users,id','category_id'=>'required|exists:categories,id','publish_date'=>'required','timezone'=>'required']); $data=$request->all(); $sd=convertTimeToUTCzone($data['publish_date'],$data['timezone']); $up=\App\Models\UpcomingCourse::create(['creator_id'=>$data['teacher_id'],'teacher_id'=>$data['teacher_id'],'category_id'=>$data['category_id'],'slug'=>!empty($data['slug'])?$data['slug']:\App\Models\UpcomingCourse::makeSlug($data['title']),'type'=>$data['type'],'thumbnail'=>$data['thumbnail'],'image_cover'=>$data['image_cover'],'publish_date'=>$sd->getTimestamp(),'timezone'=>$data['timezone'],'price'=>!empty($data['price'])?convertPriceToDefaultCurrency($data['price']):null,'status'=>\App\Models\UpcomingCourse::$pending,'created_at'=>time()]); \App\Models\Translation\UpcomingCourseTranslation::updateOrCreate(['upcoming_course_id'=>$up->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description'],'seo_description'=>$data['seo_description']??null]); return redirect()->route('panel.v1.admin.education.upcoming.edit',['id'=>$up->id])->with('toast',['title'=>'تم','msg'=>'تم الإنشاء','type'=>'success']); }
    public function upcomingEdit(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $up=\App\Models\UpcomingCourse::where('id',$id)->with(['tags'])->firstOrFail(); $teachers=\App\User::where('role_name',\App\Models\Role::$teacher)->get(); $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.upcoming-form','تعديل دورة قادمة',array_merge(AdminMockData::shell('education','courses'),['teachers'=>$teachers,'categories'=>$cats,'upcomingCourse'=>$up,'formAction'=>route('panel.v1.admin.education.upcoming.update',['id'=>$up->id])])); }
    public function upcomingUpdate(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $up=\App\Models\UpcomingCourse::findOrFail($id); $request->validate(['type'=>'required|in:webinar,course,text_lesson','title'=>'required|max:255','thumbnail'=>'required','image_cover'=>'required','description'=>'required','teacher_id'=>'required|exists:users,id','category_id'=>'required|exists:categories,id','publish_date'=>'required','timezone'=>'required']); $data=$request->all(); $sd=convertTimeToUTCzone($data['publish_date'],$data['timezone']); $isDraft=!empty($data['draft'])&&$data['draft']=='1'; $reject=!empty($data['draft'])&&$data['draft']=='reject'; $publish=!empty($data['draft'])&&$data['draft']=='publish'; $status=$publish?\App\Models\UpcomingCourse::$active:($reject?\App\Models\UpcomingCourse::$inactive:($isDraft?\App\Models\UpcomingCourse::$isDraft:\App\Models\UpcomingCourse::$pending)); $up->update(['creator_id'=>$data['teacher_id'],'teacher_id'=>$data['teacher_id'],'category_id'=>$data['category_id'],'slug'=>!empty($data['slug'])?$data['slug']:\App\Models\UpcomingCourse::makeSlug($data['title']),'type'=>$data['type'],'thumbnail'=>$data['thumbnail'],'image_cover'=>$data['image_cover'],'publish_date'=>$sd->getTimestamp(),'timezone'=>$data['timezone'],'price'=>!empty($data['price'])?convertPriceToDefaultCurrency($data['price']):null,'status'=>$status]); \App\Models\Translation\UpcomingCourseTranslation::updateOrCreate(['upcoming_course_id'=>$up->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description'],'seo_description'=>$data['seo_description']??null]); return redirect()->route('panel.v1.admin.education.upcoming.edit',['id'=>$up->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function upcomingDelete(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\UpcomingCourse::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function upcomingApprove(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $up=\App\Models\UpcomingCourse::findOrFail($id); $up->update(['status'=>\App\Models\UpcomingCourse::$active]); try{ sendNotification("upcoming_course_approved",['[item_title]'=>$up->title],$up->teacher_id); }catch(\Throwable $e){} return back()->with('toast',['title'=>'تم','msg'=>'تمت الموافقة','type'=>'success']); }
    public function upcomingReject(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\UpcomingCourse::findOrFail($id)->update(['status'=>\App\Models\UpcomingCourse::$inactive]); return back()->with('toast',['title'=>'تم','msg'=>'تم الرفض','type'=>'success']); }

    // ===== Waitlist =====
    public function waitlistIndex(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Webinar::where('enable_waitlist',true); $p=$q->paginate(15)->withQueryString(); foreach($p as $w){ $wq=\App\Models\Waitlist::where('webinar_id',$w->id); $w->members=$wq->count(); $w->registered_members=(clone $wq)->whereNotNull('user_id')->count(); } return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','قوائم الانتظار',array_merge(AdminMockData::shell('education','courses'),['waitlists'=>$p,'paginator'=>$p,'stubTitle'=>'قوائم الانتظار'])); }
    public function waitlistView(Request $request,int $webinarId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $webinar=\App\Models\Webinar::findOrFail($webinarId); $q=\App\Models\Waitlist::where('webinar_id',$webinarId); $q=fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at'); if($s=$request->get('search')) $q->where('full_name','like',"%$s%"); $p=$q->orderBy('created_at','desc')->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','قائمة الانتظار: '.$webinar->title,array_merge(AdminMockData::shell('education','courses'),['waitlistItems'=>$p,'paginator'=>$p,'webinar'=>$webinar,'stubTitle'=>'قائمة الانتظار'])); }
    public function waitlistDelete(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Waitlist::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function waitlistDeleteAll(Request $request,int $webinarId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Waitlist::where('webinar_id',$webinarId)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Course Students List =====
    public function courseStudents(Request $request, int $id)
    {
        $u = $this->resolveAdmin($request);
        if ($u instanceof \Illuminate\Http\RedirectResponse) {
            return $u;
        }

        $webinar = \App\Models\Webinar::findOrFail($id);
        $payload = $this->buildCourseStudentsPayload($webinar, $request, false);

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.course-students',
            ($webinar->title ?? 'الدورة') . ' - الطلاب',
            array_merge(AdminMockData::shell('education', 'courses'), [
                'webinar' => $webinar,
                'courseId' => $webinar->id,
                'students' => $payload['rows'],
                'paginator' => $payload['paginator'],
                'filterGroups' => $payload['filterGroups'],
            ])
        );
    }

    public function exportCourseStudents(Request $request, int $id)
    {
        $u = $this->resolveAdmin($request);
        if ($u instanceof \Illuminate\Http\RedirectResponse) {
            return $u;
        }

        $webinar = \App\Models\Webinar::findOrFail($id);
        $payload = $this->buildCourseStudentsPayload($webinar, $request, true);
        $fileName = 'course_students_' . ($webinar->slug ?: $webinar->id) . '_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CourseStudentsListExport($payload['rows']),
            $fileName
        );
    }

    /**
     * @return array{rows: array, paginator: \Illuminate\Contracts\Pagination\LengthAwarePaginator|null, filterGroups: array}
     */
    private function buildCourseStudentsPayload(\App\Models\Webinar $webinar, Request $request, bool $forExport = false): array
    {
        $webinar->loadMissing(['sessions', 'files', 'textLessons']);

        $query = \App\Models\Sale::query()
            ->with(['buyer'])
            ->where('webinar_id', $webinar->id)
            ->whereNull('refund_at')
            ->orderByDesc('created_at');

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->where('id', (int) $search)
                        ->orWhere('buyer_id', (int) $search);
                }
                $q->orWhereHas('buyer', function ($buyer) use ($search) {
                    $buyer->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('mobile', 'like', '%' . $search . '%');
                    if (ctype_digit($search)) {
                        $buyer->orWhere('id', (int) $search);
                    }
                });
            });
        }

        $groupId = $request->get('group_id');
        if (!empty($groupId)) {
            $userIds = \App\Models\GroupUser::where('group_id', $groupId)->pluck('user_id')->all();
            $query->whereIn('buyer_id', $userIds ?: [0]);
        }

        $status = (string) $request->get('status', '');
        if ($status === 'blocked') {
            $query->where('access_to_purchased_item', false);
        } elseif ($status === 'active') {
            $query->where(function ($q) {
                $q->where('access_to_purchased_item', true)->orWhereNull('access_to_purchased_item');
            });
            if (!empty($webinar->access_days)) {
                $accessTimestamp = (int) $webinar->access_days * 24 * 60 * 60;
                $query->whereRaw('created_at + ? >= ?', [$accessTimestamp, time()]);
            }
        } elseif ($status === 'expire' && !empty($webinar->access_days)) {
            $accessTimestamp = (int) $webinar->access_days * 24 * 60 * 60;
            $query->whereRaw('created_at + ? < ?', [$accessTimestamp, time()]);
        }

        $filterGroups = \App\Models\Group::where('status', 'active')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])
            ->all();

        if ($forExport) {
            $sales = $query->limit(2000)->get();
            $paginator = null;
        } else {
            $paginator = $query->paginate(10)->withQueryString();
            $sales = collect($paginator->items());
        }

        $reviewRates = \App\Models\WebinarReview::query()
            ->where('webinar_id', $webinar->id)
            ->whereIn('creator_id', $sales->pluck('buyer_id')->filter()->unique()->all() ?: [0])
            ->pluck('rates', 'creator_id');

        $rows = $sales->map(function ($sale) use ($webinar, $reviewRates) {
            $buyer = $sale->buyer;
            $buyerId = (int) ($sale->buyer_id ?? 0);
            $purchaseAt = (int) ($sale->created_at ?? 0);
            $hasAccess = $sale->access_to_purchased_item !== false && $sale->access_to_purchased_item !== 0;

            $expired = false;
            if (!empty($webinar->access_days) && $purchaseAt > 0) {
                try {
                    $expired = !$webinar->checkHasExpiredAccessDays($purchaseAt, $sale->gift_id ?? null);
                } catch (\Throwable $e) {
                    $accessSeconds = (int) $webinar->access_days * 86400;
                    $expired = ($purchaseAt + $accessSeconds) < time();
                }
            }

            if (!$buyerId || empty($buyer)) {
                $statusLabel = 'غير مسجّل';
                $statusClass = 'bg-[#FFFBEB] text-[#D97706]';
            } elseif ($expired) {
                $statusLabel = 'منتهي';
                $statusClass = 'bg-[#FFFBEB] text-[#D97706]';
            } elseif (!$hasAccess) {
                $statusLabel = 'محظور';
                $statusClass = 'bg-[#FEF2F2] text-[#DC2626]';
            } else {
                $statusLabel = 'نشط';
                $statusClass = 'bg-[#ECFDF5] text-[#059669]';
            }

            $learning = 0;
            if ($buyer) {
                $learning = \App\Http\Controllers\PanelV1\Support\CoursePerformanceBuilder::studentProgressPercent($webinar, $buyer);
            }

            $groupName = '—';
            if ($buyer && method_exists($buyer, 'getUserGroup')) {
                try {
                    $group = $buyer->getUserGroup();
                    $groupName = $group->name ?? '—';
                } catch (\Throwable $e) {
                    $groupName = '—';
                }
            }

            $rate = $reviewRates[$buyerId] ?? null;
            $actions = [];
            if ($buyerId) {
                $actions[] = [
                    'label' => 'تعديل المستخدم',
                    'url' => route('panel.v1.admin.system.users.edit', ['id' => $buyerId]),
                ];
                if ($hasAccess) {
                    $actions[] = [
                        'label' => 'حظر الوصول',
                        'action' => route('panel.v1.admin.education.enrollment.block', ['saleId' => $sale->id]),
                        'tone' => 'danger',
                        'confirm' => 'حظر وصول هذا المتدرب للدورة؟',
                    ];
                } else {
                    $actions[] = [
                        'label' => 'تفعيل الوصول',
                        'action' => route('panel.v1.admin.education.enrollment.enable', ['saleId' => $sale->id]),
                        'tone' => 'success',
                    ];
                }
            }

            return [
                'id' => $buyerId ?: ('sale-' . $sale->id),
                'name' => $buyer->full_name ?? 'متدرب',
                'email' => $buyer->email ?? '',
                'avatar' => ($buyer && method_exists($buyer, 'getAvatar')) ? $buyer->getAvatar() : null,
                'rate' => $rate !== null ? $rate : '—',
                'learning' => $learning,
                'group' => $groupName,
                'income' => handlePrice($sale->total_amount ?? 0),
                'income_raw' => (float) ($sale->total_amount ?? 0),
                'purchase_date' => $purchaseAt > 0 ? date('Y/m/d H:i', $purchaseAt) : '—',
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'actions' => $actions,
            ];
        })->values()->all();

        return [
            'rows' => $rows,
            'paginator' => $paginator,
            'filterGroups' => $filterGroups,
        ];
    }

    // ===== Course Performance (per course) =====
    public function coursePerformance(Request $request, int $id)
    {
        $u = $this->resolveAdmin($request);
        if ($u instanceof \Illuminate\Http\RedirectResponse) {
            return $u;
        }

        $webinar = \App\Models\Webinar::with(['category'])->findOrFail($id);
        $perf = \App\Http\Controllers\PanelV1\Support\CoursePerformanceBuilder::build($webinar, $request, [
            'review_route' => 'panel.v1.admin.education.assignments.review',
            'assignments_fallback_url' => route('panel.v1.admin.education.section', ['section' => 'assignments']),
            'remind_route' => 'panel.v1.admin.education.courses.performance.remind',
            'remind_params' => ['id' => $webinar->id],
        ]);

        $pendingCount = (int) ($perf['pending_count'] ?? 0);
        $behindCount = (int) ($perf['behind_count'] ?? 0);
        $completedCount = (int) ($perf['completed_count'] ?? 0);
        $firstPendingId = $perf['first_pending_id'] ?? null;

        $alertParts = [];
        if ($pendingCount > 0) {
            $alertParts[] = "{$pendingCount} واجبات بانتظار التصحيح والتقييم";
        }
        if ($behindCount > 0) {
            $alertParts[] = "{$behindCount} طلاب متأخرين عن جدول الدراسة";
        }
        $alertText = !empty($alertParts)
            ? ('مهام تشغيلية تتطلب تدخلك اليوم: ' . implode(' • ', $alertParts))
            : 'لا توجد مهام تشغيلية عاجلة لهذه الدورة حالياً';

        $filters = [
            'q' => trim((string) $request->get('q', '')),
            'progress' => (string) $request->get('progress', ''),
        ];

        $courseReviews = \App\Models\WebinarReview::with(['creator'])
            ->where('webinar_id', $webinar->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'student' => $r->creator->full_name ?? 'طالب',
                    'content' => (int) ($r->content_quality ?? 0),
                    'instructor' => (int) ($r->instructor_skills ?? 0),
                    'avg' => $r->rates ?? '—',
                    'comment' => \Illuminate\Support\Str::limit(strip_tags($r->description ?? ''), 120),
                    'date' => !empty($r->created_at) ? date('Y/m/d', (int) $r->created_at) : '—',
                ];
            })->all();

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.course-performance',
            'لوحة أداء الدورة',
            array_merge(AdminMockData::shell('education', 'courses'), [
                'webinar' => $webinar,
                'courseId' => $webinar->id,
                'courseTitle' => $webinar->title,
                'courseSubtitle' => $webinar->title,
                'courseDetailsUrl' => route('landing.v1.course-details', ['slug' => $webinar->slug]),
                'alertText' => $alertText,
                'reviewId' => $firstPendingId,
                'canGradeNow' => !empty($firstPendingId),
                'gradeNowUrl' => !empty($firstPendingId)
                    ? route('panel.v1.admin.education.assignments.review', ['id' => $firstPendingId])
                    : route('panel.v1.admin.education.section', ['section' => 'assignments']),
                'perfStats' => [
                    [
                        'value' => $pendingCount . ' واجبات',
                        'label' => 'بانتظار التصحيح',
                        'tone' => 'red',
                    ],
                    [
                        'value' => $behindCount . ' طلاب',
                        'label' => 'متأخرين عن جدول التقدم',
                        'tone' => 'yellow',
                    ],
                    [
                        'value' => $completedCount . ' طالب',
                        'label' => 'أكملوا كافة متطلبات الدورة',
                        'tone' => 'green',
                    ],
                ],
                'students' => $perf['students'] ?? [],
                'courseReviews' => $courseReviews,
                'filters' => $filters,
                'exportUrl' => route('panel.v1.admin.education.courses.performance.export', array_filter([
                    'id' => $webinar->id,
                    'q' => $filters['q'] ?: null,
                    'progress' => $filters['progress'] ?: null,
                ])),
            ])
        );
    }

    public function exportCoursePerformance(Request $request, int $id)
    {
        $u = $this->resolveAdmin($request);
        if ($u instanceof \Illuminate\Http\RedirectResponse) {
            return $u;
        }

        $webinar = \App\Models\Webinar::findOrFail($id);
        $perf = \App\Http\Controllers\PanelV1\Support\CoursePerformanceBuilder::build($webinar, $request, [
            'review_route' => 'panel.v1.admin.education.assignments.review',
            'assignments_fallback_url' => route('panel.v1.admin.education.section', ['section' => 'assignments']),
            'remind_route' => 'panel.v1.admin.education.courses.performance.remind',
            'remind_params' => ['id' => $webinar->id],
        ]);

        $fileName = 'course_students_' . ($webinar->slug ?: $webinar->id) . '_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InstructorCoursePerformanceExport($perf['students'] ?? []),
            $fileName
        );
    }

    public function remindCourseStudent(Request $request, int $id, int $studentId)
    {
        $u = $this->resolveAdmin($request);
        if ($u instanceof \Illuminate\Http\RedirectResponse) {
            return $u;
        }

        $webinar = \App\Models\Webinar::findOrFail($id);
        $sale = \App\Models\Sale::query()
            ->where('webinar_id', $webinar->id)
            ->where('buyer_id', $studentId)
            ->whereNull('refund_at')
            ->first();

        if (empty($sale)) {
            abort(404);
        }

        $student = \App\User::find($studentId);
        \App\Models\Notification::create([
            'user_id' => $studentId,
            'sender_id' => $u->id,
            'webinar_id' => $webinar->id,
            'title' => 'تذكير بمتابعة الدورة',
            'message' => 'تذكير من الإدارة لمتابعة تقدمك في دورة: ' . ($webinar->title ?? ''),
            'sender' => 'system',
            'type' => 'single',
            'created_at' => time(),
        ]);

        return back()->with('toast', [
            'title' => 'تم',
            'msg' => 'تم إرسال تذكير إلى ' . ($student->full_name ?? 'الطالب'),
            'type' => 'success',
        ]);
    }

    public function assignmentReview(Request $request, int $id)
    {
        $u = $this->resolveAdmin($request);
        if ($u instanceof \Illuminate\Http\RedirectResponse) {
            return $u;
        }

        $history = \App\Models\WebinarAssignmentHistory::with(['assignment.webinar', 'student', 'messages'])
            ->findOrFail($id);

        $assignment = $history->assignment;
        $webinar = $assignment->webinar ?? null;
        $student = $history->student;

        $studentMessages = $history->messages
            ->where('sender_id', $history->student_id)
            ->sortBy('id')
            ->values();

        $answerParagraphs = $studentMessages
            ->pluck('message')
            ->filter(fn ($msg) => filled(trim((string) $msg)))
            ->values()
            ->all();

        $attachment = $studentMessages->first(fn ($msg) => filled($msg->file_path) || filled($msg->file_title));
        $attachmentUrl = null;
        if ($attachment && filled($attachment->file_path)) {
            $publicPath = public_path($attachment->file_path);
            $attachmentUrl = is_file($publicPath)
                ? asset($attachment->file_path)
                : url($attachment->getDownloadUrl($history->assignment_id));
        }

        $statusLabel = match ($history->status) {
            \App\Models\WebinarAssignmentHistory::$pending => 'بانتظار التصحيح',
            \App\Models\WebinarAssignmentHistory::$passed => 'ناجح',
            \App\Models\WebinarAssignmentHistory::$notPassed => 'راسب',
            default => $history->status ?: '—',
        };

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.assignment-review',
            'تصحيح التكليف',
            array_merge(AdminMockData::shell('education', 'assignments'), [
                'history' => $history,
                'historyId' => $history->id,
                'webinar' => $webinar,
                'courseTitle' => $webinar->title ?? '',
                'coursePerformanceUrl' => $webinar
                    ? route('panel.v1.admin.education.courses.performance', ['id' => $webinar->id])
                    : route('panel.v1.admin.education.section', ['section' => 'courses']),
                'studentName' => $student->full_name ?? 'طالب',
                'studentEmail' => $student->email ?? '',
                'assignmentTitle' => $assignment->title ?? 'تكليف',
                'assignmentDescription' => trim(strip_tags((string) ($assignment->description ?? ''))) ?: 'لا يوجد وصف لهذا التكليف.',
                'answerParagraphs' => $answerParagraphs,
                'attachmentName' => $attachment->file_title ?? null,
                'attachmentUrl' => $attachmentUrl,
                'maxGrade' => (int) ($assignment->grade ?? 50),
                'passGrade' => (int) ($assignment->pass_grade ?? 25),
                'historyGrade' => $history->grade,
                'historyStatus' => $history->status,
                'historyStatusLabel' => $statusLabel,
                'canGrade' => in_array($history->status, [
                    \App\Models\WebinarAssignmentHistory::$pending,
                    \App\Models\WebinarAssignmentHistory::$passed,
                    \App\Models\WebinarAssignmentHistory::$notPassed,
                ], true),
                'gradeAction' => route('panel.v1.admin.education.assignments.grade', ['id' => $history->id]),
            ])
        );
    }

    public function gradeAssignment(Request $request, int $id)
    {
        $u = $this->resolveAdmin($request);
        if ($u instanceof \Illuminate\Http\RedirectResponse) {
            return $u;
        }

        $history = \App\Models\WebinarAssignmentHistory::with(['assignment.webinar'])->findOrFail($id);
        $maxGrade = (int) ($history->assignment->grade ?? 50);

        $request->validate([
            'grade' => 'required|numeric|min:0|max:' . $maxGrade,
        ]);

        $grade = (int) $request->input('grade');
        $passGrade = (int) ($history->assignment->pass_grade ?? 0);

        $history->grade = $grade;
        $history->status = $grade >= $passGrade
            ? \App\Models\WebinarAssignmentHistory::$passed
            : \App\Models\WebinarAssignmentHistory::$notPassed;
        $history->save();

        return redirect()
            ->route('panel.v1.admin.education.assignments.review', ['id' => $history->id])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم حفظ درجة التكليف',
                'type' => 'success',
            ]);
    }

    // ===== WebinarStatistic =====
    public function webinarStatistic(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $stats=[['label'=>'إجمالي الدورات','value'=>(string)\App\Models\Webinar::count(),'icon'=>'icon-[tabler--book]'],['label'=>'نشطة','value'=>(string)\App\Models\Webinar::where('status','active')->count(),'icon'=>'icon-[tabler--check]'],['label'=>'بانتظار المراجعة','value'=>(string)\App\Models\Webinar::where('status','pending')->count(),'icon'=>'icon-[tabler--hourglass]'],['label'=>'مسودات','value'=>(string)\App\Models\Webinar::where('status','is_draft')->count(),'icon'=>'icon-[tabler--file-text]'],['label'=>'إجمالي المبيعات','value'=>handlePrice(\App\Models\Sale::whereNotNull('webinar_id')->whereNull('refund_at')->sum('total_amount')),'icon'=>'icon-[tabler--cash]']]; return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','إحصائيات الدورات',array_merge(AdminMockData::shell('education','home'),['stats'=>$stats,'stubTitle'=>'الإحصائيات'])); }

    // ===== Related Courses =====
    public function relatedCourses(Request $request,int $itemId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $map=['webinar'=>'App\Models\Webinar','bundle'=>'App\Models\Bundle','product'=>'App\Models\Product','upcomingCourse'=>'App\Models\UpcomingCourse','event'=>'App\Models\Event']; $itemType=$request->get('item_type','webinar'); $q=\App\Models\RelatedCourse::where('targetable_id',$itemId)->with(['course.teacher'])->when(!empty($map[$itemType]),fn($qq)=>$qq->where('targetable_type',$map[$itemType]))->orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','دورات ذات صلة #'.$itemId,array_merge(AdminMockData::shell('education','courses'),['relatedCourses'=>$p,'paginator'=>$p,'itemId'=>$itemId,'itemType'=>$itemType,'stubTitle'=>'دورات ذات صلة'])); }
    public function storeRelatedCourse(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['item_id'=>'required','item_type'=>'required|in:webinar,bundle,product,upcomingCourse,event','course_id'=>'required|exists:webinars,id']); $data=$request->all(); $map=['webinar'=>'App\Models\Webinar','bundle'=>'App\Models\Bundle','product'=>'App\Models\Product','upcomingCourse'=>'App\Models\UpcomingCourse','event'=>'App\Models\Event']; $type=$map[$data['item_type']]; \App\Models\RelatedCourse::updateOrCreate(['targetable_id'=>$data['item_id'],'targetable_type'=>$type,'course_id'=>$data['course_id']],['order'=>null]); return back()->with('toast',['title'=>'تم','msg'=>'تم الحفظ','type'=>'success']); }
    public function deleteRelatedCourse(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\RelatedCourse::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Noticeboard =====
    public function noticeboardList(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Noticeboard::orderBy('created_at','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.education.section-real','لوح الإعلانات',array_merge(AdminMockData::shell('education','courses'),['noticeboards'=>$p,'paginator'=>$p,'stubTitle'=>'لوح الإعلانات'])); }
    public function noticeboardCreate(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.education.noticeboard-form','إعلان جديد',array_merge(AdminMockData::shell('education','courses'),['formAction'=>route('panel.v1.admin.education.noticeboard.store')])); }
    public function noticeboardStore(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required','type'=>'required','message'=>'required']); $data=$request->all(); \App\Models\Noticeboard::create(['organ_id'=>null,'type'=>$data['type'],'sender'=>'Staff','sender_id'=>auth()->id(),'sender_type'=>'platform','title'=>$data['title'],'message'=>$data['message'],'created_at'=>time()]); return redirect()->route('panel.v1.admin.education.noticeboard.list')->with('toast',['title'=>'تم','msg'=>'تم الإرسال','type'=>'success']); }
    public function noticeboardDelete(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Noticeboard::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
}
