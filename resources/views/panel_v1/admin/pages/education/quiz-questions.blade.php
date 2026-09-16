@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', ['title'=>$stubTitle ?? 'أسئلة الاختبار','subtitle'=>$quiz->webinar->title ?? ''])
    <div class="border border-d9 rounded-14px bg-white overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
            <h2 class="font-bold text-16px text-primary">الأسئلة ({{ $questions->total() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">السؤال</th><th class="px-4 py-3 text-center">النوع</th><th class="px-4 py-3 text-center">الدرجة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                <tbody>
                @foreach ($questions as $q)
                    <tr class="border-b border-d9 last:border-0">
                        <td class="px-4 py-3 font-bold text-primary">{{ \Illuminate\Support\Str::limit($q->title ?? '',60) }}</td>
                        <td class="px-4 py-3 text-center">{{ $q->type === 'multiple' ? 'اختيار من متعدد' : 'مقالي' }}</td>
                        <td class="px-4 py-3 text-center">{{ $q->grade }}</td>
                        <td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.quizzes.questions.delete',['id'=>$q->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <h2 class="font-bold text-16px text-primary mb-4">إضافة سؤال</h2>
        <form method="POST" action="{{ route('panel.v1.admin.education.quizzes.questions.store',['id'=>$quiz->id]) }}" class="space-y-4">
            @csrf
            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">نص السؤال *</label>
                <textarea name="title" rows="2" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('title') }}</textarea>
                @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <select name="type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="multiple" selected>اختيار من متعدد</option>
                        <option value="descriptive">مقالي</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الدرجة *</label>
                    <input type="number" name="grade" value="{{ old('grade', 5) }}" min="1" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">رقم الإجابة الصحيحة (0-3)</label>
                    <input type="number" name="correct_index" value="0" min="0" max="3" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @for($i=0;$i<4;$i++)
                    <input type="text" name="options[]" placeholder="الخيار {{ $i+1 }}{{ $i==0 ? ' (اترك الباقي فارغاً للمقالي)' : '' }}" class="input input-bordered w-full h-11 rounded-10px border-d9 text-13px">
                @endfor
            </div>
            <button type="submit" class="h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">+ إضافة السؤال</button>
        </form>
    </div>
    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>
@endsection
