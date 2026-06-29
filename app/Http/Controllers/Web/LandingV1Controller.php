<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CartManagerController;
use App\Http\Controllers\Web\traits\LandingAuthRedirectTrait;
use App\Models\Role;
use App\Models\Webinar;
use App\User;
use App\Models\Sale;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OfflineBank;
use App\Models\Blog;
use App\Models\PaymentChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LandingV1Controller extends Controller
{
    use LandingAuthRedirectTrait;

    private function webinarTeacherEagerLoad(): array
    {
        return [
            'teacher:id,full_name,avatar,avatar_settings',
        ];
    }

    /**
     * Category title lives in category_translations — load manually to avoid
     * invalid `select id, title from categories` eager-load constraints.
     */
    private function getFreeWorkshops(?int $limit = null)
    {
        $query = Webinar::where('status', Webinar::$active)
            ->where('private', false)
            ->where(function ($query) {
                $query->whereNull('price')->orWhere('price', 0);
            })
            ->with($this->webinarTeacherEagerLoad())
            ->orderByDesc('created_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $workshops = $query->get();

        $this->attachCategoryTranslations($workshops);

        return $workshops;
    }

    private function getPaidCourses(?int $limit = null)
    {
        $query = Webinar::where('status', Webinar::$active)
            ->where('private', false)
            ->where('price', '>', 0)
            ->with($this->webinarTeacherEagerLoad())
            ->orderByDesc('created_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $courses = $query->get();

        $this->attachCategoryTranslations($courses);

        return $courses;
    }

    private function getLatestBlogPosts(?int $limit = 4)
    {
        return Blog::where('status', 'publish')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function attachInstructorStats(User $instructor): void
    {
        $instructor->courses_count = Webinar::where('status', Webinar::$active)
            ->where('private', false)
            ->where(function ($query) use ($instructor) {
                $query->where('creator_id', $instructor->id)
                    ->orWhere('teacher_id', $instructor->id);
            })
            ->count();

        $instructor->students_count = Sale::where('seller_id', $instructor->id)
            ->whereNotNull('webinar_id')
            ->where('type', 'webinar')
            ->whereNull('refund_at')
            ->count();

        $instructor->rating = $this->getInstructorRating($instructor);
    }

    private function getInstructorRating(User $user)
    {
        $rates = $user->rates(true);

        if (is_array($rates)) {
            return $rates['rate'] ?? 0;
        }

        return $rates ?: 0;
    }

    private function attachCategoryTranslations($webinars): void
    {
        if ($webinars instanceof Webinar) {
            $webinars = collect([$webinars]);
        }

        $collection = $webinars instanceof \Illuminate\Pagination\AbstractPaginator
            ? $webinars->getCollection()
            : collect($webinars);

        if ($collection->isEmpty()) {
            return;
        }

        $categoryIds = $collection->pluck('category_id')->filter()->unique()->values();

        if ($categoryIds->isEmpty()) {
            return;
        }

        $categories = Category::query()
            ->whereIn('id', $categoryIds)
            ->with('translations')
            ->get()
            ->keyBy('id');

        foreach ($collection as $webinar) {
            if (!empty($webinar->category_id) && isset($categories[$webinar->category_id])) {
                $webinar->setRelation('category', $categories[$webinar->category_id]);
            }
        }
    }

    private function getActiveInstructors(?int $limit = null)
    {
        $query = User::query()
            ->select('id', 'full_name', 'username', 'avatar', 'avatar_settings', 'bio', 'headline', 'about')
            ->where('role_name', Role::$teacher)
            ->where('status', 'active')
            ->orderByDesc('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function index()
    {
        $trainers = $this->getActiveInstructors(12);

        $data = [
            'pageTitle' => 'مركز الجودة والتميز للتدريب',
            'trainers' => $trainers,
            'instructors' => $trainers,
            'freeWorkshops' => $this->getFreeWorkshops(12),
            'paidCourses' => $this->getPaidCourses(12),
            'latestPosts' => $this->getLatestBlogPosts(4),
        ];

        return view('landing_v1.pages.home', $data);
    }

    public function about()
    {
        return view('landing_v1.pages.about', [
            'pageTitle' => 'من نحن',
        ]);
    }

    public function workshops()
    {
        $workshops = $this->getFreeWorkshops();

        return view('landing_v1.pages.workshops', [
            'pageTitle' => 'ورش ومحاضرات مجانية',
            'workshops' => $workshops,
        ]);
    }

    public function blogs()
    {
        $posts = Blog::where('status', 'publish')
            ->with(['category', 'author:id,full_name,avatar,avatar_settings'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('landing_v1.pages.blogs', [
            'pageTitle' => 'اخر الاخبار لدينا',
            'posts' => $posts,
        ]);
    }

    public function blogDetails($slug)
    {
        $post = Blog::where('slug', $slug)
            ->where('status', 'publish')
            ->with([
                'category',
                'author' => function ($query) {
                    $query->select('id', 'username', 'full_name', 'role_id', 'avatar', 'role_name', 'bio', 'about');
                },
            ])
            ->first();

        if (empty($post)) {
            abort(404);
        }

        $post->update(['visit_count' => ($post->visit_count ?? 0) + 1]);

        $recentPosts = Blog::where('status', 'publish')
            ->where('id', '!=', $post->id)
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('landing_v1.pages.blog-details', [
            'pageTitle' => $post->title,
            'post' => $post,
            'recentPosts' => $recentPosts,
        ]);
    }

    public function courseDetailsFree()
    {
        $course = Webinar::where('status', 'active')
            ->where('private', false)
            ->where(function ($query) {
                $query->whereNull('price')->orWhere('price', 0);
            })
            ->orderBy('id')
            ->first();

        if (empty($course)) {
            abort(404);
        }

        return redirect()->route('landing.v1.course-details', $course->slug);
    }

    public function courseDetailsPaid()
    {
        $course = Webinar::where('status', 'active')
            ->where('private', false)
            ->where('price', '>', 0)
            ->orderBy('id')
            ->first();

        if (empty($course)) {
            abort(404);
        }

        return redirect()->route('landing.v1.course-details', $course->slug);
    }

    public function coursesPaid(Request $request)
    {
        $activeCategory = $request->input('category_id');

        $categories = $this->getPaidCourseFilterCategories();

        $query = $this->paidCoursesBaseQuery()
            ->with($this->webinarTeacherEagerLoad());

        if ($request->filled('category_id')) {
            $categoryId = (int) $request->input('category_id');

            if ($categories->contains('id', $categoryId)) {
                $subCategoryIds = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
                $query->whereIn('category_id', array_merge([$categoryId], $subCategoryIds));
            } else {
                $activeCategory = null;
            }
        }

        $courses = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(20);
        $this->attachCategoryTranslations($courses);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('landing_v1.pages.courses_paid_list', [
                    'courses' => $courses,
                    'activeCategory' => $activeCategory,
                ])->render(),
                'count' => $courses->total(),
            ]);
        }

        return view('landing_v1.pages.courses-paid', [
            'pageTitle' => 'الدورات المعتمدة والبرامج المدفوعة',
            'courses' => $courses,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    private function paidCoursesBaseQuery()
    {
        return Webinar::where('status', Webinar::$active)
            ->where('private', false)
            ->where('price', '>', 0);
    }

    private function getPaidCourseFilterCategories()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('enable', true)
            ->with('translations')
            ->orderBy('order', 'asc')
            ->get();

        if ($parentCategories->isEmpty()) {
            return collect();
        }

        $parentIds = $parentCategories->pluck('id');

        $subCategoriesByParent = Category::whereIn('parent_id', $parentIds)
            ->get(['id', 'parent_id'])
            ->groupBy('parent_id');

        $paidCategoryIds = $this->paidCoursesBaseQuery()
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id')
            ->flip();

        return $parentCategories->filter(function ($category) use ($subCategoriesByParent, $paidCategoryIds) {
            if (isset($paidCategoryIds[$category->id])) {
                return true;
            }

            $subIds = $subCategoriesByParent->get($category->id, collect());

            return $subIds->contains(fn ($sub) => isset($paidCategoryIds[$sub->id]));
        })->values();
    }

    public function contact()
    {
        return view('landing_v1.pages.contact', [
            'pageTitle' => 'تواصل معنا',
        ]);
    }

    public function instructors()
    {
        $instructors = $this->getActiveInstructors();

        foreach ($instructors as $instructor) {
            $this->attachInstructorStats($instructor);
        }

        $data = [
            'pageTitle' => 'المدربين',
            'instructors' => $instructors,
        ];

        return view('landing_v1.pages.instructors', $data);
    }

    public function instructorDetails($username)
    {
        $instructor = User::query()
            ->select('id', 'full_name', 'username', 'avatar', 'avatar_settings', 'bio', 'headline', 'about', 'created_at')
            ->where('username', $username)
            ->where('role_name', Role::$teacher)
            ->where('status', 'active')
            ->firstOrFail();

        $this->attachInstructorStats($instructor);

        $courses = Webinar::where('status', Webinar::$active)
            ->where('private', false)
            ->where(function ($query) use ($instructor) {
                $query->where('teacher_id', $instructor->id)
                    ->orWhere('creator_id', $instructor->id);
            })
            ->with($this->webinarTeacherEagerLoad())
            ->orderByDesc('created_at')
            ->get();

        $this->attachCategoryTranslations($courses);

        return view('landing_v1.pages.instructor-details', [
            'pageTitle' => $instructor->full_name,
            'instructor' => $instructor,
            'courses' => $courses,
        ]);
    }

    public function courses(Request $request)
    {
        // Get parent categories
        $categories = Category::whereNull('parent_id')
            ->where('enable', true)
            ->orderBy('order', 'asc')
            ->get();

        // Query webinars
        $query = Webinar::where('status', 'active')
            ->where('private', false)
            ->with($this->webinarTeacherEagerLoad());

        // Filter by Category
        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $subCategoryIds = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
            $query->whereIn('category_id', array_merge([$categoryId], $subCategoryIds));
        }

        // Filter by Course Type
        if ($request->filled('types') && is_array($request->input('types'))) {
            $types = $request->input('types');
            $query->where(function ($q) use ($types) {
                if (in_array('paid', $types)) {
                    $q->orWhere('price', '>', 0);
                }
                if (in_array('free', $types)) {
                    $q->orWhere(function ($sq) {
                        $sq->whereNull('price')->orWhere('price', 0);
                    });
                }
                if (in_array('free_with_paid_cert', $types)) {
                    $q->orWhere(function ($sq) {
                        $sq->where(function ($ssq) {
                            $ssq->whereNull('price')->orWhere('price', 0);
                        })->where('certificate', 1);
                    });
                }
                if (in_array('has_cert', $types)) {
                    $q->orWhere('certificate', 1);
                }
            });
        }

        // Sort — default: newest first
        $sort = $request->input('sort', 'latest');
        if ($sort == 'oldest') {
            $query->orderBy('created_at');
        } elseif ($sort == 'popular') {
            $query->orderByDesc('sales_count_number')->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at')->orderByDesc('id');
        }

        $courses = $query->paginate(20);

        $this->attachCategoryTranslations($courses);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('landing_v1.pages.courses_list', ['courses' => $courses])->render(),
                'count' => $courses->total(),
            ]);
        }

        $data = [
            'pageTitle' => 'الدورات',
            'categories' => $categories,
            'courses' => $courses,
            'activeCategory' => $request->input('category_id', null)
        ];

        return view('landing_v1.pages.courses', $data);
    }

    public function courseDetails($slug = null)
    {
        // Try to fetch webinar by slug or fallback to the first active webinar if slug is missing or not found
        $course = null;
        if (!empty($slug)) {
            $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->where('private', false)
                ->with([
                    'teacher:id,full_name,avatar,avatar_settings,created_at,bio,headline,about,username',
                    'chapters' => function ($query) {
                        $query->where('status', 'active')
                            ->orderBy('order', 'asc')
                            ->with('translations');
                    },
                    'chapters.sessions' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.files' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.textLessons' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.assignments' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.quizzes' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'faqs' => function ($query) {
                        $query->orderBy('order', 'asc')->with('translations');
                    },
                ])
                ->first();
        }

        if (empty($course)) {
            $course = Webinar::where('status', 'active')
                ->where('private', false)
                ->with([
                    'teacher:id,full_name,avatar,avatar_settings,created_at,bio,headline,about,username',
                    'chapters' => function ($query) {
                        $query->where('status', 'active')
                            ->orderBy('order', 'asc')
                            ->with('translations');
                    },
                    'chapters.sessions' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.files' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.textLessons' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.assignments' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'chapters.quizzes' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'faqs' => function ($query) {
                        $query->orderBy('order', 'asc')->with('translations');
                    },
                ])
                ->orderBy('id', 'asc')
                ->first();
        }

        if (empty($course)) {
            abort(404);
        }

        $this->attachCategoryTranslations($course);

        // Calculate dynamic instructor stats
        $teacher = $course->teacher;
        $teacher_courses_count = 0;
        $teacher_students_count = 0;
        if (!empty($teacher)) {
            $teacher_courses_count = Webinar::where('status', 'active')
                ->where(function ($query) use ($teacher) {
                    $query->where('creator_id', $teacher->id)
                        ->orWhere('teacher_id', $teacher->id);
                })
                ->count();

            $teacher_students_count = Sale::where('seller_id', $teacher->id)
                ->whereNotNull('webinar_id')
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                ->count();
        }

        $teacher_rating = !empty($teacher) ? $this->getInstructorRating($teacher) : 0;

        // Query target outcomes ("What you will learn")
        $learningMaterials = $course->webinarExtraDescription()
            ->where('type', \App\Models\WebinarExtraDescription::$LEARNING_MATERIALS)
            ->get();

        // Calculate dynamic reviews distribution
        $activeReviews = $course->reviews()
            ->where('status', 'active')
            ->with('creator:id,full_name,avatar,avatar_settings')
            ->orderByDesc('created_at')
            ->get();
        $totalReviewsCount = $activeReviews->count();
        $ratesDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $activeReviews->where('rates', $i)->count();
            $percent = $totalReviewsCount > 0 ? ($count / $totalReviewsCount) * 100 : 0;
            $ratesDistribution[$i] = [
                'count' => $count,
                'percent' => $percent
            ];
        }

        $detailExtras = $this->buildCourseDetailExtras($course, $learningMaterials, $activeReviews);

        $data = array_merge([
            'pageTitle' => $course->title,
            'pageDescription' => $course->seo_description,
            'course' => $course,
            'teacher_courses_count' => $teacher_courses_count,
            'teacher_students_count' => $teacher_students_count,
            'teacher_rating' => $teacher_rating,
            'learningMaterials' => $learningMaterials,
            'ratesDistribution' => $ratesDistribution,
            'activeReviews' => $activeReviews,
            'totalReviewsCount' => $totalReviewsCount,
            'averageRating' => $totalReviewsCount > 0 ? round($activeReviews->avg('rates'), 1) : 0,
            'isInCart' => $this->isWebinarInCart($course),
            'hasUserBought' => auth()->check() ? $course->checkUserHasBought(auth()->user()) : false,
        ], $detailExtras);

        $view = ($course->price > 0)
            ? 'landing_v1.pages.course-details-paid'
            : 'landing_v1.pages.course-details-free';

        return view($view, $data);
    }

    private function buildCourseDetailExtras(Webinar $course, $learningMaterials, $activeReviews): array
    {
        $learningOutcomes = $learningMaterials->isNotEmpty()
            ? $learningMaterials->pluck('value')->filter()->values()->all()
            : [];

        $curriculumModules = $course->chapters
            ->map(function ($chapter) {
                $title = trim((string) $chapter->title);

                if ($title === '' && $chapter->relationLoaded('translations')) {
                    $title = trim((string) ($chapter->translations->first()?->title ?? ''));
                }

                return $title;
            })
            ->filter()
            ->values()
            ->all();

        $comments = $activeReviews->map(function ($review) {
            return [
                'name' => $review->creator->full_name ?? trans('public.user'),
                'date' => !empty($review->created_at) ? dateTimeFormat($review->created_at, 'j M Y') : '',
                'body' => $review->description,
            ];
        })->values()->all();

        $faqItems = ($course->relationLoaded('faqs') ? $course->faqs : $course->faqs()->with('translations')->orderBy('order', 'asc')->get())
            ->map(function ($faq) {
                return [
                    'question' => $this->resolveFaqField($faq, 'title'),
                    'answer' => $this->resolveFaqField($faq, 'answer'),
                ];
            })
            ->filter(fn ($item) => $item['question'] !== '' && $item['answer'] !== '')
            ->values()
            ->all();

        return [
            'learningOutcomes' => $learningOutcomes,
            'discoveryTopics' => $learningOutcomes,
            'curriculumModules' => $curriculumModules,
            'faqItems' => $faqItems,
            'comments' => $comments,
            'heroVideo' => $this->buildCourseHeroVideo($course),
        ];
    }

    private function resolveFaqField($faq, string $field): string
    {
        $value = trim((string) getTranslateAttributeValue($faq, $field, getDefaultLocale()));
        if ($value !== '') {
            return $value;
        }

        if ($faq->relationLoaded('translations')) {
            $translation = $faq->translations->firstWhere('locale', 'ar')
                ?? $faq->translations->first();

            return trim((string) ($translation?->{$field} ?? ''));
        }

        return '';
    }

    private function buildCourseHeroVideo(Webinar $course): array
    {
        $poster = $course->getImage();
        $source = $course->video_demo_source;
        $path = $course->video_demo;

        if (empty($source) || empty($path)) {
            return [
                'type' => 'poster',
                'poster' => $poster,
                'hasVideo' => false,
                'hasControls' => false,
            ];
        }

        $origin = urlencode(url('/'));

        switch ($source) {
            case 'youtube':
                $youtubeId = $this->extractYoutubeId($path);

                if (empty($youtubeId)) {
                    break;
                }

                return [
                    'type' => 'youtube',
                    'youtubeId' => $youtubeId,
                    'embedUrl' => "https://www.youtube-nocookie.com/embed/{$youtubeId}?enablejsapi=1&autoplay=1&mute=1&loop=1&playlist={$youtubeId}&controls=0&modestbranding=1&rel=0&playsinline=1&origin={$origin}",
                    'hasVideo' => true,
                    'hasControls' => true,
                ];

            case 'vimeo':
                $vimeoUrl = $this->convertVimeoLinkToPlay($path);

                return [
                    'type' => 'vimeo',
                    'embedUrl' => $vimeoUrl . '?autoplay=1&muted=1&loop=1&controls=0&api=1&title=0&byline=0&portrait=0&autopause=0',
                    'hasVideo' => true,
                    'hasControls' => true,
                ];

            case 'upload':
                return [
                    'type' => 'html5',
                    'videoUrl' => url($path),
                    'poster' => $poster,
                    'hasVideo' => true,
                    'hasControls' => true,
                ];

            case 'external_link':
            case 's3':
                return [
                    'type' => 'html5',
                    'videoUrl' => $path,
                    'poster' => $poster,
                    'hasVideo' => true,
                    'hasControls' => true,
                ];

            case 'secure_host':
                $bunnyEmbedUrl = $path;

                if (!str_contains($bunnyEmbedUrl, '?')) {
                    $bunnyEmbedUrl .= '?autoplay=true&loop=true&muted=true&preload=true';
                }

                return [
                    'type' => 'bunny',
                    'embedUrl' => $bunnyEmbedUrl,
                    'hasVideo' => true,
                    'hasControls' => true,
                ];

            case 'iframe':
            case 'google_drive':
                return [
                    'type' => 'raw',
                    'rawHtml' => $path,
                    'hasVideo' => true,
                    'hasControls' => false,
                ];
        }

        return [
            'type' => 'poster',
            'poster' => $poster,
            'hasVideo' => false,
            'hasControls' => false,
        ];
    }

    private function extractYoutubeId(string $path): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function convertVimeoLinkToPlay(string $path): string
    {
        $path = trim($path);

        if (str_contains($path, 'player.vimeo.com/video')) {
            return $path;
        }

        if (!preg_match('/^https?:\/\//i', $path)) {
            $path = 'https://' . $path;
        }

        $parsed = parse_url($path);

        if (empty($parsed['host'])) {
            return $path;
        }

        $hostname = preg_replace('/^www\./', '', strtolower($parsed['host']));

        if ($hostname === 'vimeo.com' && !empty($parsed['path'])) {
            $parts = explode('/', trim($parsed['path'], '/'));
            $id = end($parts);

            if (preg_match('/^\d+$/', $id)) {
                return 'https://player.vimeo.com/video/' . $id;
            }
        }

        return $path;
    }

    private function isWebinarInCart(Webinar $course): bool
    {
        $cartManager = new CartManagerController();

        return $cartManager->getCarts()->contains(function ($cart) use ($course) {
            return !empty($cart->webinar_id) && (int) $cart->webinar_id === (int) $course->id;
        });
    }

    public function checkout(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        if (empty($user)) {
            $this->storeLandingAuthIntent('checkout', [
                'discount_id' => $request->input('discount_id'),
            ]);

            return redirect()->route('landing.v1.login');
        }

        $carts = Cart::where('creator_id', $user->id)
            ->with([
                'webinar',
                'bundle',
                'ticket',
                'installmentPayment',
                'productOrder' => function ($q) { $q->with(['product']); },
            ])
            ->get();

        if (empty($carts) || $carts->isEmpty()) {
            return redirect()->route('landing.v1.cart');
        }

        $discountId     = $request->input('discount_id');
        $discountCoupon = Discount::where('id', $discountId)->first();
        if (empty($discountCoupon) || $discountCoupon->checkValidDiscount() !== 'ok') {
            $discountCoupon = null;
        }

        $cartController  = new CartController();
        $calculate       = $cartController->calculatePrice($carts, $user, $discountCoupon);
        $order           = $cartController->createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon);

        if (empty($order)) {
            return redirect()->route('landing.v1.cart');
        }

        // Free order — handle immediately
        if ($order->total_amount <= 0) {
            $paymentController = new \App\Http\Controllers\Web\PaymentController();
            $paymentController->setPaymentAccounting($order);
            $order->update(['status' => Order::$paid]);
            return redirect('/payments/status?t=' . $order->id);
        }

        $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));
        $userCurrency    = currency();

        $paymentChannels = PaymentChannel::where('status', 'active')
            ->get()
            ->filter(function ($ch) use ($isMultiCurrency, $userCurrency) {
                return !$isMultiCurrency
                    || (! empty($ch->currencies) && in_array($userCurrency, $ch->currencies));
            })
            ->values();

        $invalidChannels = PaymentChannel::where('status', 'active')
            ->get()
            ->filter(function ($ch) use ($isMultiCurrency, $userCurrency) {
                return $isMultiCurrency
                    && (empty($ch->currencies) || ! in_array($userCurrency, $ch->currencies));
            })
            ->values();

        $razorpay = $paymentChannels->contains('class_name', 'Razorpay');

        return view('landing_v1.pages.checkout', [
            'pageTitle'       => 'إتمام الدفع',
            'paymentChannels' => $paymentChannels,
            'invalidChannels' => $invalidChannels,
            'carts'           => $carts,
            'calculatePrices' => $calculate,
            'order'           => $order,
            'count'           => $carts->count(),
            'userCharge'      => $user->getAccountingCharge(),
            'razorpay'        => $razorpay,
            'discountCoupon'  => $discountCoupon,
            'offlineBanks'    => OfflineBank::orderBy('created_at', 'desc')->with('specifications')->get(),
        ]);
    }

    public function cart()
    {
        $user = auth()->user();

        // Use CartManagerController which handles both auth (DB) and guest (cookie) carts
        $cartManager = new CartManagerController();
        $carts = $cartManager->getCarts();

        $calculatePrices = [
            'sub_total'            => 0,
            'total_discount'       => 0,
            'tax'                  => 0,
            'tax_price'            => 0,
            'total'                => 0,
            'product_delivery_fee' => 0,
        ];

        if (!empty($carts) && $carts->isNotEmpty()) {
            if (!empty($user)) {
                // Authenticated user: use the full price calculator (handles tax, discounts, commissions)
                $cartController = new CartController();
                $calculatePrices = $cartController->calculatePrice($carts, $user);
            } else {
                // Guest user: calculate subtotal directly from cart items (no tax/discount logic without a user)
                $subTotal = 0;
                foreach ($carts as $cart) {
                    if (!empty($cart->webinar)) {
                        $subTotal += (float) $cart->webinar->price;
                    } elseif (!empty($cart->bundle)) {
                        $subTotal += (float) $cart->bundle->price;
                    } elseif (!empty($cart->productOrder) && !empty($cart->productOrder->product)) {
                        $subTotal += (float) $cart->productOrder->product->price * ($cart->productOrder->quantity ?? 1);
                    }
                }
                $calculatePrices['sub_total'] = round($subTotal, 2);
                $calculatePrices['total']     = round($subTotal, 2);
            }
        }

        return view('landing_v1.pages.cart', [
            'pageTitle'       => 'سلة التسوق',
            'carts'           => $carts,
            'calculatePrices' => $calculatePrices,
        ]);
    }
}
