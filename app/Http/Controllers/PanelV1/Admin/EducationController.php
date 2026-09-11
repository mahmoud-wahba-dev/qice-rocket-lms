<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class EducationController extends AdminController
{
    public function home(Request $request)
    {
        $stats = [
            ['label'=>'إجمالي الدورات','value'=>(string)\App\Models\Webinar::count()],
            ['label'=>'نشطة','value'=>(string)\App\Models\Webinar::where('status','active')->count()],
            ['label'=>'بانتظار المراجعة','value'=>(string)\App\Models\Webinar::where('status','pending')->count()],
            ['label'=>'مسودات','value'=>(string)\App\Models\Webinar::where('status','is_draft')->count()],
            ['label'=>'الباقات','value'=>(string)\App\Models\Bundle::count()],
            ['label'=>'الجلسات المباشرة','value'=>(string)\App\Models\Session::count()],
        ];
        $latestCourses = \App\Models\Webinar::with(['category'])->orderBy('id','desc')->limit(5)->get()->map(fn($w)=>[
            'title'=>$w->title,
            'category'=>$w->category->title ?? '',
            'status'=>$w->status,
            'statusTone'=> $w->status==='active' ? 'success' : 'danger',
            'type'=> $w->type ?? $w->category->title ?? 'دورة',
            'price'=> $w->price ? handlePrice($w->price) : 'مجانية',
        ])->all();

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.home',
            'لوحة التعليم والأكاديميات',
            array_merge(AdminMockData::shell('education','home'), [
                'welcomeTitle'=>'مرحباً بك في لوحة التعليم',
                'welcomeSubtitle'=>'نظرة عامة على الأكاديميات والدورات',
                'stats'=>$stats,
                'statsCards'=>$stats,
                'latestCourses'=>$latestCourses,
                'chartMetrics'=>[
                    ['label'=>'نشطة','value'=>\App\Models\Webinar::where('status','active')->count()],
                    ['label'=>'قيد المراجعة','value'=>\App\Models\Webinar::where('status','pending')->count()],
                ],
            ])
        );
    }

    public function section(Request $request, string $section)
    {
        $shell = AdminMockData::shell('education', $section);
        $real = [];
        $title = $section;
        switch($section){
            case 'courses':
                $title='إدارة الدورات';
                $real['courses'] = \App\Models\Webinar::with(['category','teacher'])->orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'bundles':
                $title='حزم الدورات';
                $real['bundles'] = \App\Models\Bundle::orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'assignments':
                $title='التكليفات';
                $real['assignments'] = \App\Models\WebinarAssignment::with(['webinar'])->orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'quizzes':
                $title='الاختبارات';
                $real['quizzes'] = \App\Models\Quiz::with(['webinar'])->orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'certificates':
                $title='الشهادات';
                $real['certificates'] = \App\Models\Certificate::with(['webinar','student'])->orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'live':
                $title='البث المباشر';
                $real['lives'] = \App\Models\Session::with(['webinar'])->where('status','active')->orderBy('date','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'events':
                $title='الفعاليات';
                $real['events'] = class_exists(\App\Models\Event::class) ? \App\Models\Event::orderBy('id','desc')->limit(20)->get() : collect();
                $real['stubTitle']=$title;
                break;
            case 'forums':
                $title='المنتديات';
                $real['forums'] = \App\Models\ForumTopic::with(['creator'])->orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'notifications':
                $title='إشعارات الدورات';
                $real['notifications'] = \App\Models\Notification::orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'reviews':
                $title='المراجعات';
                $real['reviews'] = \App\Models\Comment::with(['webinar','user'])->whereNotNull('webinar_id')->orderBy('id','desc')->limit(20)->get();
                $real['stubTitle']=$title;
                break;
            case 'registration':
                $title='التسجيل'; $real['registrations']=\App\User::where('status','active')->orderBy('id','desc')->limit(20)->get(); $real['stubTitle']=$title; break;
            case 'waitlists':
                $title='قوائم الانتظار'; $real['waitlists']=class_exists(\App\Models\Waitlist::class) ? \App\Models\Waitlist::orderBy('id','desc')->limit(20)->get() : collect(); $real['stubTitle']=$title; break;
            case 'departments':
                $title='الأقسام'; $real['departments']=\App\Models\Category::whereNull('parent_id')->orderBy('order')->limit(20)->get(); $real['stubTitle']=$title; break;
            case 'filters':
                $title='فلاتر الدورات'; $real['filters']=class_exists(\App\Models\Filter::class) ? \App\Models\Filter::orderBy('id','desc')->limit(20)->get() : collect(); $real['stubTitle']=$title; break;
            case 'attendance':
                $title='الحضور'; $real['attendances']=\App\Models\SessionAttendance::with(['session.webinar'])->orderBy('id','desc')->limit(20)->get(); $real['stubTitle']=$title; break;
            case 'attendance-history':
                $title='سجل الحضور'; $real['attendanceHistory']=\App\Models\SessionAttendance::orderBy('id','desc')->limit(20)->get(); $real['stubTitle']=$title; break;
            default:
                $meta = AdminMockData::stubMeta('education', $section);
                $title = $meta['stubTitle'] ?? $section;
                $real = $meta;
                break;
        }
        $data = array_merge($shell, $real, ['stubTitle'=>$title]);

        // Use real list view for known sections, else stub
        $view = in_array($section,['courses','bundles','assignments','quizzes','certificates','live','events','forums','notifications','reviews','registration','waitlists','departments','filters','attendance','attendance-history']) ? 'panel_v1.admin.pages.education.section-real' : 'panel_v1.admin.pages.education.stub';

        // fallback to stub if real view not exists
        if(!view()->exists($view)) $view='panel_v1.admin.pages.education.stub';

        return $this->renderAdmin(
            $request,
            $view,
            $title,
            $data
        );
    }

    public function storeDepartment(Request $request)
    {
        $request->validate(['title'=>'required|string|max:255']);
        $cat = new \App\Models\Category();
        $cat->title = $request->input('title');
        $cat->slug = \Illuminate\Support\Str::slug($request->input('title')).'-'.time();
        $cat->order = \App\Models\Category::max('order')+1;
        $cat->save();
        $trans = $cat->translateOrNew(app()->getLocale());
        $trans->title = $request->input('title');
        $trans->save();
        return back()->with('toast',['title'=>'تم','msg'=>'تم إنشاء القسم','type'=>'success']);
    }

    public function deleteDepartment(Request $request, int $id)
    {
        \App\Models\Category::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']);
    }
}
