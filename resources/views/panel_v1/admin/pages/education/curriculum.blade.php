@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', ['title'=>$stubTitle ?? 'منهج الدورة','subtitle'=>$webinar->category->title ?? ''])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <h2 class="font-bold text-16px text-primary mb-4">إضافة وحدة جديدة</h2>
        <form method="POST" action="{{ route('panel.v1.admin.education.courses.chapters.store',['id'=>$webinar->id]) }}" class="flex gap-2">
            @csrf
            <input type="text" name="title" required placeholder="عنوان الوحدة *" class="input input-bordered flex-1 h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none">
            <button type="submit" class="h-12 px-6 rounded-12px bg-primary text-white font-bold text-13px hover:opacity-95 transition shrink-0">+ إضافة</button>
        </form>
    </div>
    @forelse ($chapters as $ch)
    <div class="border border-d9 rounded-14px bg-white overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between gap-3">
            <h2 class="font-bold text-16px text-primary">{{ $ch->title ?: 'وحدة #'.$ch->id }}</h2>
            <form method="POST" action="{{ route('panel.v1.admin.education.courses.chapters.delete',['id'=>$webinar->id,'chapterId'=>$ch->id]) }}" onsubmit="return confirm('حذف الوحدة؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف الوحدة"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form>
        </div>
        <div class="p-4 sm:p-6 space-y-3">
            @foreach ($ch->sessions as $s)
                <div class="flex items-center justify-between gap-2 rounded-10px border border-d9 px-3 py-2.5">
                    <p class="font-medium text-13px text-primary">جلسة: {{ $s->title }} <span class="text-gray">({{ $s->duration ?? 0 }} دقيقة)</span></p>
                    <form method="POST" action="{{ route('panel.v1.admin.education.courses.sessions.delete',['id'=>$webinar->id,'sessionId'=>$s->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="text-red-500 font-bold text-12px">حذف</button></form>
                </div>
            @endforeach
            @foreach ($ch->files as $f)
                <div class="flex items-center justify-between gap-2 rounded-10px border border-d9 px-3 py-2.5">
                    <p class="font-medium text-13px text-primary">ملف: {{ $f->title }}</p>
                    <form method="POST" action="{{ route('panel.v1.admin.education.courses.files.delete',['id'=>$webinar->id,'fileId'=>$f->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="text-red-500 font-bold text-12px">حذف</button></form>
                </div>
            @endforeach
            @foreach ($ch->textLessons as $t)
                <div class="flex items-center justify-between gap-2 rounded-10px border border-d9 px-3 py-2.5">
                    <p class="font-medium text-13px text-primary">درس نصي: {{ $t->title }}</p>
                </div>
            @endforeach
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 pt-2">
                <form method="POST" action="{{ route('panel.v1.admin.education.courses.sessions.store',['id'=>$webinar->id]) }}" class="flex flex-wrap gap-2 rounded-10px bg-[#FAFAF4] border border-d9 p-3">
                    @csrf
                    <input type="hidden" name="chapter_id" value="{{ $ch->id }}">
                    <input type="text" name="topic" required placeholder="موضوع الجلسة *" class="input input-bordered flex-1 h-10 rounded-10px border-d9 text-13px min-w-[10rem]">
                    <input type="date" name="date" required class="input input-bordered h-10 rounded-10px border-d9 text-13px">
                    <input type="number" name="duration" value="60" min="1" class="input input-bordered w-24 h-10 rounded-10px border-d9 text-13px">
                    <button type="submit" class="h-10 px-4 rounded-10px bg-primary text-white font-bold text-12px">+ جلسة</button>
                </form>
                <form method="POST" action="{{ route('panel.v1.admin.education.courses.files.store',['id'=>$webinar->id]) }}" enctype="multipart/form-data" class="flex flex-wrap gap-2 rounded-10px bg-[#FAFAF4] border border-d9 p-3">
                    @csrf
                    <input type="hidden" name="chapter_id" value="{{ $ch->id }}">
                    <input type="text" name="title" required placeholder="عنوان الملف *" class="input input-bordered flex-1 h-10 rounded-10px border-d9 text-13px min-w-[10rem]">
                    <input type="file" name="upload" required class="input input-bordered h-10 rounded-10px border-d9 text-12px">
                    <button type="submit" class="h-10 px-4 rounded-10px bg-primary text-white font-bold text-12px">+ ملف</button>
                </form>
            </div>
        </div>
    </div>
    @empty
        @include('panel_v1.admin.components.empty-stub', ['title'=>'لا توجد وحدات','subtitle'=>'أضف الوحدة الأولى من الأعلى.'])
    @endforelse
</div>
@endsection
