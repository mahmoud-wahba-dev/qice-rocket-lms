@extends('panel_v1.admin.layouts.app')
@section('content')
@php
    $editQuestion = $editQuestion ?? null;
    $isEditing = !empty($editQuestion);
    $editAnswers = $isEditing ? ($editQuestion->quizzesQuestionsAnswers ?? collect()) : collect();
    $correctIndex = 0;
    if ($isEditing && $editAnswers->isNotEmpty()) {
        foreach ($editAnswers->values() as $i => $ans) {
            if (!empty($ans->correct)) {
                $correctIndex = $i;
                break;
            }
        }
    }
    $formAction = $isEditing
        ? route('panel.v1.admin.education.quizzes.questions.update', ['id' => $quiz->id, 'questionId' => $editQuestion->id])
        : route('panel.v1.admin.education.quizzes.questions.store', ['id' => $quiz->id]);
@endphp
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? 'أسئلة الاختبار',
        'subtitle' => $quiz->webinar->title ?? '',
    ])

    <div class="border border-d9 rounded-14px bg-white overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
            <h2 class="font-bold text-16px text-primary">الأسئلة ({{ $questions->total() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-14px">
                <thead>
                    <tr class="bg-fa border-b border-d9 text-gray">
                        <th class="px-4 py-3 text-start">السؤال</th>
                        <th class="px-4 py-3 text-center">النوع</th>
                        <th class="px-4 py-3 text-center">الدرجة</th>
                        <th class="px-4 py-3 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($questions as $q)
                    <tr class="border-b border-d9 last:border-0 {{ $isEditing && (int)$editQuestion->id === (int)$q->id ? 'bg-[#F7F0E6]/60' : '' }}">
                        <td class="px-4 py-3 font-bold text-primary text-start">{{ \Illuminate\Support\Str::limit($q->title ?? '', 60) }}</td>
                        <td class="px-4 py-3 text-center">{{ $q->type === 'multiple' ? 'اختيار من متعدد' : 'مقالي' }}</td>
                        <td class="px-4 py-3 text-center">{{ $q->grade }}</td>
                        <td class="px-4 py-3 text-center">
                            @include('panel_v1.components.actions-dropdown', [
                                'id' => 'quiz-q-'.$q->id,
                                'items' => [
                                    [
                                        'label' => 'تعديل',
                                        'url' => route('panel.v1.admin.education.quizzes.questions.edit', ['id' => $quiz->id, 'questionId' => $q->id]),
                                        'tone' => 'gray',
                                    ],
                                    [
                                        'label' => 'حذف',
                                        'action' => route('panel.v1.admin.education.quizzes.questions.delete', ['id' => $q->id]),
                                        'confirm' => 'حذف هذا السؤال؟',
                                        'tone' => 'danger',
                                    ],
                                ],
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا توجد أسئلة بعد</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="question-form" class="border border-d9 rounded-14px bg-white p-6 sm:p-8 {{ $isEditing ? 'ring-2 ring-primary/20' : '' }}">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="font-bold text-16px text-primary">{{ $isEditing ? 'تعديل السؤال #'.$editQuestion->id : 'إضافة سؤال' }}</h2>
            @if ($isEditing)
                <a href="{{ route('panel.v1.admin.education.quizzes.questions', ['id' => $quiz->id]) }}"
                    class="inline-flex items-center gap-1.5 font-semibold text-14px text-gray hover:text-primary transition">
                    <span class="icon-[tabler--x] size-4"></span>
                    إلغاء التعديل
                </a>
            @endif
        </div>

        <form method="POST" action="{{ $formAction }}" class="space-y-4">
            @csrf
            <div>
                <label class="font-semibold text-14px text-primary mb-2 block">نص السؤال *</label>
                <textarea name="title" rows="2" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('title', $isEditing ? ($editQuestion->title ?? '') : '') }}</textarea>
                @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النوع *</label>
                    <select name="type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="multiple" @selected(old('type', $isEditing ? $editQuestion->type : 'multiple') === 'multiple')>اختيار من متعدد</option>
                        <option value="descriptive" @selected(old('type', $isEditing ? $editQuestion->type : '') === 'descriptive')>مقالي</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الدرجة *</label>
                    <input type="number" name="grade" value="{{ old('grade', $isEditing ? $editQuestion->grade : 5) }}" min="1" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">رقم الإجابة الصحيحة (0-3)</label>
                    <input type="number" name="correct_index" value="{{ old('correct_index', $correctIndex) }}" min="0" max="3" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @for ($i = 0; $i < 4; $i++)
                    @php
                        $optVal = old('options.'.$i, $isEditing ? ($editAnswers->values()->get($i)->title ?? '') : '');
                    @endphp
                    <input type="text" name="options[]" value="{{ $optVal }}"
                        placeholder="الخيار {{ $i+1 }}{{ $i == 0 ? ' (اترك الباقي فارغاً للمقالي)' : '' }}"
                        class="input input-bordered w-full h-11 rounded-10px border-d9 text-13px">
                @endfor
            </div>
            <button type="submit" class="h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                {{ $isEditing ? 'حفظ التعديلات' : '+ إضافة السؤال' }}
            </button>
        </form>
    </div>

    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>

@if ($isEditing)
@push('scripts')
<script>
document.getElementById('question-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
</script>
@endpush
@endif
@endsection
